<?php

declare(strict_types=1);

namespace App\Domains\Finance\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BoqImport implements ToArray, WithHeadingRow
{
    /**
     * @var array<int, array<string, mixed>>
     */
    public array $rows = [];

    public function array(array $array): void
    {
        $this->rows = $array;
    }
}
