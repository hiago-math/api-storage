<?php

namespace Domain\File\Actions;

use Domain\File\Interfaces\Repositories\IFileRepository;
use Domain\File\Jobs\CreateFileJob;
use Illuminate\Support\Facades\Storage;
use Shared\DTO\Files\CreateFileDTO;

class CreateFileAction
{
    public function execute(CreateFileDTO $dto)
    {
        Storage::disk('files')->put($dto->file->getClientOriginalName(), utf8_encode($dto->file->getContent()));

        CreateFileJob::dispatch($dto->file->getClientOriginalName());
    }
}
