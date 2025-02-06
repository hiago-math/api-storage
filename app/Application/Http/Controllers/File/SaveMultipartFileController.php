<?php

namespace Application\Http\Controllers\File;

use Application\Http\Controllers\Controller;
use Domain\File\Actions\CreateChunkFileAction;
use Illuminate\Http\Request;
use Shared\DTO\Files\CreateChunkFileDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SaveMultipartFileController extends Controller
{
    /**
     * @param Request $request
     * @param CreateChunkFileDTO $dto
     * @param CreateChunkFileAction $action
     * @return JsonResponse
     */
    public function __invoke(
        Request                $request,
        CreateChunkFileDTO     $dto,
        CreateChunkFileAction $action
    ): JsonResponse
    {
        $dto->register(...$request->all());
        $action->execute($dto);

        return $this->response_ok([], 'Arquivo salvo com sucesso!', Response::HTTP_CREATED);
    }
}
