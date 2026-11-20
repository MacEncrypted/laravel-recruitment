<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'name',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($company) {
            if (empty($company->uuid)) {
                $company->uuid = (string) Str::uuid();
            }
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('is_captain')
            ->withTimestamps();
    }

    public function isMember(User $user): bool
    {
        return $this->users()->whereKey($user->id)->exists();
    }

    public function ensureMember(User $user, string $errorBag = 'default'): void
    {
        if (! $this->isMember($user)) {
            throw ValidationException::withMessages([
                'user_id' => 'Choose a member of this company.',
            ])->errorBag($errorBag);
        }
    }

    public function isCaptain(User $user): bool
    {
        return $this->users()->whereKey($user->id)->wherePivot('is_captain', true)->exists();
    }
}
