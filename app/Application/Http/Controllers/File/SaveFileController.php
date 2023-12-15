<?php

namespace Application\Http\Controllers\File;

use Application\Http\Controllers\Controller;
use Domain\File\Interfaces\Repositories\IFileRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Shared\DTO\Files\CreateFileDTO;
use Symfony\Component\HttpFoundation\JsonResponse;

class SaveFileController extends Controller
{
    /**
     * @param Request $request
     * @param CreateFileDTO $createFileDTO
     * @param IFileRepository $fileRepository
     * @return JsonResponse
     */
    public function __invoke(
        Request $request,
        CreateFileDTO $createFileDTO,
        IFileRepository $fileRepository
    ): JsonResponse
    {
        $createFileDTO->register(...$request->all());
        $fileRepository->createFile($createFileDTO);

        return $this->response_ok($createFileDTO->toArray(only: ['file_uid']), 'Arquivo salvo com sucesso!', Response::HTTP_CREATED);
    }
}
