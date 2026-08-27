<?php

declare(strict_types=1);

namespace Egensolve\Realtime\Contracts;





final class Envelope
{
    


    public function __construct(
        public readonly int $version,
        public readonly string $id,
        public readonly EnvelopeKind $kind,
        public readonly string $domain,
        public readonly string $type,
        public readonly string $tenant,
        public readonly \DateTimeImmutable $occurredAt,
        public readonly ?Subject $subject,
        public readonly ?Recovery $recovery,
        public readonly ?\DateTimeImmutable $expiresAt,
        public readonly array $payload,
    ) {
    }
}
