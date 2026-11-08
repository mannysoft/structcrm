<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use RuntimeException;

/**
 * Signals a recoverable tool failure.
 *
 * Thrown failures are converted into tool results by
 * {@see Concerns\InteractsWithCompanyData::attempt()} so the model can
 * correct itself instead of aborting the entire agent run.
 */
final class ToolFailure extends RuntimeException
{
    //
}
