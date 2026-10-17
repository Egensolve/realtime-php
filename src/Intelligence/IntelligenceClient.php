<?php

declare(strict_types=1);

namespace Egensolve\Realtime\Contracts\Intelligence;

use Egensolve\Realtime\Contracts\Http\ApiResponse;
use Egensolve\Realtime\Contracts\Http\CurlTransport;
use Egensolve\Realtime\Contracts\Http\JsonClient;
use Egensolve\Realtime\Contracts\Http\Transport;

 
final readonly class IntelligenceClient
{
    private JsonClient $http;

    public function __construct(string $baseUrl, string $machineCredential, Transport $transport = new CurlTransport(), int $timeoutSeconds = 5)
    {
        $this->http = new JsonClient($baseUrl, $machineCredential, $transport, $timeoutSeconds);
    }

    public function profiles(): ApiResponse
    {
        return $this->http->request('GET', '/api/v1/intelligence/profiles');
    }

     
    public function createJob(array $request, string $idempotencyKey): ApiResponse
    {
        return $this->http->request('POST', '/api/v1/intelligence/jobs', $this->versioned($request), $idempotencyKey);
    }

    public function job(string $jobId): ApiResponse
    {
        return $this->http->request('GET', '/api/v1/intelligence/jobs/'.$this->id($jobId));
    }

    public function cancelJob(string $jobId, string $idempotencyKey): ApiResponse
    {
        return $this->http->request('POST', '/api/v1/intelligence/jobs/'.$this->id($jobId).'/cancel', ['version' => 1], $idempotencyKey);
    }

     
    public function createInstructionSet(array $revision, string $idempotencyKey): ApiResponse
    {
        return $this->http->request('POST', '/api/v1/intelligence/instruction-sets', $this->versioned($revision), $idempotencyKey);
    }

     
    public function addInstructionRevision(string $setId, array $revision, string $idempotencyKey): ApiResponse
    {
        return $this->http->request('POST', '/api/v1/intelligence/instruction-sets/'.$this->id($setId).'/revisions', $this->versioned($revision), $idempotencyKey);
    }

    public function instructionSet(string $setId): ApiResponse
    {
        return $this->http->request('GET', '/api/v1/intelligence/instruction-sets/'.$this->id($setId));
    }

    


    private function versioned(array $payload): array
    {
        if (isset($payload['version']) && $payload['version'] !== 1) {
            throw new \InvalidArgumentException('Intelligence request version must be 1.');
        }

        return ['version' => 1, ...$payload];
    }

    private function id(string $id): string
    {
        if (\preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/Di', $id) !== 1) {
            throw new \InvalidArgumentException('Intelligence ID is invalid.');
        }

        return $id;
    }
}
