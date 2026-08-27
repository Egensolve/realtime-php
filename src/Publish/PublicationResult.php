<?php

declare(strict_types=1);

namespace Egensolve\Realtime\Contracts\Publish;






final readonly class PublicationResult
{
    public function __construct(
        public PublicationOutcome $outcome,
        public int $httpStatus,
        public string $eventId,
        public ?string $errorCode = null,
        public ?int $retryAfterSeconds = null,
    ) {
    }

    public function isRetryable(): bool
    {
        return $this->outcome === PublicationOutcome::RetryableFailure;
    }
}
