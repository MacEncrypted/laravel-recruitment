<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Item;
use App\Models\ItemAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemAssignmentController extends Controller
{
    public function store(Request $request, Company $company, Item $item)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'quantity' => 'required|integer|min:1',
        ], [], ['user_id' => 'person', 'quantity' => 'pieces']);

        $item->assignTo(User::findOrFail($validated['user_id']), $validated['quantity'], $request->user());

        return redirect()->route('companies.warehouse.items.show', [$company, $item])
            ->with('success', 'Pieces assigned.');
    }

    public function update(Request $request, Company $company, Item $item, ItemAssignment $assignment)
    {
        $validated = $request->validateWithBag('reassign', [
            'user_id' => ['required', 'integer', 'exists:users,id', Rule::notIn([$assignment->user_id])],
        ], [
            'user_id.not_in' => 'This person already holds these pieces.',
        ], ['user_id' => 'person']);

        $assignment->reassignTo(User::findOrFail($validated['user_id']), $request->user());

        return redirect()->route('companies.warehouse.items.show', [$company, $item])
            ->with('success', 'Pieces reassigned.');
    }

    public function returnToWarehouse(Company $company, Item $item, ItemAssignment $assignment)
    {
        $assignment->returnToWarehouse();

        return redirect()->route('companies.warehouse.items.show', [$company, $item])
            ->with('success', 'Pieces returned to the warehouse.');
    }
}
