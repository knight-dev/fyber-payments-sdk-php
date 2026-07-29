<?php

namespace Fyber\Exceptions;

use Exception;

class FyberException extends Exception
{
    protected string $type;
    protected ?string $errorCode;
    protected int $statusCode;
    protected mixed $details;

    public function __construct(
        string $message,
        string $type = 'api_error',
        ?string $errorCode = null,
        int $statusCode = 400,
        mixed $details = null
    ) {
        parent::__construct($message);
        $this->type = $type;
        $this->errorCode = $errorCode;
        $this->statusCode = $statusCode;
        $this->details = $details;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getDetails(): mixed
    {
        return $this->details;
    }

    public static function fromResponse(array $response, int $statusCode): self
    {
        return new self(
            $response['message'] ?? 'An error occurred',
            $response['type'] ?? 'api_error',
            $response['code'] ?? null,
            $statusCode,
            $response['details'] ?? null
        );
    }
}
