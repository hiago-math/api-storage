<?php

namespace Domain\File\Interfaces\Services;

use Illuminate\Http\UploadedFile;

interface IFileService
{
    /**
     * @param string $uid
     * @return UploadedFile
     */
    public function downalodFile(string $uid): UploadedFile;

    public function getBinaryFile(string $uid);
}
