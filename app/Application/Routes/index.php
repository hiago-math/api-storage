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
            'fw' => ['type' => 'laravel', 'version' => app()->version()]
        ]);
    });

    get_files_routes(__DIR__ . '/api');
});
