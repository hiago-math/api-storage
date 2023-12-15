<?php

namespace Shared\DTO\Files;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Shared\DTO\DTOAbstract;
use Symfony\Component\HttpFoundation\File\File;

class CreateFileDTO extends DTOAbstract
{
    /**
     * @var string
     */
    public string $file_uid;

    /**
     * @var string
     */
    public string $file_name;

    /**
     * @var string
     */
    public string $extension;

    /**
     * @var string
     */
    public string $url;

    /**
     * @var string
     */
    public string $size;

    /**
     * @var string
     */
    public string $hash_file;

    /**
     * @var string
     */
    public string $content_file;

    /**
     * @var string
     */
    public string $mime_type;

    public function register(UploadedFile|File $file): self
    {
        $this->file_uid = Str::uuid();
        $this->file_name = $file->getClientOriginalName();
        $this->extension = $file->getClientOriginalExtension();
        $this->mime_type = $file->getClientMimeType();
        $this->url = '';
        $this->size = $file->getSize();
        $this->content_file = utf8_encode($file->getContent());
        $this->hash_file = get_hash_file($this->content_file);

        return $this;
    }
}
