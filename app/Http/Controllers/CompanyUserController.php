<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;

class CompanyUserController extends Controller
{
    public function index(Company $company)
    {
        $users = $company->users()->paginate(10);
        $availableUsers = User::whereDoesntHave('companies', function($query) use ($company) {
            $query->where('company_id', $company->id);
        })->get();

        return view('companies.users.index', compact('company', 'users', 'availableUsers'));
    }

    public function attach(Request $request, Company $company)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $company->users()->attach($request->user_id);

        return redirect()->route('companies.users.index', $company)
            ->with('success', 'User assigned to company successfully.');
    }

    public function detach(Company $company, User $user)
    {
        $company->users()->detach($user->id);

        return redirect()->route('companies.users.index', $company)
            ->with('success', 'User removed from company successfully.');
    }
}
