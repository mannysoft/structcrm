<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\CompanyManagement\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class TeamManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_page_requires_authentication(): void
    {
        $this->get('/team')->assertRedirect('/login');
    }

    public function test_team_page_requires_manage_users_permission(): void
    {
        $company = Company::factory()->create();
        $staff = User::factory()->role('staff')->create(['company_id' => $company->id]);

        $this->actingAs($staff)->get('/team')->assertForbidden();
    }

    public function test_owner_can_view_the_team_page(): void
    {
        $company = Company::factory()->create();
        $owner = User::factory()->create(['company_id' => $company->id]);
        User::factory()->role('staff')->create(['company_id' => $company->id, 'name' => 'Jane Doe']);

        $this->actingAs($owner)
            ->get('/team')
            ->assertOk()
            ->assertSee('Jane Doe');
    }

    public function test_staff_set_password_link_requires_a_valid_signature(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->role('staff')->create(['company_id' => $company->id]);

        $this->get('/staff/set-password/'.$user->id)->assertForbidden();

        $signed = URL::temporarySignedRoute(
            'staff.set-password',
            now()->addMinutes(5),
            ['user' => $user->id],
        );

        $this->get($signed)->assertOk();
    }
}
