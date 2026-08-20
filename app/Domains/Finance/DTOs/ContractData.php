<?php

declare(strict_types=1);

namespace App\Domains\Finance\DTOs;

use App\Common\ValueObjects\Money;
use App\Domains\Finance\Enums\ContractStatus;
use Carbon\CarbonImmutable;

final readonly class ContractData
{
    public function __construct(
        public int $projectId,
        public string $title,
        public Money $contractValue,
        public ContractStatus $status = ContractStatus::Draft,
        public ?CarbonImmutable $signedAt = null,
    ) {}
}
