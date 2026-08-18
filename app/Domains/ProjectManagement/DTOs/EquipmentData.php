<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\DTOs;

use App\Domains\ProjectManagement\Enums\EquipmentStatus;
use App\Domains\ProjectManagement\Enums\EquipmentType;
use Carbon\CarbonImmutable;

final readonly class EquipmentData
{
    public function __construct(
        public string $name,
        public EquipmentType $type,
        public EquipmentStatus $status,
        public ?CarbonImmutable $serviceDueAt = null,
        public ?string $notes = null,
    ) {}
}
