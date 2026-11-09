<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyPermissionsTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $captain;
    private User $member;
    private User $outsider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create(['name' => 'Stark Industries']);

        $this->captain = User::factory()->create();
        $this->member = User::factory()->create();
        $this->outsider = User::factory()->create();

        $this->company->users()->attach($this->captain->id, ['is_captain' => true]);
        $this->company->users()->attach($this->member->id);
    }

    public function test_any_user_creating_a_company_becomes_its_captain(): void
    {
        $this->actingAs($this->outsider)
            ->post(route('companies.store'), ['name' => 'New Co'])
            ->assertRedirect(route('companies.index'));

        $company = Company::where('name', 'New Co')->firstOrFail();

        $this->assertTrue($company->isCaptain($this->outsider));
        $this->assertTrue($this->outsider->can('update', $company));
        $this->assertTrue($this->outsider->can('manageWarehouse', $company));
    }

    public function test_captain_can_rename_and_manage_users(): void
    {
        $this->actingAs($this->captain)
            ->put(route('companies.update', $this->company), ['name' => 'Renamed'])
            ->assertRedirect();
        $this->assertSame('Renamed', $this->company->fresh()->name);

        $this->actingAs($this->captain)
            ->post(route('companies.users.attach', $this->company), ['user_id' => $this->outsider->id])
            ->assertRedirect();
        $this->assertTrue($this->company->isMember($this->outsider));
        $this->assertFalse($this->company->isCaptain($this->outsider));

        $this->actingAs($this->captain)
            ->delete(route('companies.users.detach', [$this->company, $this->member]))
            ->assertRedirect();
        $this->assertFalse($this->company->isMember($this->member));
    }

    public function test_captain_can_delete_company(): void
    {
        $this->actingAs($this->captain)
            ->delete(route('companies.destroy', $this->company))
            ->assertRedirect();

        $this->assertModelMissing($this->company);
    }

    public function test_captain_cannot_remove_themselves(): void
    {
        $this->actingAs($this->captain)
            ->delete(route('companies.users.detach', [$this->company, $this->captain]))
            ->assertSessionHas('error');

        $this->assertTrue($this->company->isCaptain($this->captain));
    }

    public function test_captain_of_another_company_cannot_manage_this_one(): void
    {
        $otherCompany = Company::create(['name' => 'Wayne Enterprises']);
        $otherCaptain = User::factory()->create();
        $otherCompany->users()->attach($otherCaptain->id, ['is_captain' => true]);

        $this->actingAs($otherCaptain)->get(route('companies.edit', $this->company))->assertForbidden();
        $this->actingAs($otherCaptain)->get(route('companies.warehouse.index', $this->company))->assertForbidden();
    }

    public function test_captain_of_one_company_is_only_a_member_in_another(): void
    {
        $otherCompany = Company::create(['name' => 'Wayne Enterprises']);
        $otherCompany->users()->attach($this->captain->id);

        $this->actingAs($this->captain)->get(route('companies.users.index', $otherCompany))->assertOk();
        $this->actingAs($this->captain)->get(route('companies.edit', $otherCompany))->assertForbidden();
        $this->actingAs($this->captain)->get(route('companies.warehouse.index', $otherCompany))->assertForbidden();
    }

    public function test_member_sees_members_but_cannot_change_anything(): void
    {
        $this->actingAs($this->member)
            ->get(route('companies.users.index', $this->company))
            ->assertOk()
            ->assertSee($this->captain->name)
            ->assertSee('Captain')
            ->assertDontSee('Assign User');

        $this->actingAs($this->member)->get(route('companies.show', $this->company))->assertOk();
        $this->actingAs($this->member)->get(route('companies.edit', $this->company))->assertForbidden();
        $this->actingAs($this->member)->put(route('companies.update', $this->company), ['name' => 'X'])->assertForbidden();
        $this->actingAs($this->member)->delete(route('companies.destroy', $this->company))->assertForbidden();
        $this->actingAs($this->member)
            ->post(route('companies.users.attach', $this->company), ['user_id' => $this->outsider->id])
            ->assertForbidden();
        $this->actingAs($this->member)
            ->delete(route('companies.users.detach', [$this->company, $this->captain]))
            ->assertForbidden();
        $this->actingAs($this->member)->get(route('companies.warehouse.index', $this->company))->assertForbidden();
    }

    public function test_outsider_sees_only_the_company_name(): void
    {
        $this->actingAs($this->outsider)
            ->get(route('companies.index'))
            ->assertOk()
            ->assertSee('Stark Industries')
            ->assertDontSee($this->company->uuid)
            ->assertDontSee(route('companies.users.index', $this->company));

        $this->actingAs($this->outsider)->get(route('companies.show', $this->company))->assertForbidden();
        $this->actingAs($this->outsider)->get(route('companies.users.index', $this->company))->assertForbidden();
    }

    public function test_seeder_gives_every_company_exactly_one_captain_and_some_members(): void
    {
        Company::query()->delete();
        $this->seed(DatabaseSeeder::class);

        foreach (Company::all() as $company) {
            $this->assertSame(1, $company->users()->wherePivot('is_captain', true)->count(), $company->name);
            $this->assertGreaterThan(1, $company->users()->count(), $company->name);
        }

        // Steve: Kapitan w jednej firmie, zwykły członek w drugiej, w trzeciej go nie ma
        $steve = User::where('email', 'captainamerica@avengers.com')->firstOrFail();
        $stark = Company::where('name', 'Stark Industries')->firstOrFail();
        $wayne = Company::where('name', 'Wayne Enterprises')->firstOrFail();
        $eswat = Company::where('name', 'E.S.W.A.T')->firstOrFail();

        $this->assertTrue($stark->isCaptain($steve));
        $this->assertTrue($wayne->isMember($steve));
        $this->assertFalse($wayne->isCaptain($steve));
        $this->assertFalse($eswat->isMember($steve));
    }
}
