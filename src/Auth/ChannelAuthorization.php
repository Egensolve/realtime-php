<?php

declare(strict_types=1);

namespace Egensolve\Realtime\Contracts\Auth;






final class ChannelAuthorization
{
    public function __construct(
        public readonly string $auth,
        public readonly ?string $channelData = null,
    ) {
    }

     
    public function toArray(): array
    {
        $result = ['auth' => $this->auth];
        if ($this->channelData !== null) {
            $result['channel_data'] = $this->channelData;
        }

        return $result;
    }
}
