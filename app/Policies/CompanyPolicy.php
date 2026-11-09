<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

/**
 * Uprawnienia do firm:
 * - każdy zalogowany widzi listę firm (ale osoba spoza firmy tylko jej nazwę),
 * - każdy zalogowany może założyć firmę i zostaje jej Kapitanem,
 * - członek firmy widzi szczegóły i listę członków, ale niczego nie zmienia,
 * - tylko Kapitan danej firmy (company_user.is_captain) nią zarządza.
 */
class CompanyPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Company $company): bool
    {
        return $company->isMember($user);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Company $company): bool
    {
        return $company->isCaptain($user);
    }

    public function delete(User $user, Company $company): bool
    {
        return $company->isCaptain($user);
    }

    public function manageUsers(User $user, Company $company): bool
    {
        return $company->isCaptain($user);
    }

    public function manageWarehouse(User $user, Company $company): bool
    {
        return $company->isCaptain($user);
    }
}
