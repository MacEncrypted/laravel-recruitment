<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class WarehouseController extends Controller
{
    public function index(Company $company)
    {
        $items = $company->items()
            ->with(['currentUser', 'users'])
            ->get();

        $users = $company->users;

        return view('companies.warehouse.index',
            compact('company', 'items', 'users'));
    }

    public function show(Company $company, Item $item)
    {
        Gate::authorize('manageWarehouse', $company);

        abort_unless($item->company_id === $company->id, 404);

        $item->load(['currentUser', 'users']);

        $users = $company->users()->orderBy('name')->get();

        return view('companies.warehouse.show', compact('company', 'item', 'users'));
    }

    public function store(Request $request, Company $company)
    {
        Gate::authorize('manageWarehouse', $company);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
        ]);

        $company->items()->create($data);

        return back();
    }

    public function assign(
        Request $request,
        Company $company,
        Item $item
    ) {
        Gate::authorize('manageWarehouse', $company);

        abort_unless($item->company_id === $company->id, 404);

        $data = $request->validate([
            'user_id' => [
                'required',
                Rule::exists('company_user', 'user_id')
                    ->where('company_id', $company->id),
            ],
        ]);

        DB::transaction(function () use ($item, $data) {
            $item = Item::whereKey($item->id)
                ->lockForUpdate()
                ->firstOrFail();

            $current = DB::table('item_user')
                ->where('item_id', $item->id)
                ->whereNull('unassigned_at')
                ->first();

            if ($current && $current->user_id == $data['user_id']) {
                return;
            }

            $now = now();

            DB::table('item_user')
                ->where('item_id', $item->id)
                ->whereNull('unassigned_at')
                ->update(['unassigned_at' => $now]);

            DB::table('item_user')->insert([
                'item_id' => $item->id,
                'user_id' => $data['user_id'],
                'assigned_at' => $now,
            ]);
        });

        return redirect()
                ->route('companies.warehouse.show', [$company, $item])
                ->with('success', 'Item assigned successfully.');
    }
}
