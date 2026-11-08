<?php

declare(strict_types=1);

namespace App\Ai\Tools\Sales;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Sales\Models\Supplier;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class ListSuppliers implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'List or search suppliers by name, email or phone. Supplier UUIDs are needed to create purchase orders.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        return $this->attempt(function () use ($request): array {
            $this->authorize('viewAny', Supplier::class);

            $search = trim((string) ($request['search'] ?? ''));

            $suppliers = Supplier::query()
                ->where('company_id', $this->companyId())
                ->when($search !== '', fn ($builder) => $builder->where(
                    fn ($inner) => $inner
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                ))
                ->orderBy('name')
                ->limit($this->limit($request['limit'] ?? null))
                ->get();

            return [
                'count' => $suppliers->count(),
                'suppliers' => $suppliers->map(fn (Supplier $supplier) => $this->supplierSummary($supplier))->all(),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Optional name, email or phone fragment.'),
            'limit' => $schema->integer()->description('Maximum suppliers to return (default 25, max 100).'),
        ];
    }
}
