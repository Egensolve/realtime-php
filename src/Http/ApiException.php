<?php

declare(strict_types=1);

namespace Egensolve\Realtime\Contracts\Http;

final class ApiException extends \RuntimeException
{
     
    public function __construct(
        public readonly int $httpStatus,
        public readonly string $errorCode,
        string $message,
        public readonly array $details,
    ) {
        parent::__construct($message);
    }

    public function isRetryable(): bool
    {
        return $this->httpStatus === 429 || $this->httpStatus >= 500;
    }
}
