<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Enums;

enum EquipmentType: string
{
    case HeavyMachinery = 'heavy_machinery';
    case Tool = 'tool';
    case Vehicle = 'vehicle';
    case Other = 'other';
}
