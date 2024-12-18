<?php

namespace Domain\File\Actions;

use Domain\File\Interfaces\Repositories\IFileRepository;
use Domain\File\Jobs\CreateFileJob;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Shared\DTO\Files\CreateChunkFileDTO;
use Shared\DTO\Files\CreateFileDTO;

class CreateChunkFileAction
{
    public function execute(CreateChunkFileDTO $dto)
    {
        $chunkPath = Storage::disk('files')->path('uploads/');
        $dto->file->move($chunkPath, $dto->filename . '.part' . $dto->chunk_number);
        if ($dto->chunk_number + 1 == $dto->total_chunks) {
            $this->mergeFileParts($chunkPath, $dto->filename, $dto->total_chunks);
        }
    }

    private function mergeFileParts($directory, $fileName, $totalChunks)
    {
        $finalPath = $directory . $fileName;
        $file = fopen($finalPath, 'wb');
        for ($i = 0; $i < $totalChunks; $i++) {
            $chunkPath = $directory . $fileName . '.part' . $i;
            $chunk = fopen($chunkPath, 'rb');
            stream_copy_to_stream($chunk, $file);
            fclose($chunk);
            unlink($chunkPath);
        }
        fclose($file);
        Storage::disk('files')->put('file_merged/' .$fileName, file_get_contents($finalPath));
        unlink($finalPath);
    }
}
