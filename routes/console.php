<?php

use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Artisan;

Artisan::command('blog:demo', function () {
    if (! app()->environment('local', 'testing')) {
        $this->error('Los datos de demostración solo se crean en local.');

        return 1;
    }
    $this->call('db:seed', ['--class' => DatabaseSeeder::class]);
    $this->info('Demo local: admin@blog.test / DemoNoticias!2026');
    $this->warn('Credenciales ficticias para la demostración local; no usar en un servidor público.');
})->purpose('Crear contenido y administrador ficticios sin borrar datos existentes');
