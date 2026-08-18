<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Enums;

enum EquipmentStatus: string
{
    case Available = 'available';
    case InUse = 'in_use';
    case Maintenance = 'maintenance';
    case Retired = 'retired';
}
