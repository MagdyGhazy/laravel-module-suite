<?php

namespace Ghazym\LaravelModuleSuite\Traits;

use Ghazym\LaravelModuleSuite\Services\ServiceResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

trait ResponseTrait
{
    /**
     * Handel incoming response
     *
     * @param mixed $response
     */
    protected function handleServiceResponse(ServiceResponse $response): \Illuminate\Http\JsonResponse
    {
        if ($response->status >= 200 && $response->status < 300) {
            return $this->successResponse($response->data, $response->message ?? 'Success', $response->status);
        }

        return match ($response->status) {
            401 => $this->unauthorizedResponse($response->message),
            403 => $this->forbiddenResponse($response->message),
            404 => $this->notFoundResponse($response->message),
            422 => $this->validationErrorResponse($response->errors, $response->message),
            default => $this->errorResponse($response->message, $response->status, $response->errors),
        };
    }

    /**
     * Return success response
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function successResponse(mixed $data, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data instanceof JsonResource || $data instanceof AnonymousResourceCollection) {

            $resourceData = $data->response()->getData(true);
            $response['data'] = $resourceData['data'];

            if (isset($resourceData['meta'])) {
                $response['pagination'] = [
                    'total'        => $resourceData['meta']['total'] ?? 0,
                    'per_page'     => $resourceData['meta']['per_page'] ?? 0,
                    'current_page' => $resourceData['meta']['current_page'] ?? 0,
                    'last_page'    => $resourceData['meta']['last_page'] ?? 0,
                    'from'         => $resourceData['meta']['from'] ?? 0,
                    'to'           => $resourceData['meta']['to'] ?? 0,
                ];
            }
        }
        elseif ($data instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $response['data'] = $data->items();
            $response['pagination'] = [
                'total'        => $data->total(),
                'per_page'     => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page'    => $data->lastPage(),
                'from'         => $data->firstItem(),
                'to'           => $data->lastItem()
            ];
        }

        else {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return error response
     *
     * @param string $message
     * @param int $statusCode
     * @param mixed $errors
     * @return JsonResponse
     */
    protected function errorResponse(string $message, int $statusCode = 500, mixed $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return validation error response
     *
     * @param mixed $errors
     * @param string $message
     * @return JsonResponse
     */
    protected function validationErrorResponse(mixed $errors, string $message = 'Validation Error'): JsonResponse
    {
        return $this->errorResponse($message, 422, $errors);
    }

    /**
     * Return not found response
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function notFoundResponse(string $message = 'Resource Not Found'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }

    /**
     * Return unauthorized response
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->errorResponse($message, 401);
    }

    /**
     * Return forbidden response
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function forbiddenResponse(string $message = 'Forbidden'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }

    /**
     * Return server error response
     *
     * @param string $message
     * @param mixed $errors
     * @return JsonResponse
     */
    protected function serverErrorResponse(string $message = 'Server Error', mixed $errors = null): JsonResponse
    {
        return $this->errorResponse($message, 500, $errors);
    }
} 