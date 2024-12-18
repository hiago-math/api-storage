<?php

namespace Domain\File\Jobs;

use Domain\File\Interfaces\Repositories\IFileRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use MongoDB\BSON\Binary;

class CreateFileJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    public $tries = 0;

    public function __construct(
        private string $path
    )
    {
    }

    public function handle(
        IFileRepository $fileRepository
    )
    {
        try {
            $bin = Storage::disk('files')->get($this->path);
            $bin = compress_binary_file($bin);
            dd($bin);
        } catch (\Throwable $e) {

            dd($e->getMessage());
        }
    }

}
