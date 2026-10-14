<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            throw new \RuntimeException('Seeder de demostración solo local.');
        }
        $admin = User::firstOrNew(['email' => 'admin@blog.test']);
        if (! $admin->exists) {
            $admin->fill(['name' => 'Redacción Pulso', 'password' => 'DemoNoticias!2026']);
            $admin->is_admin = true;
            $admin->save();
        }
        Post::unguard();
        $items = [
            ['Tecnología', 'Bibliotecas digitales para aprender a tu ritmo', 'Los recursos abiertos acercan libros, cursos y herramientas de estudio a más estudiantes.', 'Una biblioteca digital permite explorar materiales desde distintos dispositivos. Este ejemplo presenta una noticia ficticia para comprobar la publicación, la búsqueda y la lectura de contenidos. La redacción incorpora títulos claros, resúmenes breves y categorías que facilitan encontrar información.'],
            ['Educación', 'Estudiantes comparten proyectos en una feria académica', 'Una jornada de intercambio reúne propuestas de software y experiencias de aprendizaje.', 'La comunidad estudiantil dispone de un espacio para presentar prototipos y explicar cómo se construyeron. Esta noticia de demostración destaca la importancia de planificar, probar y documentar los proyectos. Los asistentes pueden conversar sobre los resultados y sugerir nuevas mejoras.'],
            ['Comunidad', 'Lectura al aire libre en los espacios de la ciudad', 'Una iniciativa ficticia invita a compartir historias y recuperar el hábito de la lectura.', 'Los espacios públicos pueden convertirse en lugares para aprender y conversar. En esta demostración se describe una jornada de lectura con actividades abiertas para distintas edades. La noticia sirve para explorar el diseño del blog y comprobar que el texto se adapta a pantallas pequeñas.'],
            ['Tecnología', 'Cinco hábitos para cuidar tus cuentas digitales', 'Contraseñas distintas y verificación adicional ayudan a proteger el acceso a los servicios.', 'Utilizar una contraseña diferente para cada servicio reduce el impacto de una filtración. También conviene activar la verificación adicional y revisar los permisos de las aplicaciones. Este contenido educativo ficticio permite comprobar la presentación de noticias dentro del blog académico.'],
            ['Educación', 'Aprender programación con proyectos pequeños', 'Construir una aplicación sencilla permite practicar y revisar resultados de forma continua.', 'Un proyecto pequeño puede incluir una página pública, un formulario y una base de datos. Dividir el trabajo en actividades facilita encontrar errores y comprobar que cada parte funciona. Esta noticia de ejemplo conecta el aprendizaje de programación con la planificación de pruebas de software.'],
            ['Comunidad', 'Una agenda para conocer actividades de tu barrio', 'Organizar la información por categorías mejora la consulta de las novedades locales.', 'La información resulta más útil cuando se presenta de manera clara y fácil de encontrar. Una agenda comunitaria puede reunir talleres y encuentros en un mismo espacio. Esta es una noticia ficticia de demostración; no describe una actividad real ni anuncia fechas de eventos.'],
            ['Tecnología', 'Diseño accesible desde el primer formulario', 'Las etiquetas, el contraste y los mensajes de error ayudan a comprender una interfaz web.', 'Relacionar cada campo con una etiqueta permite identificar qué dato se solicita. Los mensajes de error deben explicar el problema y conservar los datos válidos para facilitar la corrección. La navegación por teclado y el diseño adaptable forman parte de la revisión del prototipo.'],
        ];
        foreach ($items as [$name,$title,$excerpt,$body]) {
            $category = Category::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]);
            Post::firstOrCreate(['title_key' => Str::lower($title)], ['title' => $title, 'excerpt' => $excerpt, 'body' => $body, 'category_id' => $category->id, 'user_id' => $admin->id, 'status' => 'published']);
        }
        $draftTitle = 'Borrador de la próxima edición';
        Post::firstOrCreate(['title_key' => Str::lower($draftTitle)], ['title' => $draftTitle, 'excerpt' => 'Este contenido está en revisión y solo aparece dentro del panel de administración.', 'body' => str_repeat('Texto ficticio en revisión editorial. ', 5), 'category_id' => Category::first()->id, 'user_id' => $admin->id, 'status' => 'draft']);
        Post::reguard();
    }
}
