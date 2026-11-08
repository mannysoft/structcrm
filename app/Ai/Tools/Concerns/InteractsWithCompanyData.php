<?php

declare(strict_types=1);

namespace App\Ai\Tools\Concerns;

use App\Ai\Tools\ToolFailure;
use App\Domains\CompanyManagement\Models\Company;
use App\Models\User;
use BackedEnum;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Throwable;

/**
 * Shared behaviour for every AI tool: tenant scoping, authorization,
 * money handling and model-readable results.
 */
trait InteractsWithCompanyData
{
    public function __construct(public readonly User $user)
    {
        //
    }

    /**
     * Get the company that the current user belongs to.
     */
    protected function companyId(): int
    {
        return (int) $this->user->company_id;
    }

    protected function company(): ?Company
    {
        return Company::query()->find($this->companyId());
    }

    /**
     * Get the company's configured currency code.
     */
    protected function currency(): string
    {
        return $this->company()?->currency?->value ?? 'PHP';
    }

    /**
     * Convert a major-unit amount (e.g. 1500.25) into minor units (150025).
     */
    protected function toCents(float|int|string|null $amount): int
    {
        if ($amount === null || $amount === '') {
            return 0;
        }

        return (int) round(((float) $amount) * 100);
    }

    /**
     * Convert minor units (150025) into a major-unit decimal (1500.25).
     */
    protected function toDecimal(?int $cents): float
    {
        return round(($cents ?? 0) / 100, 2);
    }

    /**
     * Parse a date string or throw a tool failure.
     */
    protected function parseDate(?string $value, ?CarbonImmutable $default = null): CarbonImmutable
    {
        if ($value === null || trim($value) === '') {
            if ($default !== null) {
                return $default;
            }

            throw new ToolFailure('A date is required (formatted as YYYY-MM-DD).');
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (Throwable) {
            throw new ToolFailure("Invalid date [{$value}]. Use the YYYY-MM-DD format.");
        }
    }

    /**
     * Normalise a caller supplied list size.
     */
    protected function limit(mixed $value, int $default = 25, int $max = 100): int
    {
        if ($value === null || $value === '') {
            return $default;
        }

        return max(1, min($max, (int) $value));
    }

    /**
     * Resolve a company-owned record by UUID, numeric id, or a natural key column.
     *
     * @template TModel of Model
     *
     * @param  class-string<TModel>  $modelClass
     * @param  array<int, string>  $lookupColumns
     * @return TModel
     */
    protected function findRecord(
        string $modelClass,
        ?string $identifier,
        string $label,
        array $lookupColumns = [],
    ): Model {
        $identifier = trim((string) $identifier);

        if ($identifier === '') {
            throw new ToolFailure("A {$label} identifier is required. Use the relevant list tool to find one.");
        }

        $record = $modelClass::query()
            ->where('company_id', $this->companyId())
            ->where(function ($query) use ($identifier, $lookupColumns) {
                $query->where('uuid', $identifier);

                if (ctype_digit($identifier)) {
                    $query->orWhere('id', (int) $identifier);
                }

                foreach ($lookupColumns as $column) {
                    $query->orWhere($column, $identifier);
                }
            })
            ->first();

        if (! $record instanceof Model) {
            throw new ToolFailure("No {$label} matching [{$identifier}] was found in your company.");
        }

        return $record;
    }

    /**
     * Resolve a staff user by email, name or numeric id within the company.
     */
    protected function findTeamMember(?string $identifier): User
    {
        $identifier = trim((string) $identifier);

        if ($identifier === '') {
            throw new ToolFailure('A team member email, name or id is required.');
        }

        $member = User::query()
            ->where('company_id', $this->companyId())
            ->where('type', User::TYPE_STAFF)
            ->where(function ($query) use ($identifier) {
                $query->where('email', $identifier)->orWhere('name', $identifier);

                if (ctype_digit($identifier)) {
                    $query->orWhere('id', (int) $identifier);
                }
            })
            ->first();

        if (! $member instanceof User) {
            throw new ToolFailure("No staff member matching [{$identifier}] was found in your company.");
        }

        return $member;
    }

    /**
     * Authorize an ability for the current user against a policy target.
     */
    protected function authorize(string $ability, object|string $target): void
    {
        try {
            Gate::forUser($this->user)->authorize($ability, $target);
        } catch (Throwable) {
            throw new ToolFailure("You do not have permission to {$ability} this record.");
        }
    }

    /**
     * Ensure the user holds the given permission.
     */
    protected function authorizePermission(string $permission): void
    {
        if (! $this->user->can($permission)) {
            throw new ToolFailure("You do not have the [{$permission}] permission required for this action.");
        }
    }

    /**
     * Run a tool callback, turning expected failures into model-readable results.
     */
    protected function attempt(callable $callback): string
    {
        try {
            return $this->ok($callback());
        } catch (ToolFailure $failure) {
            return $this->fail($failure->getMessage());
        }
    }

    /**
     * Encode a successful tool result.
     */
    protected function ok(mixed $data): string
    {
        return $this->encode(['ok' => true, 'data' => $data]);
    }

    /**
     * Encode a recoverable tool error.
     */
    protected function fail(string $message): string
    {
        return $this->encode(['ok' => false, 'error' => $message]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function encode(array $payload): string
    {
        $json = json_encode(
            $payload,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR,
        );

        return $json === false ? '{"ok":false,"error":"Unable to encode tool result."}' : $json;
    }

    /**
     * Unwrap a backed enum into its scalar value.
     */
    protected function enumValue(mixed $value): mixed
    {
        return $value instanceof BackedEnum ? $value->value : $value;
    }

    /**
     * Get the role names held by the current user.
     *
     * @return array<int, string>
     */
    protected function roleNames(): array
    {
        return $this->user->getRoleNames()->all();
    }
}
