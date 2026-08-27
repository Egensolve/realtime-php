<?php

declare(strict_types=1);

namespace Egensolve\Realtime\Contracts\Publish;








final class CurlTransport implements Transport
{
    


    public function send(
        string $method,
        string $url,
        array $headers,
        string $body,
        int $timeoutSeconds,
        int $maxResponseBytes,
    ): TransportResponse {
        $method = \strtoupper($method);

        if ($method === '') {
            throw new PublicationTransportException('An HTTP method is required.');
        }

        $handle = \curl_init($url);

        if ($handle === false) {
            throw new PublicationTransportException('Failed to initialize the HTTP request.');
        }

        $headerLines = [];
        foreach ($headers as $name => $value) {
            $headerLines[] = "{$name}: {$value}";
        }

        if ($maxResponseBytes < 1) {
            throw new PublicationTransportException('The response size limit must be positive.');
        }

        $responseHeaders = [];
        $responseBody = '';
        $responseExceededLimit = false;

        \curl_setopt_array($handle, [
            \CURLOPT_CUSTOMREQUEST => $method,
            \CURLOPT_POSTFIELDS => $body,
            \CURLOPT_HTTPHEADER => $headerLines,
            \CURLOPT_TIMEOUT => $timeoutSeconds,
            \CURLOPT_HEADERFUNCTION => function ($curl, string $line) use (&$responseHeaders): int {
                $parts = \explode(':', $line, 2);
                if (\count($parts) === 2) {
                    $responseHeaders[\strtolower(\trim($parts[0]))] = \trim($parts[1]);
                }

                return \strlen($line);
            },
            \CURLOPT_WRITEFUNCTION => function ($curl, string $chunk) use (&$responseBody, &$responseExceededLimit, $maxResponseBytes): int {
                if (\strlen($responseBody) + \strlen($chunk) > $maxResponseBytes) {
                    $responseExceededLimit = true;

                    return 0;
                }

                $responseBody .= $chunk;

                return \strlen($chunk);
            },
        ]);

        $completed = \curl_exec($handle);
        $errorNumber = \curl_errno($handle);
        $status = (int) \curl_getinfo($handle, \CURLINFO_RESPONSE_CODE);
        unset($handle);

        if ($responseExceededLimit) {
            throw new PublicationTransportException(\sprintf('Publish response exceeded the %d-byte limit.', $maxResponseBytes));
        }

        if ($errorNumber !== 0 || $completed !== true) {
            throw new PublicationTransportException(\sprintf('Publish request failed (curl errno %d).', $errorNumber));
        }

        return new TransportResponse($status, $responseHeaders, $responseBody);
    }
}
