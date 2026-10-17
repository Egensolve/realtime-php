<?php

declare(strict_types=1);

namespace Egensolve\Realtime\Contracts\Http;

final readonly class JsonClient
{
    public function __construct(
        private string $baseUrl,
        private string $machineCredential,
        private Transport $transport = new CurlTransport(),
        private int $timeoutSeconds = 5,
    ) {
        HttpSecurity::assertSecureBaseUrl($baseUrl, 'ERS');
        if (\strlen($machineCredential) < 32 || \strlen($machineCredential) > 512) {
            throw new \InvalidArgumentException('ERS machine credential length is invalid.');
        }
        if ($timeoutSeconds < 1 || $timeoutSeconds > 30) {
            throw new \InvalidArgumentException('ERS timeout must be between 1 and 30 seconds.');
        }
    }

     
    public function request(string $method, string $path, ?array $payload = null, ?string $idempotencyKey = null): ApiResponse
    {
        $method = \strtoupper($method);
        if (! \in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], true)
            || ! \preg_match('#^/api/v1/(?:media|artifacts|intelligence)/[A-Za-z0-9._~!$&\'()*+,;=:@%/-]*$#D', $path)) {
            throw new \InvalidArgumentException('ERS HTTP method or path is invalid.');
        }
        $headers = ['Accept' => 'application/json', 'Authorization' => 'Bearer '.$this->machineCredential];
        if ($payload !== null) {
            $headers['Content-Type'] = 'application/json';
        }
        if ($idempotencyKey !== null) {
            if (\preg_match('/^[A-Za-z0-9._:-]{1,128}$/D', $idempotencyKey) !== 1) {
                throw new \InvalidArgumentException('Idempotency key is invalid.');
            }
            $headers['Idempotency-Key'] = $idempotencyKey;
        }
        $body = $payload === null ? null : (string) \json_encode($payload, JSON_THROW_ON_ERROR);
        $response = $this->transport->send(
            $method,
            \rtrim($this->baseUrl, '/').$path,
            $headers,
            $body,
            $this->timeoutSeconds,
            HttpSecurity::MAX_RESPONSE_BYTES,
        );
        HttpSecurity::assertResponseBodyWithinLimit($response->body, 'ERS HTTP');
        $decoded = \json_decode($response->body, true);
        if (! \is_array($decoded)) {
            throw new TransportException('ERS returned a non-JSON HTTP response.');
        }
        if ($response->status >= 200 && $response->status < 300) {
            return new ApiResponse($response->status, $decoded, $response->header('x-idempotency-replayed') === 'true');
        }
        throw new ApiException(
            $response->status,
            \is_string($decoded['code'] ?? null) ? $decoded['code'] : 'ers.control_plane_error',
            \is_string($decoded['message'] ?? null) ? $decoded['message'] : 'ERS HTTP request failed.',
            \is_array($decoded['details'] ?? null) ? $decoded['details'] : [],
        );
    }
}
