<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Item;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemAssignmentController extends Controller
{
    public function store(Request $request, Company $company, Item $item)
    {
        $validated = $request->validate([
            'user_id' => ['required', $this->companyMember($company)],
            'quantity' => 'required|integer|min:1',
        ], $this->messages());

        $item->assignTo(User::findOrFail($validated['user_id']), $validated['quantity'], $request->user());

        return redirect()->route('companies.warehouse.items.show', [$company, $item])
            ->with('success', 'Pieces assigned.');
    }

    private function companyMember(Company $company)
    {
        return Rule::exists('company_user', 'user_id')->where('company_id', $company->id);
    }

    private function messages(): array
    {
        return [
            'user_id.required' => 'Choose a person.',
            'user_id.exists' => 'Choose a member of this company.',
        ];
    }
}
