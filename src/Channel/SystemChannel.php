<?php

declare(strict_types=1);

namespace Egensolve\Realtime\Contracts\Channel;

use Egensolve\Realtime\Contracts\ValidationError;





final class SystemChannel implements Channel
{
    private function __construct(
        private readonly string $tenant,
        public readonly string $scope,
    ) {
    }

     
    public static function for(string $tenant, string $scope): self
    {
        return new self(
            ChannelGrammar::requireOpaqueId($tenant, 'tenant'),
            ChannelGrammar::requireToken($scope, 'scope'),
        );
    }

    public function tenant(): string
    {
        return $this->tenant;
    }

    public function toString(): string
    {
        return ChannelGrammar::requireWithinMaxLength(
            "private-v1.tenants.{$this->tenant}.systems.{$this->scope}",
        );
    }
}
