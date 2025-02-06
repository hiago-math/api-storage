<?php

use Application\Http\Controllers\File\DownloadFileController;
use Application\Http\Controllers\File\SaveFileController;
use Illuminate\Support\Facades\Route;

Route::prefix('file')->name('.file')->group(function () {
    Route::post('', \Application\Http\Controllers\File\SaveMultipartFileController::class)->name('.save');
    Route::get('/{file_uid}/download', DownloadFileController::class)->name('.download');
});
