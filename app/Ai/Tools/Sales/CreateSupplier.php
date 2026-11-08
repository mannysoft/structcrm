<?php

declare(strict_types=1);

namespace App\Ai\Tools\Sales;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Domains\Sales\Actions\CreateSupplierAction;
use App\Domains\Sales\DTOs\SupplierData;
use App\Domains\Sales\Models\Supplier;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

final class CreateSupplier implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Create a supplier record with contact details.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $this->authorize('create', Supplier::class);

            $supplier = app(CreateSupplierAction::class)->execute(
                new SupplierData(
                    name: $validated['name'],
                    email: $validated['email'] ?? null,
                    phone: $validated['phone'] ?? null,
                    address: $validated['address'] ?? null,
                    notes: $validated['notes'] ?? null,
                ),
                $this->companyId(),
            );

            return [
                'message' => 'Supplier created.',
                'supplier' => $this->supplierSummary($supplier),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Supplier name.')->required(),
            'email' => $schema->string()->description('Contact email.'),
            'phone' => $schema->string()->description('Contact phone.'),
            'address' => $schema->string()->description('Address.'),
            'notes' => $schema->string()->description('Optional notes.'),
        ];
    }
}
