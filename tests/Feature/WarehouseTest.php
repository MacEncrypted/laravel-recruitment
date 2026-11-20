<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Item;
use App\Models\ItemAssignment;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class WarehouseTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $captain;
    private User $member;
    private User $outsider;
    private User $otherCaptain;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create(['name' => 'Stark Industries']);

        $this->captain = User::factory()->create();
        $this->member = User::factory()->create();
        $this->outsider = User::factory()->create();
        $this->otherCaptain = User::factory()->create();

        $this->company->users()->attach($this->captain->id, ['is_captain' => true]);
        $this->company->users()->attach($this->member->id);

        Company::create(['name' => 'Wayne Enterprises'])
            ->users()->attach($this->otherCaptain->id, ['is_captain' => true]);
    }

    public function test_captain_can_add_item(): void
    {
        $this->actingAs($this->captain)
            ->post(route('companies.warehouse.items.store', $this->company), ['name' => "Captain's Shield", 'quantity' => 3])
            ->assertRedirect(route('companies.warehouse.index', $this->company))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('items', [
            'company_id' => $this->company->id,
            'name' => "Captain's Shield",
            'quantity' => 3,
        ]);

        $this->actingAs($this->captain)
            ->get(route('companies.warehouse.index', $this->company))
            ->assertOk()
            ->assertSee("Captain's Shield");
    }

    public static function invalidItems(): array
    {
        return [
            'empty name' => ['', 3],
            'too long name' => [str_repeat('a', 256), 1],
            'zero pieces' => ['Shield', 0],
            'negative pieces' => ['Shield', -1],
            'fractional pieces' => ['Shield', 1.5],
            'empty pieces' => ['Shield', ''],
            'too many pieces' => ['Shield', 100001],
        ];
    }

    #[DataProvider('invalidItems')]
    public function test_add_item_validation(string $name, mixed $quantity): void
    {
        $this->actingAs($this->captain)
            ->post(route('companies.warehouse.items.store', $this->company), ['name' => $name, 'quantity' => $quantity])
            ->assertSessionHasErrors();

        $this->assertDatabaseCount('items', 0);
    }

    public function test_only_captain_can_add_items(): void
    {
        foreach ([$this->member, $this->outsider, $this->otherCaptain] as $user) {
            $this->actingAs($user)
                ->get(route('companies.warehouse.items.create', $this->company))
                ->assertForbidden();
            $this->actingAs($user)
                ->post(route('companies.warehouse.items.store', $this->company), ['name' => 'Shield', 'quantity' => 1])
                ->assertForbidden();
        }

        $this->assertDatabaseCount('items', 0);
    }

    public function test_item_name_is_escaped(): void
    {
        $this->company->items()->create(['name' => '<b>x</b>', 'quantity' => 1]);

        $this->actingAs($this->captain)
            ->get(route('companies.warehouse.index', $this->company))
            ->assertSee('&lt;b&gt;x&lt;/b&gt;', false)
            ->assertDontSee('<b>x</b>', false);
    }

    private function shield(int $quantity = 3): Item
    {
        return $this->company->items()->create(['name' => "Captain's Shield", 'quantity' => $quantity]);
    }

    private function assign(Item $item, User $user, mixed $quantity): TestResponse
    {
        return $this->actingAs($this->captain)
            ->post(route('companies.warehouse.assignments.store', [$this->company, $item]), [
                'user_id' => $user->id,
                'quantity' => $quantity,
            ]);
    }

    public function test_captain_can_assign_pieces_to_member(): void
    {
        $item = $this->shield();

        $this->assign($item, $this->member, 2)
            ->assertRedirect(route('companies.warehouse.items.show', [$this->company, $item]))
            ->assertSessionHas('success');

        $assignment = $item->assignments()->sole();
        $this->assertTrue($assignment->user->is($this->member));
        $this->assertTrue($assignment->assignedBy->is($this->captain));
        $this->assertSame(2, $assignment->quantity);
        $this->assertNotNull($assignment->assigned_at);
        $this->assertNull($assignment->returned_at);
        $this->assertSame(1, $item->available());

        $this->actingAs($this->captain)
            ->get(route('companies.warehouse.index', $this->company))
            ->assertSeeInOrder(["Captain's Shield", '3', '1', $this->member->name]);
    }

    public function test_cannot_assign_more_than_available(): void
    {
        $item = $this->shield();

        $this->assign($item, $this->member, 2)->assertSessionHasNoErrors();
        $this->assign($item, $this->captain, 2)->assertSessionHasErrors('quantity');

        $this->assertSame(1, $item->assignments()->count());
        $this->assertSame(1, $item->available());
    }

    public function test_same_member_can_hold_two_assignments_of_one_item(): void
    {
        $item = $this->shield();

        $this->assign($item, $this->member, 1)->assertSessionHasNoErrors();
        $this->assign($item, $this->member, 2)->assertSessionHasNoErrors();

        $this->assertSame(2, $item->openAssignments()->count());
        $this->assertSame(0, $item->available());
    }

    public function test_cannot_assign_to_someone_outside_the_company(): void
    {
        $item = $this->shield();

        $this->assign($item, $this->outsider, 1)->assertSessionHasErrors('user_id');
        $this->assign($item, $this->otherCaptain, 1)->assertSessionHasErrors('user_id');
        $this->actingAs($this->captain)
            ->post(route('companies.warehouse.assignments.store', [$this->company, $item]), ['user_id' => 999999, 'quantity' => 1])
            ->assertSessionHasErrors('user_id');

        $this->assertDatabaseCount('item_assignments', 0);
    }

    public static function invalidQuantities(): array
    {
        return [
            'zero' => [0],
            'negative' => [-1],
            'fractional' => [1.5],
            'empty' => [''],
        ];
    }

    #[DataProvider('invalidQuantities')]
    public function test_assign_quantity_validation(mixed $quantity): void
    {
        $this->assign($this->shield(), $this->member, $quantity)->assertSessionHasErrors('quantity');

        $this->assertDatabaseCount('item_assignments', 0);
    }

    public function test_only_captain_can_see_item_and_assign(): void
    {
        $item = $this->shield();

        foreach ([$this->member, $this->outsider, $this->otherCaptain] as $user) {
            $this->actingAs($user)
                ->get(route('companies.warehouse.items.show', [$this->company, $item]))
                ->assertForbidden();
            $this->actingAs($user)
                ->post(route('companies.warehouse.assignments.store', [$this->company, $item]), ['user_id' => $user->id, 'quantity' => 1])
                ->assertForbidden();
        }

        $this->assertDatabaseCount('item_assignments', 0);
    }

    public function test_item_from_another_company_is_not_found(): void
    {
        $otherItem = Company::where('name', 'Wayne Enterprises')->sole()
            ->items()->create(['name' => 'Batarang', 'quantity' => 5]);

        $this->actingAs($this->captain)
            ->get(route('companies.warehouse.items.show', [$this->company, $otherItem]))
            ->assertNotFound();
        $this->actingAs($this->captain)
            ->post(route('companies.warehouse.assignments.store', [$this->company, $otherItem]), ['user_id' => $this->member->id, 'quantity' => 1])
            ->assertNotFound();

        $this->assertDatabaseCount('item_assignments', 0);
    }

    private function reassign(Item $item, ItemAssignment $assignment, User $user): TestResponse
    {
        return $this->actingAs($this->captain)
            ->patch(route('companies.warehouse.assignments.update', [$this->company, $item, $assignment]), ['user_id' => $user->id]);
    }

    private function returnPieces(Item $item, ItemAssignment $assignment): TestResponse
    {
        return $this->actingAs($this->captain)
            ->patch(route('companies.warehouse.assignments.return', [$this->company, $item, $assignment]));
    }

    public function test_captain_can_reassign_pieces(): void
    {
        $item = $this->shield();
        $old = $item->assignTo($this->member, 2, $this->captain);

        $this->reassign($item, $old, $this->captain)
            ->assertRedirect(route('companies.warehouse.items.show', [$this->company, $item]))
            ->assertSessionHas('success');

        $this->assertNotNull($old->fresh()->returned_at);
        $new = $item->openAssignments()->sole();
        $this->assertTrue($new->user->is($this->captain));
        $this->assertTrue($new->assignedBy->is($this->captain));
        $this->assertSame(2, $new->quantity);
        $this->assertSame(1, $item->available());
    }

    public function test_cannot_reassign_to_outsider_or_current_holder(): void
    {
        $item = $this->shield();
        $assignment = $item->assignTo($this->member, 2, $this->captain);

        $this->reassign($item, $assignment, $this->outsider)->assertSessionHasErrors('user_id', null, 'reassign');
        $this->reassign($item, $assignment, $this->member)->assertSessionHasErrors('user_id', null, 'reassign');

        $this->assertSame(1, $item->assignments()->count());
        $this->assertNull($assignment->fresh()->returned_at);
    }

    public function test_captain_can_return_pieces_to_the_warehouse(): void
    {
        $item = $this->shield();
        $assignment = $item->assignTo($this->member, 2, $this->captain);

        $this->returnPieces($item, $assignment)
            ->assertRedirect(route('companies.warehouse.items.show', [$this->company, $item]))
            ->assertSessionHas('success');

        $this->assertNotNull($assignment->fresh()->returned_at);
        $this->assertSame(3, $item->available());
    }

    public function test_ended_assignment_cannot_be_returned_or_reassigned(): void
    {
        $item = $this->shield();
        $assignment = $item->assignTo($this->member, 2, $this->captain);
        $this->returnPieces($item, $assignment)->assertSessionHasNoErrors();
        $returnedAt = $assignment->fresh()->returned_at;

        $this->returnPieces($item, $assignment)->assertSessionHasErrors('assignment');
        $this->reassign($item, $assignment, $this->captain)->assertSessionHasErrors('assignment');

        $this->assertSame(1, $item->assignments()->count());
        $this->assertEquals($returnedAt, $assignment->fresh()->returned_at);
    }

    public function test_assignment_of_another_item_is_not_found(): void
    {
        $item = $this->shield();
        $otherAssignment = $this->company->items()->create(['name' => 'Arc Reactor', 'quantity' => 1])
            ->assignTo($this->member, 1, $this->captain);

        $this->reassign($item, $otherAssignment, $this->captain)->assertNotFound();
        $this->returnPieces($item, $otherAssignment)->assertNotFound();

        $this->assertNull($otherAssignment->fresh()->returned_at);
    }

    public function test_only_captain_can_reassign_or_return(): void
    {
        $item = $this->shield();
        $assignment = $item->assignTo($this->member, 2, $this->captain);

        foreach ([$this->member, $this->outsider, $this->otherCaptain] as $user) {
            $this->actingAs($user)
                ->patch(route('companies.warehouse.assignments.update', [$this->company, $item, $assignment]), ['user_id' => $user->id])
                ->assertForbidden();
            $this->actingAs($user)
                ->patch(route('companies.warehouse.assignments.return', [$this->company, $item, $assignment]))
                ->assertForbidden();
        }

        $this->assertNull($assignment->fresh()->returned_at);
        $this->assertSame(1, $item->assignments()->count());
    }

    public function test_captain_sees_item_history_newest_first(): void
    {
        $this->travelTo(now()->subDays(2));
        $item = $this->shield();
        $first = $item->assignTo($this->member, 2, $this->captain);
        $this->travelBack();
        $this->travelTo(now()->subDay());
        $second = $first->reassignTo($this->captain, $this->captain);
        $this->travelBack();
        $second->returnToWarehouse();

        $this->actingAs($this->captain)
            ->get(route('companies.warehouse.items.show', [$this->company, $item]))
            ->assertOk()
            ->assertSee('History')
            ->assertSeeInOrder([
                'History',
                $this->captain->name, $second->assigned_at->format('Y-m-d H:i'), $second->fresh()->returned_at->format('Y-m-d H:i'),
                $this->member->name, $first->assigned_at->format('Y-m-d H:i'), $first->fresh()->returned_at->format('Y-m-d H:i'),
            ]);
    }

    public function test_history_marks_current_assignment(): void
    {
        $item = $this->shield();
        $item->assignTo($this->member, 1, $this->captain);

        $this->actingAs($this->captain)
            ->get(route('companies.warehouse.items.show', [$this->company, $item]))
            ->assertSeeInOrder(['History', $this->member->name, 'Current']);
    }

    public function test_removing_member_returns_only_this_companys_items(): void
    {
        $item = $this->shield();
        $here = $item->assignTo($this->member, 2, $this->captain);

        $wayne = Company::where('name', 'Wayne Enterprises')->sole();
        $wayne->users()->attach($this->member->id);
        $elsewhere = $wayne->items()->create(['name' => 'Batarang', 'quantity' => 5])
            ->assignTo($this->member, 1, $this->otherCaptain);

        $this->actingAs($this->captain)
            ->delete(route('companies.users.detach', [$this->company, $this->member]))
            ->assertRedirect();

        $this->assertNotNull($here->fresh()->returned_at);
        $this->assertNull($elsewhere->fresh()->returned_at);
        $this->assertSame(3, $item->available());
        $this->assertSame(1, $item->assignments()->count());
    }

    public function test_deleting_account_returns_items(): void
    {
        $item = $this->shield();
        $item->assignTo($this->member, 2, $this->captain);

        $this->actingAs($this->member)
            ->delete(route('profile.destroy'), ['password' => 'password'])
            ->assertSessionHasNoErrors();

        $this->assertModelMissing($this->member);
        $this->assertSame(0, $item->assignments()->count());
        $this->assertSame(3, $item->available());
    }

    public function test_history_survives_deleting_the_person_who_assigned(): void
    {
        $item = $this->shield();
        $formerCaptain = User::factory()->create();
        $assignment = $item->assignTo($this->member, 1, $formerCaptain);

        $formerCaptain->delete();

        $this->assertNull($assignment->fresh()->assigned_by);
        $this->actingAs($this->captain)
            ->get(route('companies.warehouse.items.show', [$this->company, $item]))
            ->assertOk()
            ->assertSeeInOrder(['History', $this->member->name, '—']);
    }

    public function test_deleting_company_deletes_its_items_and_history(): void
    {
        $this->shield()->assignTo($this->member, 1, $this->captain);

        $this->actingAs($this->captain)
            ->delete(route('companies.destroy', $this->company))
            ->assertRedirect();

        $this->assertDatabaseCount('items', 0);
        $this->assertDatabaseCount('item_assignments', 0);
    }

    public function test_user_sees_their_current_items_from_all_companies(): void
    {
        $this->shield()->assignTo($this->member, 2, $this->captain);
        $wayne = Company::where('name', 'Wayne Enterprises')->sole();
        $wayne->users()->attach($this->member->id);
        $wayne->items()->create(['name' => 'Batarang', 'quantity' => 5])->assignTo($this->member, 1, $this->otherCaptain);

        $this->actingAs($this->member)
            ->get(route('items.mine'))
            ->assertOk()
            ->assertSeeInOrder(['Batarang', 'Wayne Enterprises', '1'])
            ->assertSeeInOrder(["Captain's Shield", 'Stark Industries', '2']);
    }

    public function test_my_items_hides_returned_items_and_items_of_others(): void
    {
        $item = $this->shield();
        $item->assignTo($this->member, 1, $this->captain)->returnToWarehouse();
        $this->company->items()->create(['name' => 'Arc Reactor', 'quantity' => 1])->assignTo($this->captain, 1, $this->captain);

        $this->actingAs($this->member)
            ->get(route('items.mine'))
            ->assertOk()
            ->assertDontSee("Captain's Shield")
            ->assertDontSee('Arc Reactor')
            ->assertSee('You have no items assigned right now.');
    }

    public function test_validation_messages_use_the_field_labels(): void
    {
        $this->actingAs($this->captain)
            ->from(route('companies.warehouse.items.create', $this->company))
            ->followingRedirects()
            ->post(route('companies.warehouse.items.store', $this->company), ['name' => 'Shield', 'quantity' => 0])
            ->assertSee('The pieces field must be at least 1.')
            ->assertDontSee('quantity field');

        $item = $this->shield();
        $this->actingAs($this->captain)
            ->from(route('companies.warehouse.items.show', [$this->company, $item]))
            ->followingRedirects()
            ->post(route('companies.warehouse.assignments.store', [$this->company, $item]), ['user_id' => $this->member->id, 'quantity' => ''])
            ->assertSee('The pieces field is required.');
    }

    public function test_piece_counts_use_singular_and_plural(): void
    {
        $item = $this->shield(1);
        $item->assignTo($this->member, 1, $this->captain);

        $this->actingAs($this->captain)
            ->get(route('companies.warehouse.items.show', [$this->company, $item]))
            ->assertSee('0 of 1 piece available.')
            ->assertSee("Reassign 1 piece held by {$this->member->name} to")
            ->assertSee("1 piece held by {$this->member->name}")
            ->assertDontSee('1 pieces');
    }

    public function test_assign_from_a_stale_page_explains_why_it_failed(): void
    {
        $item = $this->shield(1);
        $item->assignTo($this->member, 1, $this->captain);

        $this->actingAs($this->captain)
            ->from(route('companies.warehouse.items.show', [$this->company, $item]))
            ->followingRedirects()
            ->post(route('companies.warehouse.assignments.store', [$this->company, $item]), ['user_id' => $this->captain->id, 'quantity' => 1])
            ->assertSee('Only 0 of 1 piece available.');

        $this->assertSame(1, $item->assignments()->count());
    }

    public function test_failed_reassign_does_not_preselect_a_person_in_the_assign_form(): void
    {
        $item = $this->shield();
        $assignment = $item->assignTo($this->member, 1, $this->captain);

        $this->actingAs($this->captain)
            ->from(route('companies.warehouse.items.show', [$this->company, $item]))
            ->followingRedirects()
            ->patch(route('companies.warehouse.assignments.update', [$this->company, $item, $assignment]), ['user_id' => $this->member->id])
            ->assertSee('This person already holds these pieces.')
            ->assertDontSee('selected', false);
    }

    public function test_seeder_creates_warehouse_demo_data(): void
    {
        Company::query()->delete();
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $steve = User::where('email', 'captainamerica@avengers.com')->sole();
        $natasha = User::where('email', 'blackwidow@shield.gov')->sole();
        $wayne = Company::where('name', 'Wayne Enterprises')->sole();
        $stark = Company::where('name', 'Stark Industries')->sole();

        $stevesItems = $steve->itemAssignments()->open()->with('item')->get();
        $this->assertTrue($stevesItems->contains(
            fn ($assignment) => $assignment->item->company_id === $wayne->id && $assignment->assignedBy->is($natasha)
        ));

        $shield = $stark->items()->where('name', "Captain's Shield")->sole();
        $this->assertSame(3, $shield->quantity);
        $this->assertTrue($shield->assignments()->whereNotNull('returned_at')->exists());

        foreach (Item::all() as $item) {
            $this->assertGreaterThanOrEqual(0, $item->available(), $item->name);
        }
    }
}
