<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $captain;
    private User $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->company = Company::where('name', 'Stark Industries')
            ->firstOrFail();

        $this->captain = User::where('email', 'captainamerica@avengers.com')
            ->firstOrFail();

        $this->member = User::where('email', 'ironman@starkindustries.com')
            ->firstOrFail();
    }

    private function createItem(): Item
    {
        return $this->company->items()->create([
            'name' => "Captain's Shield",
            'quantity' => 3,
        ]);
    }

    public function test_captain_can_add_item(): void
    {
        $this->actingAs($this->captain)
            ->post(route('companies.warehouse.store', $this->company), [
                'name' => "Captain's Shield",
                'quantity' => 3,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('items', [
            'company_id' => $this->company->id,
            'name' => "Captain's Shield",
            'quantity' => 3,
        ]);
    }

    public function test_member_cannot_add_item(): void
    {
        $this->actingAs($this->member)
            ->post(route('companies.warehouse.store', $this->company), [
                'name' => 'Shield',
                'quantity' => 3,
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('items', 0);
    }

    public function test_quantity_must_be_positive(): void
    {
        $this->actingAs($this->captain)
            ->post(route('companies.warehouse.store', $this->company), [
                'name' => 'Shield',
                'quantity' => 0,
            ])
            ->assertSessionHasErrors('quantity');

        $this->assertDatabaseCount('items', 0);
    }

    public function test_captain_can_assign_item(): void
    {
        $item = $this->createItem();

        $this->actingAs($this->captain)
            ->post(route('companies.warehouse.assign', [
                $this->company, $item,
            ]), [
                'user_id' => $this->member->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('item_user', [
            'item_id' => $item->id,
            'user_id' => $this->member->id,
            'unassigned_at' => null,
        ]);
    }

    public function test_item_can_be_reassigned_with_history(): void
    {
        $item = $this->createItem();

        $url = route('companies.warehouse.assign', [
            $this->company, $item,
        ]);

        $this->actingAs($this->captain)
            ->post($url, ['user_id' => $this->member->id]);

        $this->post($url, [
            'user_id' => $this->captain->id,
        ]);

        $this->assertDatabaseCount('item_user', 2);

        $this->assertDatabaseHas('item_user', [
            'item_id' => $item->id,
            'user_id' => $this->captain->id,
            'unassigned_at' => null,
        ]);

        $this->assertDatabaseMissing('item_user', [
            'item_id' => $item->id,
            'user_id' => $this->member->id,
            'unassigned_at' => null,
        ]);

        $this->assertNotNull(
            $item->users()
                ->where('users.id', $this->member->id)
                ->first()
                ->pivot
                ->unassigned_at
        );
    }

    public function test_cannot_assign_item_to_outsider(): void
    {
        $item = $this->createItem();

        $outsider = User::where('email', 'thor@asgard.com')
            ->firstOrFail();

        $this->actingAs($this->captain)
            ->post(route('companies.warehouse.assign', [
                $this->company, $item,
            ]), [
                'user_id' => $outsider->id,
            ])
            ->assertSessionHasErrors('user_id');

        $this->assertDatabaseCount('item_user', 0);
    }

    public function test_member_cannot_manage_warehouse(): void
    {
        $item = $this->createItem();

        $this->actingAs($this->member)
            ->get(route('companies.warehouse.show', [
                $this->company, $item,
            ]))
            ->assertForbidden();
    }

    public function test_cannot_assign_item_from_another_company(): void
    {
        $otherCompany = Company::where('id', '!=', $this->company->id)
            ->firstOrFail();

        $item = $otherCompany->items()->create([
            'name' => 'Mjolnir',
            'quantity' => 1,
        ]);

        $this->actingAs($this->captain)
            ->post(route('companies.warehouse.assign', [
                $this->company, $item,
            ]), [
                'user_id' => $this->member->id,
            ])
            ->assertNotFound();

        $this->assertDatabaseCount('item_user', 0);
    }

    public function test_assigning_same_user_does_not_duplicate_history(): void
    {
        $item = $this->createItem();

        $url = route('companies.warehouse.assign', [
            $this->company, $item,
        ]);

        $this->actingAs($this->captain)
            ->post($url, [
                'user_id' => $this->member->id,
            ])
            ->assertRedirect();

        $this->post($url, [
            'user_id' => $this->member->id,
        ])->assertRedirect();

        $this->assertDatabaseCount('item_user', 1);

        $this->assertDatabaseHas('item_user', [
            'item_id' => $item->id,
            'user_id' => $this->member->id,
            'unassigned_at' => null,
        ]);
    }
}
