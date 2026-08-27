<?php

declare(strict_types=1);

namespace Egensolve\Realtime\Contracts;










final class EnvelopeValidator
{
     
     
    public const MAX_ENVELOPE_BYTES = 10240;

     
     
     
     
    private const TOKEN = '/^[a-z][a-z0-9]*(-[a-z0-9]+)*$/D';
    private const DOTTED_TOKEN = '/^[a-z][a-z0-9]*(-[a-z0-9]+)*(\.[a-z][a-z0-9]*(-[a-z0-9]+)*)*$/D';
    private const OPAQUE_ID = '/^[A-Za-z0-9_-]+$/D';
    private const ULID = '/^[0-9A-HJKMNP-TV-Z]{26}$/D';
    private const DATA_URI = '/^data:[^,]*;?base64?,/iD';
    private const BASE64_CHARSET = '/^[A-Za-z0-9+\/]+={0,2}$/D';
    private const BASE64_MIN_LENGTH = 256;
    private const BASE64_MIN_DISTINCT_CHARS = 20;
    private const MAX_PAYLOAD_RECURSION_DEPTH = 10;

     
    public static function parse(string $raw): Envelope
    {
        if (\strlen($raw) > self::MAX_ENVELOPE_BYTES) {
            throw ValidationError::make(
                ValidationErrorCode::EnvelopeTooLarge,
                ValidationErrorCategory::SizeLimit,
                \sprintf('Envelope exceeds the %d byte limit.', self::MAX_ENVELOPE_BYTES),
            );
        }

        try {
             
            $decoded = json_decode($raw, associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw ValidationError::make(
                ValidationErrorCode::EnvelopeInvalidJson,
                ValidationErrorCategory::Parse,
                'Envelope is not valid JSON: ' . $e->getMessage(),
            );
        }

        if (!\is_array($decoded)) {
            throw ValidationError::make(
                ValidationErrorCode::EnvelopeMissingField,
                ValidationErrorCategory::Schema,
                'Envelope must be a JSON object.',
                '/version',
                ['field' => 'version'],
            );
        }

        return self::validateDecoded($decoded);
    }

    




    private static function validateDecoded(array $data): Envelope
    {
        foreach (['version', 'id', 'kind', 'domain', 'type', 'tenant', 'occurred_at', 'payload'] as $field) {
            if (!\array_key_exists($field, $data)) {
                throw ValidationError::make(
                    ValidationErrorCode::EnvelopeMissingField,
                    ValidationErrorCategory::Schema,
                    \sprintf('Missing required field "%s".', $field),
                    '/' . $field,
                    ['field' => $field],
                );
            }
        }

        if (!(\is_int($data['version']) && $data['version'] === 1)) {
            throw ValidationError::make(
                ValidationErrorCode::EnvelopeUnsupportedVersion,
                ValidationErrorCategory::Schema,
                'Unsupported envelope version; this validator only supports version 1.',
                '/version',
                ['field' => 'version'],
            );
        }

        $kind = \is_string($data['kind']) ? EnvelopeKind::tryFrom($data['kind']) : null;
        if ($kind === null) {
            throw ValidationError::make(
                ValidationErrorCode::EnvelopeInvalidKind,
                ValidationErrorCategory::Schema,
                'kind must be "durable" or "ephemeral".',
                '/kind',
                ['field' => 'kind'],
            );
        }

        $id = $data['id'];
        if (!\is_string($id) || preg_match(self::ULID, $id) !== 1) {
            throw ValidationError::make(
                ValidationErrorCode::EnvelopeInvalidId,
                ValidationErrorCategory::Schema,
                'id must be a 26-character Crockford Base32 ULID.',
                '/id',
                ['field' => 'id'],
            );
        }

        $domain = $data['domain'];
        if (!\is_string($domain) || \strlen($domain) > 64 || preg_match(self::DOTTED_TOKEN, $domain) !== 1) {
            throw ValidationError::make(
                ValidationErrorCode::EnvelopeInvalidDomain,
                ValidationErrorCategory::Schema,
                'domain must be a lowercase dotted token of at most 64 characters.',
                '/domain',
                ['field' => 'domain'],
            );
        }

        $type = $data['type'];
        if (!\is_string($type) || \strlen($type) > 64 || preg_match(self::DOTTED_TOKEN, $type) !== 1) {
            throw ValidationError::make(
                ValidationErrorCode::EnvelopeInvalidType,
                ValidationErrorCategory::Schema,
                'type must be a lowercase dotted token of at most 64 characters.',
                '/type',
                ['field' => 'type'],
            );
        }

        $tenant = $data['tenant'];
        if (!\is_string($tenant) || \strlen($tenant) === 0 || \strlen($tenant) > 64 || preg_match(self::OPAQUE_ID, $tenant) !== 1) {
            throw ValidationError::make(
                ValidationErrorCode::EnvelopeInvalidTenant,
                ValidationErrorCategory::Schema,
                'tenant must be 1-64 characters of letters, digits, "_", or "-".',
                '/tenant',
                ['field' => 'tenant'],
            );
        }

        $occurredAtRaw = $data['occurred_at'];
        if (!\is_string($occurredAtRaw) || ($occurredAt = self::parseRfc3339($occurredAtRaw)) === null) {
            throw ValidationError::make(
                ValidationErrorCode::EnvelopeInvalidTimestamp,
                ValidationErrorCategory::Schema,
                'occurred_at must be an RFC 3339 timestamp.',
                '/occurred_at',
                ['field' => 'occurred_at'],
            );
        }

        $subject = null;
        if (\array_key_exists('subject', $data)) {
            $subject = self::validateSubject($data['subject']);
        }

        $recovery = null;
        $expiresAt = null;
        if ($kind === EnvelopeKind::Durable) {
            if (!\array_key_exists('recovery', $data)) {
                throw ValidationError::make(
                    ValidationErrorCode::EnvelopeRecoveryRequired,
                    ValidationErrorCategory::Schema,
                    'Durable events require a recovery object.',
                    '/recovery',
                    ['field' => 'recovery'],
                );
            }
            if (\array_key_exists('expires_at', $data)) {
                throw ValidationError::make(
                    ValidationErrorCode::EnvelopeExpiresAtNotAllowed,
                    ValidationErrorCategory::Schema,
                    'Durable events must not carry expires_at.',
                    '/expires_at',
                    ['field' => 'expires_at'],
                );
            }
            $recovery = self::validateRecovery($data['recovery']);
        } else {
            if (!\array_key_exists('expires_at', $data)) {
                throw ValidationError::make(
                    ValidationErrorCode::EnvelopeExpiresAtRequired,
                    ValidationErrorCategory::Schema,
                    'Ephemeral events require expires_at.',
                    '/expires_at',
                    ['field' => 'expires_at'],
                );
            }
            if (\array_key_exists('recovery', $data)) {
                throw ValidationError::make(
                    ValidationErrorCode::EnvelopeRecoveryNotAllowed,
                    ValidationErrorCategory::Schema,
                    'Ephemeral events must not carry recovery.',
                    '/recovery',
                    ['field' => 'recovery'],
                );
            }
            $expiresAtRaw = $data['expires_at'];
            if (!\is_string($expiresAtRaw) || ($expiresAt = self::parseRfc3339($expiresAtRaw)) === null) {
                throw ValidationError::make(
                    ValidationErrorCode::EnvelopeInvalidTimestamp,
                    ValidationErrorCategory::Schema,
                    'expires_at must be an RFC 3339 timestamp.',
                    '/expires_at',
                    ['field' => 'expires_at'],
                );
            }
        }

        $payload = $data['payload'];
        if (!\is_array($payload) || (array_is_list($payload) && $payload !== [])) {
            throw ValidationError::make(
                ValidationErrorCode::EnvelopePayloadNotObject,
                ValidationErrorCategory::Schema,
                'payload must be a JSON object.',
                '/payload',
                ['field' => 'payload'],
            );
        }

        if (self::containsBinaryLikeString($payload, 0)) {
            throw ValidationError::make(
                ValidationErrorCode::EnvelopePayloadBinaryRejected,
                ValidationErrorCategory::Schema,
                'payload contains binary/media-like content, which is never transported over the realtime channel.',
                '/payload',
                ['field' => 'payload'],
            );
        }

        return new Envelope(
            version: 1,
            id: $id,
            kind: $kind,
            domain: $domain,
            type: $type,
            tenant: $tenant,
            occurredAt: $occurredAt,
            subject: $subject,
            recovery: $recovery,
            expiresAt: $expiresAt,
            payload: $payload,
        );
    }

     
    private static function validateSubject(mixed $value): Subject
    {
        if (
            !\is_array($value)
            || !\is_string($value['type'] ?? null)
            || \strlen($value['type']) === 0
            || \strlen($value['type']) > 64
            || preg_match(self::TOKEN, $value['type']) !== 1
            || !\is_string($value['id'] ?? null)
            || \strlen($value['id']) === 0
            || \strlen($value['id']) > 128
        ) {
            throw ValidationError::make(
                ValidationErrorCode::EnvelopeInvalidSubject,
                ValidationErrorCategory::Schema,
                'subject must be {type: lowercase token, id: 1-128 character string}.',
                '/subject',
                ['field' => 'subject'],
            );
        }

        return new Subject(type: $value['type'], id: $value['id']);
    }

     
    private static function validateRecovery(mixed $value): Recovery
    {
        if (
            !\is_array($value)
            || !\is_string($value['scope'] ?? null)
            || \strlen($value['scope']) === 0
            || \strlen($value['scope']) > 64
            || preg_match(self::TOKEN, $value['scope']) !== 1
            || !\is_string($value['cursor'] ?? null)
            || \strlen($value['cursor']) === 0
            || \strlen($value['cursor']) > 128
        ) {
            throw ValidationError::make(
                ValidationErrorCode::EnvelopeInvalidRecovery,
                ValidationErrorCategory::Schema,
                'recovery must be {scope: lowercase token, cursor: 1-128 character string}.',
                '/recovery',
                ['field' => 'recovery'],
            );
        }

        return new Recovery(scope: $value['scope'], cursor: $value['cursor']);
    }

    private static function parseRfc3339(string $value): ?\DateTimeImmutable
    {
        if (preg_match(
            '/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(?:\.\d+)?(Z|[+-]\d{2}:\d{2})$/D',
            $value,
            $m,
        ) !== 1) {
            return null;
        }
        [, $year, $month, $day, $hour, $minute, $second] = $m;
        if (!checkdate((int) $month, (int) $day, (int) $year)) {
            return null;
        }
        if ((int) $hour > 23 || (int) $minute > 59 || (int) $second > 60) {
            return null;
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Exception) {
            return null;
        }
    }

    private static function containsBinaryLikeString(mixed $value, int $depth): bool
    {
        if ($depth > self::MAX_PAYLOAD_RECURSION_DEPTH) {
            return false;
        }
        if (\is_string($value)) {
            return self::looksLikeBinary($value);
        }
        if (\is_array($value)) {
            foreach ($value as $item) {
                if (self::containsBinaryLikeString($item, $depth + 1)) {
                    return true;
                }
            }
        }

        return false;
    }

    private static function looksLikeBinary(string $value): bool
    {
        if (preg_match(self::DATA_URI, $value) === 1) {
            return true;
        }
        if (\strlen($value) < self::BASE64_MIN_LENGTH || preg_match(self::BASE64_CHARSET, $value) !== 1) {
            return false;
        }
        $withoutPadding = rtrim($value, '=');
        $distinct = \count(array_unique(str_split($withoutPadding)));

        return $distinct >= self::BASE64_MIN_DISTINCT_CHARS;
    }
}
