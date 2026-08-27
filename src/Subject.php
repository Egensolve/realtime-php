<?php

declare(strict_types=1);

namespace Egensolve\Realtime\Contracts;





final class Subject
{
    public function __construct(
        public readonly string $type,
        public readonly string $id,
    ) {
    }
}
