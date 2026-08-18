<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\CompanyManagement;

use App\Domains\CompanyManagement\Actions\CreateCompanyAction;
use App\Domains\CompanyManagement\DTOs\CompanyData;
use App\Domains\CompanyManagement\Enums\Currency;
use App\Domains\CompanyManagement\Events\CompanyCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CreateCompanyActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_company_with_a_uuid_and_slug(): void
    {
        Event::fake();

        $action = app(CreateCompanyAction::class);

        $company = $action->execute(new CompanyData(
            name: 'Acme Construction',
            email: 'hello@acme.test',
            currency: Currency::USD,
            timezone: 'America/New_York',
        ));

        $this->assertNotNull($company->uuid);
        $this->assertSame('acme-construction', $company->slug);
        $this->assertSame('hello@acme.test', $company->email);
        $this->assertSame(Currency::USD, $company->currency);

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'name' => 'Acme Construction',
        ]);

        Event::assertDispatched(CompanyCreated::class);
    }
}
