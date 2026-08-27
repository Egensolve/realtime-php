<?php

declare(strict_types=1);

namespace Egensolve\Realtime\Contracts;





final class Recovery
{
    public function __construct(
        public readonly string $scope,
        public readonly string $cursor,
    ) {
    }
}
