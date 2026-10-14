<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** PA02: pruebas HTTP de integración. Cada CP corresponde al plan de pruebas. */
class BlogFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true, 'password' => 'PruebaSegura!2026']);
        $this->category = Category::create(['name' => 'Tecnología', 'slug' => 'tecnologia']);
    }

    private function data(array $extra = []): array
    {
        return array_replace(['title' => 'Noticia de prueba académica', 'excerpt' => 'Resumen claro con información suficiente para presentar la noticia.', 'body' => str_repeat('Información ampliada y original para la noticia. ', 4), 'status' => 'draft', 'category_id' => $this->category->id], $extra);
    }

    private function postRecord(array $extra = []): Post
    {
        $post = new Post($this->data(array_replace(['title' => 'Noticia publicada número '.(Post::count() + 1), 'status' => 'published'], $extra)));
        $post->user_id = $this->admin->id;
        $post->save();

        return $post;
    }

    /** CP01 · Abrir portada con una noticia publicada. */
    public function test_cp01_portada_publica(): void
    {

        $post = $this->postRecord();
        $this->get('/')->assertOk()->assertSeeText($post->title);
    }

    /** CP02 · Crear un borrador y consultar portada. */
    public function test_cp02_borrador_oculto_en_listado(): void
    {

        $post = $this->postRecord(['status' => 'draft']);
        $this->get('/')->assertOk()->assertDontSeeText($post->title);
    }

    /** CP03 · Abrir URL de noticia publicada. */
    public function test_cp03_leer_noticia(): void
    {

        $post = $this->postRecord();
        $this->get(route('news.show', $post->slug))->assertOk()->assertSeeText($post->title)->assertSeeText($post->body);
    }

    /** CP04 · Acceder a la URL conocida de un borrador. */
    public function test_cp04_borrador_sin_acceso_directo(): void
    {

        $post = $this->postRecord(['status' => 'draft']);
        $this->get(route('news.show', $post->slug))->assertNotFound()->assertDontSeeText($post->body);
    }

    /** CP05 · Buscar palabra que existe en publicada y borrador. */
    public function test_cp05_busqueda_sin_filtrar_borradores(): void
    {

        $visible = $this->postRecord(['title' => 'Tecnología abierta para estudiantes']);
        $hidden = $this->postRecord(['title' => 'Tecnología interna en revisión', 'status' => 'draft']);
        $this->postRecord(['title' => 'Lectura pública para vecinos']);
        $this->get('/?q=Tecnolog')->assertOk()->assertSeeText($visible->title)->assertDontSeeText($hidden->title)->assertDontSeeText('Lectura pública para vecinos');
    }

    /** CP06 · Seleccionar una categoría con otras noticias disponibles. */
    public function test_cp06_filtro_por_categoria(): void
    {

        $one = $this->postRecord();
        $otherCategory = Category::create(['name' => 'Comunidad', 'slug' => 'comunidad']);
        $two = $this->postRecord(['category_id' => $otherCategory->id]);
        $this->get('/?categoria='.$otherCategory->id)->assertOk()->assertSeeText($two->title)->assertDontSeeText($one->title);
    }

    /** CP07 · Combinar palabra y categoría. */
    public function test_cp07_busqueda_y_categoria(): void
    {

        $one = $this->postRecord(['title' => 'Noticias de ciencia y tecnología']);
        $cat = Category::create(['name' => 'Educación', 'slug' => 'educacion']);
        $two = $this->postRecord(['title' => 'Noticias de ciencia en las aulas', 'category_id' => $cat->id]);
        $this->get('/?q=ciencia&categoria='.$cat->id)->assertOk()->assertSeeText($two->title)->assertDontSeeText($one->title);
    }

    /** CP08 · Crear siete noticias y abrir páginas 1 y 2. */
    public function test_cp08_paginacion_conserva_filtros(): void
    {

        for ($i = 1; $i <= 7; $i++) {
            $this->postRecord(['title' => 'Especial académico número '.$i]);
        }
        $this->get('/?q=Especial')->assertOk()->assertSeeText('Especial académico número 7')->assertDontSeeText('Especial académico número 1')->assertSee('q=Especial', false);
        $this->get('/?q=Especial&page=2')->assertOk()->assertSeeText('Especial académico número 1')->assertDontSeeText('Especial académico número 7');
    }

    /** CP09 · Buscar un término inexistente. */
    public function test_cp09_sin_resultados(): void
    {

        $this->get('/?q=inexistente')->assertOk()->assertSeeText('No encontramos noticias')->assertSeeText('Ver todas las noticias');
    }

    /** CP10 · Invitado intenta leer, crear, editar y borrar en administración. */
    public function test_cp10_visitante_no_puede_administrar(): void
    {

        $post = $this->postRecord();
        $this->get(route('admin.posts.index'))->assertRedirect(route('login'));
        $this->get(route('admin.posts.create'))->assertRedirect(route('login'));
        $this->post(route('admin.posts.store'), $this->data())->assertRedirect(route('login'));
        $this->put(route('admin.posts.update', $post), $this->data())->assertRedirect(route('login'));
        $this->delete(route('admin.posts.destroy', $post))->assertRedirect(route('login'));
        $this->assertDatabaseCount('posts', 1);
    }

    /** CP11 · Usuario autenticado sin permiso intenta operaciones administrativas. */
    public function test_cp11_usuario_sin_rol_no_puede_administrar(): void
    {

        $visitor = User::factory()->create(['is_admin' => false]);
        $post = $this->postRecord();
        $this->actingAs($visitor);
        $this->get(route('admin.posts.index'))->assertForbidden();
        $this->post(route('admin.posts.store'), $this->data())->assertForbidden();
        $this->put(route('admin.posts.update', $post), $this->data())->assertForbidden();
        $this->delete(route('admin.posts.destroy', $post))->assertForbidden();
        $this->assertDatabaseCount('posts', 1);
    }

    /** CP12 · Ingresar con administrador y contraseña válida. */
    public function test_cp12_login_correcto(): void
    {

        $this->post(route('login.store'), ['email' => $this->admin->email, 'password' => 'PruebaSegura!2026'])->assertRedirect(route('admin.posts.index'));
        $this->assertAuthenticatedAs($this->admin);
        $this->get(route('admin.posts.index'))->assertOk();
    }

    /** CP13 · Ingresar con contraseña incorrecta. */
    public function test_cp13_login_incorrecto(): void
    {

        $this->from(route('login'))->post(route('login.store'), ['email' => $this->admin->email, 'password' => 'incorrecta'])->assertRedirect(route('login'))->assertSessionHasErrors('email')->assertSessionMissing('_old_input.password');
        $this->assertGuest();
    }

    /** CP14 · Realizar cinco ingresos fallidos y un sexto intento. */
    public function test_cp14_limite_intentos(): void
    {

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.store'), ['email' => $this->admin->email, 'password' => 'incorrecta']);
        }
        $this->post(route('login.store'), ['email' => $this->admin->email, 'password' => 'PruebaSegura!2026'])->assertSessionHasErrors(['email' => 'Demasiados intentos. Espera un minuto y vuelve a intentarlo.']);
        $this->assertGuest();
    }

    /** CP15 · Administrador cierra sesión e intenta volver al panel. */
    public function test_cp15_cerrar_sesion(): void
    {

        $this->actingAs($this->admin)->post(route('logout'))->assertRedirect(route('news.index'));
        $this->assertGuest();
        $this->get(route('admin.posts.index'))->assertRedirect(route('login'));
    }

    /** CP16 · Administrador guarda noticia válida como borrador. */
    public function test_cp16_crear_borrador(): void
    {

        $this->actingAs($this->admin)->post(route('admin.posts.store'), $this->data())->assertRedirect(route('admin.posts.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('posts', ['title' => 'Noticia de prueba académica', 'status' => 'draft', 'user_id' => $this->admin->id, 'published_at' => null]);
    }

    /** CP17 · Guardar noticia con estado publicada. */
    public function test_cp17_crear_publicada(): void
    {

        $this->actingAs($this->admin)->post(route('admin.posts.store'), $this->data(['status' => 'published']))->assertSessionHasNoErrors();
        $post = Post::first();
        $this->assertNotNull($post->published_at);
        $this->get('/')->assertSeeText($post->title);
    }

    /** CP18 · Cambiar título y categoría de una noticia. */
    public function test_cp18_editar_noticia(): void
    {

        $post = $this->postRecord();
        $slug = $post->slug;
        $this->actingAs($this->admin)->put(route('admin.posts.update', $post), $this->data(['title' => 'Una noticia corregida', 'status' => 'published']))->assertSessionHasNoErrors()->assertRedirect(route('admin.posts.index'));
        $this->assertSame('Una noticia corregida', $post->fresh()->title);
        $this->assertSame($slug, $post->fresh()->slug);
    }

    /** CP19 · Editar una noticia manteniendo su título. */
    public function test_cp19_editar_sin_cambiar_titulo(): void
    {

        $post = $this->postRecord();
        $this->actingAs($this->admin)->put(route('admin.posts.update', $post), $this->data(['title' => $post->title]))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('posts', 1);
    }

    /** CP20 · Repetir título con mayúsculas y espacios distintos al crear y editar. */
    public function test_cp20_titulo_duplicado_normalizado(): void
    {

        $one = $this->postRecord(['title' => 'Educación y tecnología']);
        $two = $this->postRecord();
        $this->actingAs($this->admin)->post(route('admin.posts.store'), $this->data(['title' => '  EDUCACIÓN   Y TECNOLOGÍA  ']))->assertSessionHasErrors('title');
        $this->put(route('admin.posts.update', $two), $this->data(['title' => $one->title]))->assertSessionHasErrors('title');
        $this->assertDatabaseCount('posts', 2);
        $this->assertNotSame($one->title, $two->fresh()->title);
    }

    /** CP21 · Título vacío, cuatro caracteres, 151 caracteres y tipo no textual. */
    public function test_cp21_limites_titulo(): void
    {

        foreach (['', 'Arte', str_repeat('A', 151), ['invalido']] as $value) {
            $this->actingAs($this->admin)->post(route('admin.posts.store'), $this->data(['title' => $value]))->assertSessionHasErrors('title');
        } $this->assertDatabaseCount('posts', 0);
    }

    /** CP22 · Resumen vacío, 19 y 301 caracteres. */
    public function test_cp22_limites_resumen(): void
    {

        foreach (['', str_repeat('A', 19), str_repeat('A', 301)] as $value) {
            $this->actingAs($this->admin)->post(route('admin.posts.store'), $this->data(['excerpt' => $value]))->assertSessionHasErrors('excerpt');
        } $this->assertDatabaseCount('posts', 0);
    }

    /** CP23 · Contenido de 79, de 20 001 caracteres y repetido respecto al resumen. */
    public function test_cp23_contenido_minimo_maximo_y_diferente(): void
    {

        foreach ([str_repeat('B', 79), str_repeat('B', 20001)] as $value) {
            $this->actingAs($this->admin)->post(route('admin.posts.store'), $this->data(['body' => $value]))->assertSessionHasErrors('body');
        }
        $same = str_repeat('Una información repetida. ', 5);
        $this->post(route('admin.posts.store'), $this->data(['excerpt' => $same, 'body' => $same]))->assertSessionHasErrors(['body' => 'El contenido debe ser diferente del resumen y ampliar la información.']);
        $this->assertDatabaseCount('posts', 0);
    }

    /** CP24 · Enviar categoría inexistente o no numérica. */
    public function test_cp24_categoria_valida(): void
    {

        foreach ([999999, 'texto', ''] as $value) {
            $this->actingAs($this->admin)->post(route('admin.posts.store'), $this->data(['category_id' => $value]))->assertSessionHasErrors('category_id');
        }
        $this->assertDatabaseCount('posts', 0);
    }

    /** CP25 · Enviar un estado fuera de borrador o publicada. */
    public function test_cp25_estado_valido(): void
    {

        $this->actingAs($this->admin)->post(route('admin.posts.store'), $this->data(['status' => 'administrador']))->assertSessionHasErrors('status');
        $this->assertDatabaseCount('posts', 0);
    }

    /** CP26 · Enviar título corto desde el formulario. */
    public function test_cp26_error_accesible_y_datos_conservados(): void
    {

        $data = $this->data(['title' => 'Arte']);
        $this->actingAs($this->admin)->from(route('admin.posts.create'))->post(route('admin.posts.store'), $data)->assertRedirect(route('admin.posts.create'))->assertSessionHasInput('excerpt', $data['excerpt']);
        $this->get(route('admin.posts.create'))->assertOk()->assertSee('role="alert"', false)->assertSee('id="title-error"', false)->assertSee('aria-invalid="true"', false)->assertSeeText('El campo título debe tener al menos 5 caracteres.');
    }

    /** CP27 · Abrir pantalla de confirmación de eliminación. */
    public function test_cp27_confirmacion_no_borra(): void
    {

        $post = $this->postRecord();
        $this->actingAs($this->admin)->get(route('admin.posts.confirm', $post))->assertOk()->assertSeeText('Cancelar y conservar');
        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    /** CP28 · Confirmar eliminación como administrador. */
    public function test_cp28_eliminar_noticia(): void
    {

        $post = $this->postRecord();
        $this->actingAs($this->admin)->delete(route('admin.posts.destroy', $post))->assertRedirect(route('admin.posts.index'));
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
        $this->get(route('news.show', $post->slug))->assertNotFound();
    }

    /** CP29 · Cambiar noticia publicada a borrador. */
    public function test_cp29_retirar_publicacion(): void
    {

        $post = $this->postRecord();
        $this->actingAs($this->admin)->put(route('admin.posts.update', $post), $this->data(['title' => $post->title, 'status' => 'draft']))->assertSessionHasNoErrors();
        $this->assertNull($post->fresh()->published_at);
        $this->get(route('news.show', $post->slug))->assertNotFound();
    }

    /** CP30 · Guardar contenido con una etiqueta script y abrir noticia. */
    public function test_cp30_html_se_muestra_como_texto(): void
    {

        $post = $this->postRecord(['body' => '<script>alert("prueba")</script>'.str_repeat('Contenido de la noticia. ', 5)]);
        $this->get(route('news.show', $post->slug))->assertOk()->assertDontSee('<script>', false)->assertSee('&lt;script&gt;', false);
    }

    /** CP31 · Enviar autor, URL y fecha manipulados en formulario válido. */
    public function test_cp31_autor_no_se_inyecta(): void
    {

        $this->actingAs($this->admin)->post(route('admin.posts.store'), $this->data(['user_id' => 99999, 'slug' => 'infiltrado', 'published_at' => '2000-01-01']))->assertSessionHasNoErrors();
        $post = Post::first();
        $this->assertSame($this->admin->id, $post->user_id);
        $this->assertNotSame('infiltrado', $post->slug);
        $this->assertNull($post->published_at);
    }

    /** CP32 · Abrir portada e ingreso. */
    public function test_cp32_estructura_y_cabeceras(): void
    {

        $this->get('/')->assertOk()->assertSee('lang="es"', false)->assertSee('name="viewport"', false)->assertSee('href="#contenido"', false)->assertHeader('X-Frame-Options', 'DENY')->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->get(route('login'))->assertSee('for="email"', false)->assertSee('autocomplete="current-password"', false)->assertSee('name="_token"', false);

        // La preferencia solo acepta las cuatro paletas y persiste al cambiar de página.
        foreach (['dark', 'red-green', 'blue-yellow', 'light'] as $theme) {
            $this->get('/?tema='.$theme)->assertOk()->assertSee('data-theme="'.$theme.'"', false);
            $this->get(route('login'))->assertOk()->assertSee('data-theme="'.$theme.'"', false);
        }
        $this->get('/?tema[]=dark')->assertOk()->assertSee('data-theme="light"', false);
        $this->get('/?tema=invalida')->assertOk()->assertSee('data-theme="light"', false);

    }

    /** CP33 · Enviar las longitudes mínimas y máximas permitidas. */
    public function test_cp33_fronteras_validas(): void
    {

        foreach ([['title' => 'ABCDE', 'excerpt' => str_repeat('E', 20), 'body' => str_repeat('B', 80)], ['title' => str_repeat('T', 150), 'excerpt' => str_repeat('E', 300), 'body' => str_repeat('B', 20000)]] as $data) {
            $this->actingAs($this->admin)->post(route('admin.posts.store'), $this->data($data))->assertSessionHasNoErrors();
        } $this->assertDatabaseCount('posts', 2);
    }

    /** CP34 · Verificar hash y autenticar usuario sin rol editorial. */
    public function test_cp34_contrasena_y_sin_rol(): void
    {

        $this->assertNotSame('PruebaSegura!2026', $this->admin->password);
        $this->assertTrue(Hash::check('PruebaSegura!2026', $this->admin->password));
        $user = User::factory()->create(['password' => 'OtraPrueba!2026', 'is_admin' => false]);
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'OtraPrueba!2026'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** CP35 · Comparar consultas con una y seis noticias públicas. */
    public function test_cp35_consultas_acotadas(): void
    {

        $this->postRecord();
        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->get('/')->assertOk();
        $small = count(DB::getQueryLog());
        for ($i = 0; $i < 5; $i++) {
            $this->postRecord();
        } DB::flushQueryLog();
        $this->get('/')->assertOk();
        $large = count(DB::getQueryLog());
        DB::disableQueryLog();
        $this->assertSame($small, $large);
        $this->assertLessThanOrEqual(6, $large);
    }

    /** CP36 · Abrir noticia y edición de identificador inexistente. */
    public function test_cp36_noticia_inexistente(): void
    {

        $this->get('/noticias/no-existe')->assertNotFound()->assertSeeText('Volver al inicio');
        $this->actingAs($this->admin)->get('/admin/noticias/99999/edit')->assertNotFound();
    }
}
