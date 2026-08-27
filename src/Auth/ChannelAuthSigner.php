<?php

declare(strict_types=1);

namespace Egensolve\Realtime\Contracts\Auth;

use Egensolve\Realtime\Contracts\Channel\Channel;
use Egensolve\Realtime\Contracts\Channel\RoomChannel;
use Egensolve\Realtime\Contracts\ValidationError;
use Egensolve\Realtime\Contracts\ValidationErrorCategory;
use Egensolve\Realtime\Contracts\ValidationErrorCode;


















final class ChannelAuthSigner
{
    public function __construct(
        private readonly string $appKey,
        private readonly string $appSecret,
    ) {
    }

     
    public function sign(SocketId $socketId, Channel $channel, ?PresenceMemberData $memberData = null): ChannelAuthorization
    {
        $isPresenceChannel = $channel instanceof RoomChannel;

        if ($isPresenceChannel && $memberData === null) {
            throw ValidationError::make(
                ValidationErrorCode::AuthPresenceDataRequired,
                ValidationErrorCategory::Grammar,
                'A presence channel subscription requires member data to sign.',
            );
        }
        if (!$isPresenceChannel && $memberData !== null) {
            throw ValidationError::make(
                ValidationErrorCode::AuthPresenceDataNotAllowed,
                ValidationErrorCategory::Grammar,
                'Only a presence channel subscription may carry member data.',
            );
        }

        $channelData = $memberData?->toJson();
        $stringToSign = "{$socketId->value}:{$channel->toString()}";
        if ($channelData !== null) {
            $stringToSign .= ":{$channelData}";
        }

        $signature = hash_hmac('sha256', $stringToSign, $this->appSecret);

        return new ChannelAuthorization("{$this->appKey}:{$signature}", $channelData);
    }
}
