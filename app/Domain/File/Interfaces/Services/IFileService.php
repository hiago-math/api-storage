<?php

namespace Domain\File\Interfaces\Services;

use Illuminate\Http\UploadedFile;

interface IFileService
{
    /**
     * @param string $file_uid
     * @return UploadedFile
     */
    public function downalodFile(string $file_uid): UploadedFile;
}
