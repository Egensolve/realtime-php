<?php

declare(strict_types=1);

namespace Egensolve\Realtime\Contracts\Channel;

use Egensolve\Realtime\Contracts\ValidationError;





final class AccountChannel implements Channel
{
    private function __construct(
        private readonly string $tenant,
        public readonly string $actorType,
        public readonly string $actorId,
    ) {
    }

     
    public static function for(string $tenant, string $actorType, string $actorId): self
    {
        return new self(
            ChannelGrammar::requireOpaqueId($tenant, 'tenant'),
            ChannelGrammar::requireToken($actorType, 'actorType'),
            ChannelGrammar::requireOpaqueId($actorId, 'actorId'),
        );
    }

    public function tenant(): string
    {
        return $this->tenant;
    }

    public function toString(): string
    {
        return ChannelGrammar::requireWithinMaxLength(
            "private-v1.tenants.{$this->tenant}.accounts.{$this->actorType}.{$this->actorId}",
        );
    }
}
