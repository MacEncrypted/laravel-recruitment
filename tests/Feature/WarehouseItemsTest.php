<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseItemsTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    private User $captain;

    private User $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Stark Industries',
        ]);

        $this->captain = User::factory()->create();
        $this->member = User::factory()->create();

        $this->company->users()->attach(
            $this->captain->id,
            ['is_captain' => true]
        );

        $this->company->users()->attach($this->member->id);
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

        $this->assertDatabaseCount('items', 1);
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
            ->assertSessionHasErrors('quantity', null, 'createItem');

        $this->assertDatabaseCount('items', 0);
    }

    public function test_invalid_item_keeps_form_input(): void
    {
        $this->actingAs($this->captain)
            ->from(route('companies.warehouse.index', $this->company))
            ->post(route('companies.warehouse.store', $this->company), [
                'name' => "Captain's Shield",
                'quantity' => 0,
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('quantity', null, 'createItem')
            ->assertSessionHasInput('name', "Captain's Shield");
    }

    public function test_empty_warehouse_shows_message(): void
    {
        $this->actingAs($this->captain)
            ->get(route('companies.warehouse.index', $this->company))
            ->assertOk()
            ->assertSee('No items in the warehouse yet.');
    }

    public function test_captain_can_view_item_details(): void
    {
        $item = $this->company->items()->create([
            'name' => 'Shield',
            'quantity' => 3,
        ]);

        $this->actingAs($this->captain)
            ->get(route('companies.warehouse.show', [
                $this->company, $item,
            ]))
            ->assertOk()
            ->assertSee('Assignment History');
    }
}
