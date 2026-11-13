<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyItemsTest extends TestCase
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

    private function createItem(): Item
    {
        return $this->company->items()->create([
            'name' => "Captain's Shield",
            'quantity' => 3,
        ]);
    }

    public function test_guest_cannot_view_my_items(): void
    {
        $this->get(route('items.mine'))
            ->assertRedirect(route('login'));
    }

    public function test_user_sees_only_their_items(): void
    {
        $item = $this->createItem();

        $item->users()->attach($this->member->id, [
            'assigned_at' => now(),
        ]);

        $this->actingAs($this->member)
            ->get(route('items.mine'))
            ->assertOk()
            ->assertSee("Captain's Shield")
            ->assertSee('Stark Industries')
            ->assertSee('3');

        $this->actingAs($this->captain)
            ->get(route('items.mine'))
            ->assertOk()
            ->assertDontSee("Captain's Shield");
    }

    public function test_user_does_not_see_archived_items(): void
    {
        $item = $this->createItem();

        $item->users()->attach($this->member->id, [
            'assigned_at' => now()->subDay(),
            'unassigned_at' => now(),
        ]);

        $this->actingAs($this->member)
            ->get(route('items.mine'))
            ->assertOk()
            ->assertDontSee("Captain's Shield");
    }

    public function test_my_items_updates_after_reassignment(): void
    {
        $item = $this->createItem();

        $url = route('companies.warehouse.assign', [
            $this->company, $item,
        ]);

        // Assign to Tony
        $this->actingAs($this->captain)
            ->post($url, [
                'user_id' => $this->member->id,
            ])
            ->assertRedirect();

        // Tony sees the item
        $this->actingAs($this->member)
            ->get(route('items.mine'))
            ->assertOk()
            ->assertSee("Captain's Shield");

        // Reassign to Steve
        $this->actingAs($this->captain)
            ->post($url, [
                'user_id' => $this->captain->id,
            ])
            ->assertRedirect();

        // Tony no longer sees the item
        $this->actingAs($this->member)
            ->get(route('items.mine'))
            ->assertOk()
            ->assertDontSee("Captain's Shield");

        // Steve sees the item
        $this->actingAs($this->captain)
            ->get(route('items.mine'))
            ->assertOk()
            ->assertSee("Captain's Shield");

        $this->assertDatabaseCount('item_user', 2);
    }

    public function test_empty_my_items_shows_message(): void
    {
        $this->actingAs($this->member)
            ->get(route('items.mine'))
            ->assertOk()
            ->assertSee('No items assigned to you.');
    }

    public function test_unassigned_item_disappears_from_my_items(): void
    {
        $item = $this->createItem();

        $item->users()->attach($this->member->id, [
            'assigned_at' => now(),
        ]);

        $this->actingAs($this->captain)
            ->delete(route('companies.warehouse.unassign', [
                $this->company, $item,
            ]))
            ->assertRedirect();

        $this->actingAs($this->member)
            ->get(route('items.mine'))
            ->assertOk()
            ->assertDontSee("Captain's Shield");
    }

    public function test_removed_member_no_longer_sees_company_items(): void
    {
        $item = $this->createItem();

        $item->users()->attach($this->member->id, [
            'assigned_at' => now(),
        ]);

        $this->actingAs($this->captain)
            ->delete(route('companies.users.detach', [
                $this->company, $this->member,
            ]))
            ->assertRedirect();

        $this->actingAs($this->member)
            ->get(route('items.mine'))
            ->assertOk()
            ->assertDontSee("Captain's Shield");
    }
}
