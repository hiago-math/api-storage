<?php

namespace Application\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Define as configurações do PHP
        ini_set('upload_max_filesize', '100M'); // Tamanho máximo do arquivo
        ini_set('post_max_size', '100M');       // Tamanho máximo do POST
        ini_set('max_execution_time', '300');   // Tempo máximo de execução
        ini_set('max_input_time', '300');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
