<?php

declare(strict_types=1);

namespace Egensolve\Realtime\Contracts\Media;

use Egensolve\Realtime\Contracts\Http\ApiResponse;
use Egensolve\Realtime\Contracts\Http\CurlTransport;
use Egensolve\Realtime\Contracts\Http\JsonClient;
use Egensolve\Realtime\Contracts\Http\Transport;

final readonly class MediaClient
{
    private JsonClient $http;

    public function __construct(string $baseUrl, string $machineCredential, Transport $transport = new CurlTransport(), int $timeoutSeconds = 5)
    {
        $this->http = new JsonClient($baseUrl, $machineCredential, $transport, $timeoutSeconds);
    }

     
    public function createUploadIntent(array $intent, string $idempotencyKey): ApiResponse
    {
        return $this->http->request('POST', '/api/v1/media/assets/upload-intents', $this->versioned($intent), $idempotencyKey);
    }

    public function asset(string $assetId): ApiResponse
    {
        return $this->http->request('GET', '/api/v1/media/assets/'.$this->id($assetId));
    }

     
    public function createMultipartPartIntent(string $assetId, int $partNumber, array $part, string $idempotencyKey): ApiResponse
    {
        if ($partNumber < 1 || $partNumber > 10_000) {
            throw new \InvalidArgumentException('Multipart part number is invalid.');
        }

        return $this->http->request('POST', '/api/v1/media/assets/'.$this->id($assetId).'/multipart-parts/'.$partNumber.'/intent', $this->versioned($part), $idempotencyKey);
    }

     
    public function completeUpload(string $assetId, array $completion, string $idempotencyKey): ApiResponse
    {
        return $this->http->request('POST', '/api/v1/media/assets/'.$this->id($assetId).'/complete', $this->versioned($completion), $idempotencyKey);
    }

     
    public function createDownloadGrant(string $assetId, array $grant, string $idempotencyKey): ApiResponse
    {
        return $this->http->request('POST', '/api/v1/media/assets/'.$this->id($assetId).'/download-grants', $this->versioned($grant), $idempotencyKey);
    }

    public function deleteAsset(string $assetId, string $idempotencyKey): ApiResponse
    {
        return $this->http->request('DELETE', '/api/v1/media/assets/'.$this->id($assetId), ['version' => 1], $idempotencyKey);
    }

    



    private function versioned(array $payload): array
    {
        if (isset($payload['version']) && $payload['version'] !== 1) {
            throw new \InvalidArgumentException('Media request version must be 1.');
        }

        return ['version' => 1, ...$payload];
    }

    private function id(string $value): string
    {
        if (\preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/Di', $value) !== 1) {
            throw new \InvalidArgumentException('Media asset ID is invalid.');
        }

        return $value;
    }
}
