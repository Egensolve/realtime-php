<?php

declare(strict_types=1);

namespace Egensolve\Realtime\Contracts\Http;

final class HttpSecurity
{
    public const MAX_RESPONSE_BYTES = 1_048_576;

    public static function assertSecureBaseUrl(string $baseUrl, string $label): void
    {
        $parts = \parse_url($baseUrl);
        if (! \is_array($parts) || ! \is_string($parts['scheme'] ?? null) || ! \is_string($parts['host'] ?? null)
            || isset($parts['user']) || isset($parts['pass'])) {
            throw new \InvalidArgumentException($label.' base URL must be an absolute HTTP(S) URL without user information.');
        }

        $scheme = \strtolower($parts['scheme']);
        $host = \strtolower($parts['host']);
        $loopback = \in_array($host, ['localhost', '127.0.0.1', '::1'], true);
        if ($scheme !== 'https' && ! ($scheme === 'http' && $loopback)) {
            throw new \InvalidArgumentException($label.' base URL must use HTTPS except for loopback development hosts.');
        }
    }

    public static function assertResponseBodyWithinLimit(string $body, string $label): void
    {
        if (\strlen($body) > self::MAX_RESPONSE_BYTES) {
            throw new TransportException($label.' response exceeded the maximum size.');
        }
    }
}
