<?php

declare(strict_types=1);

namespace Egensolve\Realtime\Contracts\Channel;

use Egensolve\Realtime\Contracts\ValidationError;





final class DeviceChannel implements Channel
{
    private function __construct(
        private readonly string $tenant,
        public readonly string $deviceId,
    ) {
    }

     
    public static function for(string $tenant, string $deviceId): self
    {
        return new self(
            ChannelGrammar::requireOpaqueId($tenant, 'tenant'),
            ChannelGrammar::requireOpaqueId($deviceId, 'deviceId'),
        );
    }

    public function tenant(): string
    {
        return $this->tenant;
    }

    public function toString(): string
    {
        return ChannelGrammar::requireWithinMaxLength(
            "private-v1.tenants.{$this->tenant}.devices.{$this->deviceId}",
        );
    }
}
