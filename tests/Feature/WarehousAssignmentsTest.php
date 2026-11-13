<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Item;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseAssignmentsTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $captain;
    private User $member;
    private User $outsider;
    private Item $item;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Stark Industries',
        ]);

        $this->captain = User::factory()->create();
        $this->member = User::factory()->create();
        $this->outsider = User::factory()->create();

        $this->company->users()->attach(
            $this->captain->id,
            ['is_captain' => true]
        );

        $this->company->users()->attach($this->member->id);

        $this->item = $this->company->items()->create([
            'name' => "Captain's Shield",
            'quantity' => 3,
        ]);
    }

    public function test_captain_can_assign_item(): void
    {
        $this->actingAs($this->captain)
            ->post(route('companies.warehouse.assign', [
                $this->company, $this->item,
            ]), [
                'user_id' => $this->member->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('item_user', [
            'item_id' => $this->item->id,
            'user_id' => $this->member->id,
            'unassigned_at' => null,
        ]);
    }

    public function test_reassignment_preserves_history(): void
    {
        $url = route('companies.warehouse.assign', [
            $this->company, $this->item,
        ]);

        $this->actingAs($this->captain)
            ->post($url, ['user_id' => $this->member->id]);

        $this->post($url, [
            'user_id' => $this->captain->id,
        ]);

        $this->assertDatabaseCount('item_user', 2);

        $this->assertDatabaseHas('item_user', [
            'item_id' => $this->item->id,
            'user_id' => $this->captain->id,
            'unassigned_at' => null,
        ]);

        $this->assertDatabaseMissing('item_user', [
            'item_id' => $this->item->id,
            'user_id' => $this->member->id,
            'unassigned_at' => null,
        ]);

        $this->assertNotNull(
            $this->item->users()
                ->where('users.id', $this->member->id)
                ->firstOrFail()
                ->pivot
                ->unassigned_at
        );
    }

    public function test_same_user_does_not_duplicate_history(): void
    {
        $url = route('companies.warehouse.assign', [
            $this->company, $this->item,
        ]);

        $this->actingAs($this->captain)
            ->post($url, ['user_id' => $this->member->id]);

        $this->post($url, [
            'user_id' => $this->member->id,
        ]);

        $this->assertDatabaseCount('item_user', 1);
    }

    public function test_captain_can_unassign_item(): void
    {
        $this->item->users()->attach($this->member->id, [
            'assigned_at' => now()->subDay(),
        ]);

        $this->actingAs($this->captain)
            ->delete(route('companies.warehouse.unassign', [
                $this->company, $this->item,
            ]))
            ->assertRedirect();

        $this->assertDatabaseMissing('item_user', [
            'item_id' => $this->item->id,
            'unassigned_at' => null,
        ]);

        $this->assertDatabaseCount('item_user', 1);

        $this->assertNotNull(
            $this->item->users()->firstOrFail()
                ->pivot->unassigned_at
        );
    }

    public function test_member_cannot_unassign_item(): void
    {
        $this->item->users()->attach($this->member->id, [
            'assigned_at' => now(),
        ]);

        $this->actingAs($this->member)
            ->delete(route('companies.warehouse.unassign', [
                $this->company, $this->item,
            ]))
            ->assertForbidden();

        $this->assertDatabaseHas('item_user', [
            'item_id' => $this->item->id,
            'user_id' => $this->member->id,
            'unassigned_at' => null,
        ]);
    }

    public function test_cannot_assign_to_outsider(): void
    {
        $this->actingAs($this->captain)
            ->post(route('companies.warehouse.assign', [
                $this->company, $this->item,
            ]), [
                'user_id' => $this->outsider->id,
            ])
            ->assertSessionHasErrors('user_id');

        $this->assertDatabaseCount('item_user', 0);
    }

    public function test_cannot_access_item_from_another_company(): void
    {
        $otherCompany = Company::create([
            'name' => 'Wayne Enterprises',
        ]);

        $otherItem = $otherCompany->items()->create([
            'name' => 'Batarang',
            'quantity' => 1,
        ]);

        $this->actingAs($this->captain)
            ->get(route('companies.warehouse.show', [
                $this->company, $otherItem,
            ]))
            ->assertNotFound();

        $this->post(route('companies.warehouse.assign', [
            $this->company, $otherItem,
        ]), [
            'user_id' => $this->member->id,
        ])->assertNotFound();

        $this->assertDatabaseCount('item_user', 0);
    }

    public function test_member_cannot_manage_warehouse(): void
    {
        $this->actingAs($this->member)
            ->get(route('companies.warehouse.show', [
                $this->company, $this->item,
            ]))
            ->assertForbidden();
    }

    public function test_item_cannot_have_two_active_assignments(): void
    {
        $this->item->users()->attach($this->member->id, [
            'assigned_at' => now(),
        ]);

        $this->expectException(QueryException::class);

        $this->item->users()->attach($this->captain->id, [
            'assigned_at' => now(),
        ]);
    }
}
