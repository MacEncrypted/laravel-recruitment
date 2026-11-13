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

        $this->seed();

        $this->company = Company::where('name', 'Stark Industries')
            ->firstOrFail();

        $this->captain = User::where(
            'email', 'captainamerica@avengers.com'
        )->firstOrFail();

        $this->member = User::where(
            'email', 'ironman@starkindustries.com'
        )->firstOrFail();
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
}
