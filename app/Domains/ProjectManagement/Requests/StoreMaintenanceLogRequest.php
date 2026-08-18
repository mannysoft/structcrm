<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Requests;

use App\Common\ValueObjects\Money;
use App\Domains\ProjectManagement\DTOs\MaintenanceLogData;
use App\Domains\ProjectManagement\Models\MaintenanceLog;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMaintenanceLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', MaintenanceLog::class) ?? false;
    }

    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'equipment_id' => ['required', 'integer', Rule::exists('equipment', 'id')->where('company_id', $companyId)],
            'service_date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:255'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
        ];
    }

    public function toDTO(): MaintenanceLogData
    {
        return new MaintenanceLogData(
            equipmentId: (int) $this->validated('equipment_id'),
            serviceDate: CarbonImmutable::parse($this->validated('service_date')),
            description: $this->validated('description'),
            cost: Money::fromDecimal((float) ($this->validated('cost') ?? 0), $this->validated('currency') ?? 'USD'),
        );
    }
}
