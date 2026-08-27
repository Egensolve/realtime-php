<?php

declare(strict_types=1);

namespace Egensolve\Realtime\Contracts\Publish;






final readonly class SignedPublicationRequest
{
    public function __construct(
        public string $method,
        public string $path,
        public string $queryString,
        public string $body,
    ) {
    }

    public function url(string $baseUrl): string
    {
        return \rtrim($baseUrl, '/').$this->path.'?'.$this->queryString;
    }
}
