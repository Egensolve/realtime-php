<?php

declare(strict_types=1);

namespace Egensolve\Realtime\Contracts\Auth;

use Egensolve\Realtime\Contracts\ValidationError;
use Egensolve\Realtime\Contracts\ValidationErrorCategory;
use Egensolve\Realtime\Contracts\ValidationErrorCode;






final class SocketId
{
    private const MAX_LENGTH = 64;

     
     
    private const PATTERN = '/^\d+\.\d+$/D';

    private function __construct(public readonly string $value)
    {
    }

     
    public static function fromString(string $value): self
    {
        if ($value === '' || \strlen($value) > self::MAX_LENGTH || preg_match(self::PATTERN, $value) !== 1) {
            throw ValidationError::make(
                ValidationErrorCode::AuthInvalidSocketId,
                ValidationErrorCategory::Grammar,
                \sprintf('socket_id must match "{digits}.{digits}" and be at most %d characters.', self::MAX_LENGTH),
            );
        }

        return new self($value);
    }
}
