<?php

namespace Domain\File\Services;

use Domain\File\Interfaces\Repositories\IFileRepository;
use Domain\File\Interfaces\Services\IFileService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileService implements IFileService
{

    public function __construct(
        private IFileRepository $fileRepository
    )
    {
    }

    /**
     * {@inheritDoc}
     */
    public function downalodFile(string $file_uid): UploadedFile
    {
        $file = $this->fileRepository->findFileBy('file_uid', $file_uid);
        $filename = add_extension($file->get('file_name'), $file->get('extension'));

        $tmpFile = tempnam(sys_get_temp_dir(), 'download-' . now()->toString());
        file_put_contents($tmpFile, utf8_decode($file->get('content_file')));

        return new UploadedFile(
            $tmpFile,
            $filename,
            $file->get('mime_type')
        );
    }
}
