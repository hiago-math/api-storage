<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'api'], function () {

    Route::get('/', function () {
        return response()->json([
            'success' => true,
            'environment' => config('custom.AMBIENTE'),
            'name' => strtoupper(config('custom.PROJETO')),
            'online_time' => time_start_app(),
            'memory' => memory_usage(),
            'db' => [
                'mongodb' => db_mongo_check(),
                'redis' => db_redis_check()
            ],
            'fw' => ['type' => 'laravel', 'version' => app()->version()],
            'php' => [
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
                'client_max_body_size' => config('nginx.client_max_body_size'),
            ]
        ]);
    });

    get_files_routes(__DIR__ . '/api');
});
