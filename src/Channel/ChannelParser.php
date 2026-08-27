<?php

declare(strict_types=1);

namespace Egensolve\Realtime\Contracts\Channel;

use Egensolve\Realtime\Contracts\ValidationError;
use Egensolve\Realtime\Contracts\ValidationErrorCategory;
use Egensolve\Realtime\Contracts\ValidationErrorCode;






final class ChannelParser
{
     
    public static function parse(string $channel): Channel
    {
        ChannelGrammar::requireWithinMaxLength($channel);

        $parts = explode('.', $channel);
        $count = \count($parts);

        if ($count === 6 && $parts[0] === 'private-v1' && $parts[1] === 'tenants' && $parts[3] === 'accounts') {
            return AccountChannel::for(tenant: $parts[2], actorType: $parts[4], actorId: $parts[5]);
        }
        if ($count === 5 && $parts[0] === 'private-v1' && $parts[1] === 'tenants' && $parts[3] === 'devices') {
            return DeviceChannel::for(tenant: $parts[2], deviceId: $parts[4]);
        }
        if ($count === 6 && $parts[0] === 'presence-v1' && $parts[1] === 'tenants' && $parts[3] === 'rooms') {
            return RoomChannel::for(tenant: $parts[2], roomType: $parts[4], roomId: $parts[5]);
        }
        if ($count === 5 && $parts[0] === 'private-v1' && $parts[1] === 'tenants' && $parts[3] === 'systems') {
            return SystemChannel::for(tenant: $parts[2], scope: $parts[4]);
        }

        throw ValidationError::make(
            ValidationErrorCode::ChannelInvalidFormat,
            ValidationErrorCategory::Grammar,
            'Channel name does not match any of the four supported v1 shapes.',
        );
    }
}
