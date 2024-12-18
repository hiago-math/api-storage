<?php

namespace Shared\DTO\Files;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use MongoDB\BSON\Binary;
use Shared\DTO\DTOAbstract;
use Symfony\Component\HttpFoundation\File\File;

class CreateFileDTO extends DTOAbstract
{
    /** @var UploadedFile|File  */
    public UploadedFile|File $file;

    /** @var string */
    public string $file_uid;

    /** @var string */
    public string $file_name;
    /** @var string */
    public string $extension;

    /** @var string */
    public string $url;

    /** @var string */
    public string $size;

    /** @var string */
    public string $hash_file;

    /** @var string */
    public string $content_file;

    /** @var string */
    public string $mime_type;

    public function register(UploadedFile|File $file): self
    {
        $this->file = $file;

        return $this;
    }
}
