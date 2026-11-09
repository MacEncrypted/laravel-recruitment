<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::withCount('users')->latest()->paginate(10);
        return view('companies.index', compact('companies'));
    }

    public function create()
    {
        Gate::authorize('create', Company::class);

        return view('companies.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Company::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $company = Company::create($validated);

        // Kto zakłada firmę, zostaje jej Kapitanem
        $company->users()->attach($request->user()->id, ['is_captain' => true]);

        return redirect()->route('companies.index')
            ->with('success', 'Company created successfully.');
    }

    public function show(Company $company)
    {
        Gate::authorize('view', $company);

        return view('companies.show', compact('company'));
    }

    public function edit(Company $company)
    {
        Gate::authorize('update', $company);

        return view('companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        Gate::authorize('update', $company);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $company->update($validated);

        return redirect()->route('companies.index')
            ->with('success', 'Company updated successfully.');
    }

    public function destroy(Company $company)
    {
        Gate::authorize('delete', $company);

        $company->delete();

        return redirect()->route('companies.index')
            ->with('success', 'Company deleted successfully.');
    }
}
