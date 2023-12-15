<?php

use Application\Http\Controllers\File\DownloadFileController;
use Application\Http\Controllers\File\SaveFileController;
use Illuminate\Support\Facades\Route;

Route::prefix('file')->name('.file')->group(function () {
    Route::post('', SaveFileController::class)->name('.save');
    Route::get('/download/{file_uid}', DownloadFileController::class)->name('.download');
});
