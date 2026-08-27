<?php

declare(strict_types=1);

namespace Egensolve\Realtime\Contracts\Http;

final readonly class ApiResponse
{
     
    public function __construct(
        public int $status,
        public array $data,
        public bool $idempotencyReplayed,
    ) {
    }
}
