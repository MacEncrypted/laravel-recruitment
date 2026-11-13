<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CompanyUserController extends Controller
{
    public function index(Company $company)
    {
        Gate::authorize('view', $company);

        $users = $company->users()->orderBy('name')->paginate(10);
        $availableUsers = User::whereDoesntHave('companies', function ($query) use ($company) {
            $query->where('company_id', $company->id);
        })->orderBy('name')->get();

        return view('companies.users.index', compact('company', 'users', 'availableUsers'));
    }

    public function attach(Request $request, Company $company)
    {
        Gate::authorize('manageUsers', $company);

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $company->users()->syncWithoutDetaching([$request->user_id]);

        return redirect()->route('companies.users.index', $company)
            ->with('success', 'User assigned to company successfully.');
    }

    public function detach(Request $request, Company $company, User $user)
    {
        Gate::authorize('manageUsers', $company);

        // Kapitan nie może usunąć sam siebie – firma zostałaby bez nikogo, kto nią zarządza
        if ($user->is($request->user())) {
            return redirect()->route('companies.users.index', $company)
                ->with('error', 'The captain cannot remove themselves from the company.');
        }

        DB::transaction(function () use ($company, $user) {
            DB::table('item_user')
                ->where('user_id', $user->id)
                ->whereNull('unassigned_at')
                ->whereIn('item_id', $company->items()->select('id'))
                ->update([
                    'unassigned_at' => now(),
                ]);

            $company->users()->detach($user->id);
        });

        return redirect()->route('companies.users.index', $company)
            ->with('success', 'User removed from company successfully.');
    }
}
