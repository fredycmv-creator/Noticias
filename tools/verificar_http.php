<?php

date_default_timezone_set('America/Lima');

/** Prueba HTTP real de la demo local. Requiere el servidor iniciado en 127.0.0.1:8020. */
$base = rtrim($argv[1] ?? 'http://127.0.0.1:8020', '/');
if (! preg_match('~^http://127\.0\.0\.1:\d+$~', $base)) {
    fwrite(STDERR, "Solo se permite el servidor de demostración en 127.0.0.1.\n");
    exit(1);
}
$cookie = tempnam(sys_get_temp_dir(), 'blog-http-');
$checks = [];
function requestBlog(string $path, array $data = [], bool $post = false): array
{
    global $base, $cookie;
    $c = curl_init($base.$path);
    curl_setopt_array($c, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_COOKIEJAR => $cookie, CURLOPT_COOKIEFILE => $cookie, CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 5]);
    if ($post) {
        curl_setopt_array($c, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => http_build_query($data)]);
    }
    $body = curl_exec($c);
    if ($body === false) {
        throw new RuntimeException(curl_error($c));
    }
    $result = ['code' => curl_getinfo($c, CURLINFO_RESPONSE_CODE), 'ms' => round(curl_getinfo($c, CURLINFO_TOTAL_TIME) * 1000, 2), 'body' => $body];
    curl_close($c);

    return $result;
}
function checkBlog(string $id, string $what, bool $success): void
{
    global $checks;
    $checks[] = ['id' => $id, 'description' => $what, 'passed' => $success];
    if (! $success) {
        throw new RuntimeException("Falló $id: $what");
    }
}
function tokenBlog(string $html): string
{
    preg_match('/name="_token"\s+value="([^"]+)"/', $html, $m);
    if (! isset($m[1])) {
        throw new RuntimeException('No se encontró token CSRF.');
    }

    return html_entity_decode($m[1]);
}
try {
    $start = requestBlog('/');
    checkBlog('H01', 'Portada disponible', $start['code'] === 200 && str_contains($start['body'], 'Últimas noticias'));
    $login = requestBlog('/ingresar');
    $token = tokenBlog($login['body']);
    $csrf = requestBlog('/ingresar', ['email' => 'admin@blog.test', 'password' => 'DemoNoticias!2026'], true);
    checkBlog('H02', 'CSRF rechaza envío sin token en HTTP real', $csrf['code'] === 419);
    $login = requestBlog('/ingresar');
    $token = tokenBlog($login['body']);
    $bad = requestBlog('/ingresar', ['_token' => $token, 'email' => 'admin@blog.test', 'password' => 'incorrecta'], true);
    checkBlog('H03', 'Ingreso inválido informa el error', $bad['code'] === 200 && str_contains($bad['body'], 'El correo o la contraseña no son correctos.'));
    $login = requestBlog('/ingresar');
    $token = tokenBlog($login['body']);
    $panel = requestBlog('/ingresar', ['_token' => $token, 'email' => 'admin@blog.test', 'password' => 'DemoNoticias!2026'], true);
    checkBlog('H04', 'Ingreso válido abre panel', $panel['code'] === 200 && str_contains($panel['body'], 'Tus noticias'));
    $form = requestBlog('/admin/noticias/create');
    $token = tokenBlog($form['body']);
    preg_match('/<option value="(\d+)"/', $form['body'], $cat);
    $title = 'Prueba HTTP '.date('YmdHis').'-'.bin2hex(random_bytes(3));
    $data = ['_token' => $token, 'title' => $title, 'excerpt' => 'Resumen de demostración para comprobar el formulario de noticias.', 'body' => str_repeat('Texto ampliado exclusivo de la prueba HTTP local. ', 4), 'category_id' => $cat[1], 'status' => 'draft'];
    $invalid = requestBlog('/admin/noticias', array_replace($data, ['title' => 'Arte']), true);
    checkBlog('H05', 'Error de validación legible', $invalid['code'] === 200 && str_contains($invalid['body'], 'El campo título debe tener al menos 5 caracteres.'));
    checkBlog('H06', 'Error asociado y resumen conservado', str_contains($invalid['body'], 'id="title-error"') && str_contains($invalid['body'], $data['excerpt']));
    $create = requestBlog('/admin/noticias', $data, true);
    checkBlog('H07', 'Creación real de borrador', str_contains($create['body'], 'Noticia creada correctamente.'));
    $dom = new DOMDocument;
    @$dom->loadHTML('<?xml encoding="UTF-8">'.$create['body']);
    $xpath = new DOMXPath($dom);
    $link = $xpath->query('//tr[th[contains(.,"'.$title.'")]]//a[contains(@href,"/edit")]')->item(0);
    if (! $link) {
        throw new RuntimeException('No se encontró la noticia creada.');
    }
    $editPath = parse_url($link->getAttribute('href'), PHP_URL_PATH);
    $edit = requestBlog($editPath);
    $token = tokenBlog($edit['body']);
    $resource = str_replace('/edit', '', $editPath);
    $index = requestBlog('/?q='.urlencode($title));
    checkBlog('H08', 'Borrador oculto del público', ! str_contains($index['body'], $title.'</a>'));
    $same = str_repeat('Información repetida entre resumen y contenido. ', 3);
    $sameResult = requestBlog($resource, array_replace($data, ['_token' => $token, '_method' => 'PUT', 'excerpt' => $same, 'body' => $same]), true);
    checkBlog('H09', 'Contenido no puede repetir resumen', str_contains($sameResult['body'], 'El contenido debe ser diferente del resumen'));
    $update = requestBlog($resource, array_replace($data, ['_token' => $token, '_method' => 'PUT', 'status' => 'published']), true);
    checkBlog('H10', 'Publicación desde edición', str_contains($update['body'], 'Noticia actualizada correctamente.'));
    $public = requestBlog('/?q='.urlencode($title));
    checkBlog('H11', 'Noticia publicada visible', str_contains($public['body'], $title.'</a>'));
    $dup = requestBlog('/admin/noticias', array_replace($data, ['_token' => $token, 'title' => mb_strtoupper($title)]), true);
    checkBlog('H12', 'Título duplicado rechazado en HTTP', str_contains($dup['body'], 'Ya existe una noticia con ese título.'));
    $confirm = requestBlog($resource.'/eliminar');
    $token = tokenBlog($confirm['body']);
    $delete = requestBlog($resource, ['_token' => $token, '_method' => 'DELETE'], true);
    checkBlog('H13', 'Eliminación del registro de prueba', str_contains($delete['body'], 'Noticia eliminada correctamente.'));
    $logout = requestBlog('/salir', ['_token' => $token], true);
    $after = requestBlog('/admin/noticias');
    checkBlog('H14', 'Cierre impide volver al panel', str_contains($after['body'], 'name="password"'));
    $samples = [];
    for ($i = 0; $i < 10; $i++) {
        $samples[] = requestBlog('/')['ms'];
    } sort($samples);
    echo json_encode(['generated_at' => date(DATE_ATOM), 'scope' => 'HTTP local secuencial; sin carga concurrente; no certificación de rendimiento',
        'checks' => $checks, 'passed' => count($checks), 'failed' => 0, 'home_html_bytes' => strlen($start['body']),
        'warm_requests' => 10, 'median_ms' => round(($samples[4] + $samples[5]) / 2, 2), 'p95_ms' => $samples[9], 'samples_ms' => $samples], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n";
} catch (Throwable $e) {
    echo json_encode(['checks' => $checks, 'error' => $e->getMessage()], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n";
    exit(1);
} finally {
    @unlink($cookie);
}
