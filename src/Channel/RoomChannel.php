<?php

declare(strict_types=1);

namespace Egensolve\Realtime\Contracts\Channel;

use Egensolve\Realtime\Contracts\ValidationError;






final class RoomChannel implements Channel
{
    private function __construct(
        private readonly string $tenant,
        public readonly string $roomType,
        public readonly string $roomId,
    ) {
    }

     
    public static function for(string $tenant, string $roomType, string $roomId): self
    {
        return new self(
            ChannelGrammar::requireOpaqueId($tenant, 'tenant'),
            ChannelGrammar::requireToken($roomType, 'roomType'),
            ChannelGrammar::requireOpaqueId($roomId, 'roomId'),
        );
    }

    public function tenant(): string
    {
        return $this->tenant;
    }

    public function toString(): string
    {
        return ChannelGrammar::requireWithinMaxLength(
            "presence-v1.tenants.{$this->tenant}.rooms.{$this->roomType}.{$this->roomId}",
        );
    }
}
