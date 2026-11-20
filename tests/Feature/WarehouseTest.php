<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
