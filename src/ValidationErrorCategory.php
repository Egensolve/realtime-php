<?php

declare(strict_types=1);

namespace Egensolve\Realtime\Contracts;

enum ValidationErrorCategory: string
{
    case Parse = 'parse';
    case SizeLimit = 'size_limit';
    case Schema = 'schema';
    case Grammar = 'grammar';
}
