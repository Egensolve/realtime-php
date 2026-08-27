<?php

declare(strict_types=1);

namespace Egensolve\Realtime\Contracts;

enum EnvelopeKind: string
{
    case Durable = 'durable';
    case Ephemeral = 'ephemeral';
}
