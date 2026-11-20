<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Item extends Model
{
    protected $fillable = [
        'name',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ItemAssignment::class);
    }

    public function openAssignments(): HasMany
    {
        return $this->assignments()->open();
    }

    public function available(): int
    {
        $assigned = $this->relationLoaded('openAssignments')
            ? $this->openAssignments->sum('quantity')
            : $this->openAssignments()->sum('quantity');

        return $this->quantity - (int) $assigned;
    }

    public function assignTo(User $user, int $quantity, User $assignedBy): ItemAssignment
    {
        return DB::transaction(function () use ($user, $quantity, $assignedBy) {
            $item = $this->lockForAssignment();
            $available = $item->available();

            if ($quantity > $available) {
                throw ValidationException::withMessages([
                    'quantity' => "Only {$available} of {$item->quantity} ".Str::plural('piece', $item->quantity).' available.',
                ]);
            }

            return $item->assignments()->create([
                'user_id' => $user->id,
                'assigned_by' => $assignedBy->id,
                'quantity' => $quantity,
                'assigned_at' => now(),
            ]);
        });
    }

    public function lockForAssignment(): self
    {
        return static::whereKey($this->id)->lockForUpdate()->firstOrFail();
    }
}
