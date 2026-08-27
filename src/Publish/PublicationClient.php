<?php

declare(strict_types=1);

namespace Egensolve\Realtime\Contracts\Publish;

use Egensolve\Realtime\Contracts\Channel\ChannelParser;
use Egensolve\Realtime\Contracts\EnvelopeValidator;
use Egensolve\Realtime\Contracts\ValidationError;
use Egensolve\Realtime\Contracts\ValidationErrorCategory;
use Egensolve\Realtime\Contracts\ValidationErrorCode;

















final readonly class PublicationClient
{
    private const WIRE_PATH = '/api/v1/publications';

    private const MAX_CHANNELS = 16;

    private const MAX_RESPONSE_BYTES = 8192;

    private PublishRequestSigner $signer;

    public function __construct(
        private string $baseUrl,
        string $appKey,
        string $appSecret,
        private Transport $transport,
        private int $timeoutSeconds = 5,
    ) {
        self::assertSecureBaseUrl($baseUrl);
        $this->signer = new PublishRequestSigner($appKey, $appSecret);
    }

    







    public function publish(string $rawEnvelopeJson, array $channels): PublicationResult
    {
        $envelope = EnvelopeValidator::parse($rawEnvelopeJson);

        if ($channels === [] || \count($channels) > self::MAX_CHANNELS) {
            throw ValidationError::make(
                ValidationErrorCode::ChannelInvalidFormat,
                ValidationErrorCategory::Grammar,
                'channels must contain 1-16 entries.',
                'channels',
                ['count' => \count($channels), 'maximum' => self::MAX_CHANNELS],
            );
        }

        foreach ($channels as $channel) {
            ChannelParser::parse($channel);
        }

         
        $rawEnvelope = \json_decode($rawEnvelopeJson, associative: true, flags: JSON_THROW_ON_ERROR);
        $body = (string) \json_encode(['envelope' => $rawEnvelope, 'channels' => $channels], JSON_THROW_ON_ERROR);

        $signed = $this->signer->sign('POST', self::WIRE_PATH, $body);

        $response = $this->transport->send(
            $signed->method,
            $signed->url($this->baseUrl),
            ['Content-Type' => 'application/json'],
            $signed->body,
            $this->timeoutSeconds,
            self::MAX_RESPONSE_BYTES,
        );

        return $this->classify($response, $envelope->id);
    }

    private static function assertSecureBaseUrl(string $baseUrl): void
    {
        $parts = \parse_url($baseUrl);
        if (! \is_array($parts) || ! \is_string($parts['scheme'] ?? null) || ! \is_string($parts['host'] ?? null)) {
            throw new \InvalidArgumentException('Publication base URL must be an absolute HTTP(S) URL.');
        }

        $scheme = \strtolower($parts['scheme']);
        $host = \strtolower($parts['host']);
        $loopback = \in_array($host, ['localhost', '127.0.0.1', '::1'], true);
        if ($scheme !== 'https' && ! ($scheme === 'http' && $loopback)) {
            throw new \InvalidArgumentException('Publication base URL must use HTTPS except for loopback development hosts.');
        }

        if (isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment'])
            || (isset($parts['path']) && $parts['path'] !== '' && $parts['path'] !== '/')) {
            throw new \InvalidArgumentException('Publication base URL must not contain credentials, a path, a query, or a fragment.');
        }
    }

    private function classify(TransportResponse $response, string $eventId): PublicationResult
    {
         
        $decoded = \json_decode($response->body, associative: true) ?? [];
        $code = \is_string($decoded['code'] ?? null) ? $decoded['code'] : null;
        $retryAfter = $response->header('Retry-After');

        return new PublicationResult(
            outcome: self::outcomeFor($response->status, $code),
            httpStatus: $response->status,
            eventId: $eventId,
            errorCode: $code,
            retryAfterSeconds: $retryAfter !== null ? (int) $retryAfter : null,
        );
    }

    private static function outcomeFor(int $status, ?string $code): PublicationOutcome
    {
        return match (true) {
            $status === 202 => PublicationOutcome::Accepted,
            $status === 200 => PublicationOutcome::Replayed,
            $status === 409 && $code === 'publication.event_id_reused_for_different_envelope' => PublicationOutcome::ConflictingReuse,
            $status === 409 => PublicationOutcome::RetryableFailure,
            $status === 429 => PublicationOutcome::RetryableFailure,
            $status >= 500 => PublicationOutcome::RetryableFailure,
            default => PublicationOutcome::RejectedPermanently,
        };
    }
}
