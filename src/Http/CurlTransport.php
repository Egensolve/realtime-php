<?php

declare(strict_types=1);

namespace Egensolve\Realtime\Contracts\Http;

final class CurlTransport implements Transport
{
    public function send(string $method, string $url, array $headers, ?string $body, int $timeoutSeconds, int $maxResponseBytes): TransportResponse
    {
        if (! \extension_loaded('curl')) {
            throw new TransportException('The default HTTP transport requires ext-curl; inject another Transport when unavailable.');
        }
        if ($method === '' || $maxResponseBytes < 1) {
            throw new TransportException('An ERS HTTP HTTP method is required.');
        }
        $handle = \curl_init($url);
        if ($handle === false) {
            throw new TransportException('Failed to initialize the ERS HTTP request.');
        }
        $lines = [];
        foreach ($headers as $name => $value) {
            $lines[] = "{$name}: {$value}";
        }
        $responseHeaders = [];
        $responseBody = '';
        $responseTooLarge = false;
        $options = [
            \CURLOPT_CUSTOMREQUEST => $method,
            \CURLOPT_HTTPHEADER => $lines,
            \CURLOPT_TIMEOUT => $timeoutSeconds,
            \CURLOPT_HEADERFUNCTION => static function ($curl, string $line) use (&$responseHeaders): int {
                $parts = \explode(':', $line, 2);
                if (\count($parts) === 2) {
                    $responseHeaders[\strtolower(\trim($parts[0]))] = \trim($parts[1]);
                }

                return \strlen($line);
            },
            \CURLOPT_WRITEFUNCTION => static function ($curl, string $chunk) use (&$responseBody, &$responseTooLarge, $maxResponseBytes): int {
                $chunkBytes = \strlen($chunk);
                if (\strlen($responseBody) + $chunkBytes > $maxResponseBytes) {
                    $responseTooLarge = true;

                    return 0;
                }
                $responseBody .= $chunk;

                return $chunkBytes;
            },
        ];
        if ($body !== null) {
            $options[\CURLOPT_POSTFIELDS] = $body;
        }
        \curl_setopt_array($handle, $options);
        $completed = \curl_exec($handle);
        $errorNumber = \curl_errno($handle);
        $status = (int) \curl_getinfo($handle, \CURLINFO_RESPONSE_CODE);
        if ($responseTooLarge) {
            throw new TransportException('ERS HTTP response exceeded the maximum size.');
        }
        if ($errorNumber !== 0 || $completed !== true) {
            throw new TransportException('ERS HTTP request failed before a response was received.');
        }

        return new TransportResponse($status, $responseHeaders, $responseBody);
    }
}
