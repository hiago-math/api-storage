<?php

namespace Shared\DTO\Files;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use MongoDB\BSON\Binary;
use Shared\DTO\DTOAbstract;
use Symfony\Component\HttpFoundation\File\File;

class CreateChunkFileDTO extends DTOAbstract
{
    /** @var UploadedFile|File  */
    public UploadedFile|File $file;

    /** @var int  */
   public int $chunk_number;

   /** @var int  */
   public int $total_chunks;

   /** @var string  */
   public string $filename;

    public function register(UploadedFile|File $file, int $chunk_number, int $total_chunks, $filename): self
    {
        $this->file = $file;
        $this->chunk_number = $chunk_number;
        $this->total_chunks = $total_chunks;
        $this->filename = $filename;

        return $this;
    }
}
