<?php

declare(strict_types=1);

namespace App\Domains\Finance\Controllers;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class BoqImportController extends Controller
{
    public function template(): StreamedResponse
    {
        $headers = ['item_code', 'description', 'unit', 'quantity', 'unit_price', 'cost_code'];

        return response()->streamDownload(function () use ($headers) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, $headers);
            fputcsv($handle, ['1001', 'Concrete slab', 'cu.m.', '12.5', '5000', '100']);
            fputcsv($handle, ['1002', 'Reinforcing steel', 'kg', '1000', '62', '100']);
            fputcsv($handle, ['2001', 'Masonry works', 'sq.m.', '240', '350', '200']);

            fclose($handle);
        }, 'boq-template.csv', ['Content-Type' => 'text/csv']);
    }
}
