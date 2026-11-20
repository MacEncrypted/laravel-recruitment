<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ItemAssignment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'assigned_by',
        'quantity',
        'assigned_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'assigned_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function scopeOpen(Builder $query): void
    {
        $query->whereNull('returned_at');
    }

    public function isOpen(): bool
    {
        return $this->returned_at === null;
    }

    public function reassignTo(User $user, User $assignedBy): self
    {
        return DB::transaction(function () use ($user, $assignedBy) {
            $this->item->company->ensureMember($user, 'reassign');
            $this->returnToWarehouse();

            return $this->item->assignments()->create([
                'user_id' => $user->id,
                'assigned_by' => $assignedBy->id,
                'quantity' => $this->quantity,
                'assigned_at' => now(),
            ]);
        }, 3);
    }

    public function returnToWarehouse(): void
    {
        $now = now();

        if (static::whereKey($this->id)->open()->update(['returned_at' => $now]) !== 1) {
            throw ValidationException::withMessages([
                'assignment' => 'This assignment has already ended.',
            ]);
        }

        $this->returned_at = $now;
        $this->syncOriginalAttribute('returned_at');
    }
}
