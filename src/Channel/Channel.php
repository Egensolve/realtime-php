<?php

declare(strict_types=1);

namespace Egensolve\Realtime\Contracts\Channel;








interface Channel
{
    public function tenant(): string;

    public function toString(): string;
}
