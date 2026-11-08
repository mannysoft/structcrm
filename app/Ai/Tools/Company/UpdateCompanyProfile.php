<?php

declare(strict_types=1);

namespace App\Ai\Tools\Company;

use App\Ai\Tools\Concerns\FormatsCompanyRecords;
use App\Ai\Tools\Concerns\InteractsWithCompanyData;
use App\Ai\Tools\ToolFailure;
use App\Domains\CompanyManagement\Actions\UpdateCompanyAction;
use App\Domains\CompanyManagement\DTOs\CompanyData;
use App\Domains\CompanyManagement\Enums\Currency;
use App\Domains\CompanyManagement\Models\Company;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

final class UpdateCompanyProfile implements Tool
{
    use FormatsCompanyRecords, InteractsWithCompanyData;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Update the company profile. Only the fields provided are changed; name is required if the profile is being renamed.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'timezone' => ['nullable', 'timezone:all'],
            'currency' => ['nullable', 'string'],
        ]);

        return $this->attempt(function () use ($validated): array {
            $company = Company::query()->find($this->companyId());

            if (! $company instanceof Company) {
                throw new ToolFailure('No company is associated with your account.');
            }

            $this->authorize('update', $company);

            $currency = $validated['currency'] ?? null;

            if (filled($currency)) {
                try {
                    $currency = Currency::from((string) $currency);
                } catch (Throwable) {
                    throw new ToolFailure('Unsupported currency. Use one of: '.implode(', ', array_column(Currency::cases(), 'value')).'.');
                }
            }

            $data = new CompanyData(
                name: (string) ($validated['name'] ?? $company->name),
                email: $validated['email'] ?? $company->email,
                phone: $validated['phone'] ?? $company->phone,
                address: $validated['address'] ?? $company->address,
                taxNumber: $validated['tax_number'] ?? $company->tax_number,
                timezone: $validated['timezone'] ?? $company->timezone,
                currency: $currency instanceof Currency ? $currency : ($company->currency ?? Currency::PHP),
                logoPath: $company->logo_path,
            );

            $updated = app(UpdateCompanyAction::class)->execute($company, $data);

            return [
                'message' => 'Company profile updated.',
                'company' => $this->companySummary($updated->refresh()),
            ];
        });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Company display name.'),
            'email' => $schema->string()->description('Contact email address.'),
            'phone' => $schema->string()->description('Contact phone number.'),
            'address' => $schema->string()->description('Business address.'),
            'tax_number' => $schema->string()->description('Tax / VAT registration number.'),
            'timezone' => $schema->string()->description('IANA timezone, e.g. Asia/Manila.'),
            'currency' => $schema->string()->enum(array_column(Currency::cases(), 'value'))->description('Billing currency code.'),
        ];
    }
}
