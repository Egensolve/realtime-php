<?php

declare(strict_types=1);

namespace Egensolve\Realtime\Contracts\Channel;

use Egensolve\Realtime\Contracts\ValidationError;
use Egensolve\Realtime\Contracts\ValidationErrorCategory;
use Egensolve\Realtime\Contracts\ValidationErrorCode;








final class ChannelGrammar
{
    public const MAX_LENGTH = 164;

     
     
    private const TOKEN = '/^[a-z][a-z0-9]*(-[a-z0-9]+)*$/D';
    private const OPAQUE_ID = '/^[A-Za-z0-9_-]+$/D';

     
    public static function requireOpaqueId(string $value, string $fieldName, int $maxLength = 64): string
    {
        if ($value === '' || \strlen($value) > $maxLength || preg_match(self::OPAQUE_ID, $value) !== 1) {
            throw ValidationError::make(
                ValidationErrorCode::ChannelInvalidSegment,
                ValidationErrorCategory::Grammar,
                \sprintf('%s must be 1-%d characters of letters, digits, "_", or "-".', $fieldName, $maxLength),
                null,
                ['field' => $fieldName],
            );
        }

        return $value;
    }

     
    public static function requireToken(string $value, string $fieldName, int $maxLength = 32): string
    {
        if ($value === '' || \strlen($value) > $maxLength || preg_match(self::TOKEN, $value) !== 1) {
            throw ValidationError::make(
                ValidationErrorCode::ChannelInvalidSegment,
                ValidationErrorCategory::Grammar,
                \sprintf('%s must be a lowercase ASCII token of at most %d characters.', $fieldName, $maxLength),
                null,
                ['field' => $fieldName],
            );
        }

        return $value;
    }

     
    public static function requireWithinMaxLength(string $channel): string
    {
        if (\strlen($channel) > self::MAX_LENGTH) {
            throw ValidationError::make(
                ValidationErrorCode::ChannelTooLong,
                ValidationErrorCategory::Grammar,
                \sprintf('Channel name exceeds the %d character limit.', self::MAX_LENGTH),
            );
        }

        return $channel;
    }
}
