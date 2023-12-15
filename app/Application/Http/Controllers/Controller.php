<?php

namespace Application\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @param $data
     * @param bool $status
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function response_api($data, bool $status = true, string $message, int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => $status,
            'message' => $message,
            'data' => is_array($data) ? $data : array($data)
        ], $statusCode);
    }

    /**
     * @param $data
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function response_ok($data, string $message, int $statusCode = 200): JsonResponse
    {
        return $this->response_api($data, true, $message, $statusCode);
    }

    /**
     * @param $data
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function response_fail($data, string $message, int $statusCode = 400): JsonResponse
    {
        return $this->response_api($data, false, $message, $statusCode);
    }
}
