<?php

declare(strict_types=1);

namespace Egensolve\Realtime\Contracts\Http;

interface Transport
{
     
    public function send(string $method, string $url, array $headers, ?string $body, int $timeoutSeconds, int $maxResponseBytes): TransportResponse;
}
