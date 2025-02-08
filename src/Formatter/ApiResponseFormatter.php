<?php

declare(strict_types=1);

namespace App\Formatter;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiResponseFormatter
{
    private array $data = [];
    private string $message = 'Success';
    private array $errors = [];
    private int $status = Response::HTTP_OK;
    private array $additionalData = [];

    /**
     * Встановлює дані для відповіді.
     */
    public function withData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Встановлює повідомлення для відповіді.
     */
    public function withMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Встановлює помилки для відповіді.
     */
    public function withErrors(array $errors): self
    {
        $this->errors = $errors;
        return $this;
    }

    /**
     * Встановлює HTTP-статус для відповіді.
     */
    public function withStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Встановлює додаткові дані для відповіді.
     */
    public function withAdditionalData(array $additionalData): self
    {
        $this->additionalData = $additionalData;
        return $this;
    }

    /**
     * Формує та повертає JSON-відповідь.
     */
    public function response(): JsonResponse
    {
        $responseData = [
            'data' => $this->data,
            'message' => $this->message,
            'errors' => $this->errors,
            'status' => $this->status,
            'additionalData' => $this->additionalData,
        ];

        // Видаляємо пусті поля, щоб відповідь була більш чистою
        $responseData = array_filter($responseData, fn($value) => !empty($value) || $value === 0 || $value === false);

        return new JsonResponse($responseData, $this->status);
    }
}