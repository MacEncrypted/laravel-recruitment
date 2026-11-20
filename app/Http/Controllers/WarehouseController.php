<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Item;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index(Company $company)
    {
        $items = $company->items()->with('openAssignments.user')->orderBy('name')->get();

        return view('companies.warehouse.index', compact('company', 'items'));
    }

    public function show(Company $company, Item $item)
    {
        $item->load('openAssignments.user');
        $members = $company->users()->orderBy('name')->get();
        $history = $item->assignments()
            ->with(['user', 'assignedBy'])
            ->orderByDesc('assigned_at')
            ->orderByDesc('id')
            ->get();

        return view('companies.warehouse.show', compact('company', 'item', 'members', 'history'));
    }

    public function create(Company $company)
    {
        return view('companies.warehouse.create', compact('company'));
    }

    public function store(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1|max:100000',
        ], [], ['quantity' => 'pieces']);

        $company->items()->create($validated);

        return redirect()->route('companies.warehouse.index', $company)
            ->with('success', 'Item added to the warehouse.');
    }
}
