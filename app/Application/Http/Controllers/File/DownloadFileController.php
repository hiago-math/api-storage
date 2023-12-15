<?php

namespace Application\Http\Controllers\File;

use Application\Http\Controllers\Controller;
use Domain\File\Interfaces\Services\IFileService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadFileController extends Controller
{
    /**
     * @param string $file_uid
     * @param IFileService $fileService
     * @return BinaryFileResponse
     */
    public function __invoke(
        string       $file_uid,
        IFileService $fileService
    ): BinaryFileResponse
    {
        $downloadedFile = $fileService->downalodFile($file_uid);

        return \response()->download($downloadedFile, $downloadedFile->getClientOriginalName());
    }
}
