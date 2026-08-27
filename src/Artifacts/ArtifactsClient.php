<?php

declare(strict_types=1);

namespace Egensolve\Realtime\Contracts\Artifacts;

use Egensolve\Realtime\Contracts\Http\ApiResponse;
use Egensolve\Realtime\Contracts\Http\CurlTransport;
use Egensolve\Realtime\Contracts\Http\JsonClient;
use Egensolve\Realtime\Contracts\Http\Transport;

final readonly class ArtifactsClient
{
    private JsonClient $http;

    public function __construct(string $baseUrl, string $machineCredential, Transport $transport = new CurlTransport(), int $timeoutSeconds = 5)
    {
        $this->http = new JsonClient($baseUrl, $machineCredential, $transport, $timeoutSeconds);
    }

     
    public function createArtifact(array $artifact, string $idempotencyKey): ApiResponse
    {
        return $this->http->request('POST', '/api/v1/artifacts/', $this->versioned($artifact), $idempotencyKey);
    }

    public function artifact(string $artifactId): ApiResponse
    {
        return $this->http->request('GET', '/api/v1/artifacts/'.$this->id($artifactId));
    }

     
    public function createRevisionUploadIntent(string $artifactId, array $revision, string $idempotencyKey): ApiResponse
    {
        return $this->http->request('POST', '/api/v1/artifacts/'.$this->id($artifactId).'/revisions/upload-intents', $this->versioned($revision), $idempotencyKey);
    }

    public function revision(string $artifactId, string $revisionId): ApiResponse
    {
        return $this->http->request('GET', '/api/v1/artifacts/'.$this->id($artifactId).'/revisions/'.$this->id($revisionId));
    }

     
    public function completeRevision(string $artifactId, string $revisionId, array $completion, string $idempotencyKey): ApiResponse
    {
        return $this->http->request('POST', '/api/v1/artifacts/'.$this->id($artifactId).'/revisions/'.$this->id($revisionId).'/complete', $this->versioned($completion), $idempotencyKey);
    }

    public function publishRevision(string $artifactId, string $revisionId, string $idempotencyKey): ApiResponse
    {
        return $this->http->request('PUT', '/api/v1/artifacts/'.$this->id($artifactId).'/current-revision', ['version' => 1, 'revision_id' => $this->id($revisionId)], $idempotencyKey);
    }

    public function withdrawArtifact(string $artifactId, string $idempotencyKey): ApiResponse
    {
        return $this->http->request('POST', '/api/v1/artifacts/'.$this->id($artifactId).'/withdraw', ['version' => 1], $idempotencyKey);
    }

    



    private function versioned(array $payload): array
    {
        if (isset($payload['version']) && $payload['version'] !== 1) {
            throw new \InvalidArgumentException('Artifacts request version must be 1.');
        }

        return ['version' => 1, ...$payload];
    }

    private function id(string $value): string
    {
        if (\preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/Di', $value) !== 1) {
            throw new \InvalidArgumentException('Artifact ID is invalid.');
        }

        return $value;
    }
}
