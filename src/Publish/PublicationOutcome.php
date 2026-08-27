<?php

declare(strict_types=1);

namespace Egensolve\Realtime\Contracts\Publish;






enum PublicationOutcome
{
     
    case Accepted;

     
    case Replayed;

     
    case RejectedPermanently;

     
    case RetryableFailure;

     
    case ConflictingReuse;
}
