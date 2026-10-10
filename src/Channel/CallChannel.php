<?php

declare(strict_types=1);

namespace Egensolve\Realtime\Contracts\Channel;

use Egensolve\Realtime\Contracts\ValidationError;





final class CallChannel implements Channel
{
    private function __construct(
        private readonly string $tenant,
        public readonly string $callId,
    ) {
    }

     
    public static function for(string $tenant, string $callId): self
    {
        return new self(
            ChannelGrammar::requireOpaqueId($tenant, 'tenant'),
            ChannelGrammar::requireOpaqueId($callId, 'callId', 128),
        );
    }

    public function tenant(): string
    {
        return $this->tenant;
    }

    public function toString(): string
    {
        return ChannelGrammar::requireWithinMaxLength(
            "private-v2.tenants.{$this->tenant}.calls.{$this->callId}",
        );
    }
}
