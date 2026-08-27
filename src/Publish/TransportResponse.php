<?php

declare(strict_types=1);

namespace Egensolve\Realtime\Contracts\Publish;






final readonly class TransportResponse
{
    


    public function __construct(
        public int $status,
        public array $headers,
        public string $body,
    ) {
    }

    public function header(string $name): ?string
    {
        return $this->headers[\strtolower($name)] ?? null;
    }
}
