<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedBy(): BelongsTo
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

    public function close(): bool
    {
        $now = now();

        if (static::whereKey($this->id)->open()->update(['returned_at' => $now]) !== 1) {
            return false;
        }

        $this->returned_at = $now;
        $this->syncOriginalAttribute('returned_at');

        return true;
    }

    public function reassignTo(User $user, User $assignedBy): self
    {
        return DB::transaction(function () use ($user, $assignedBy) {
            $item = $this->item->lockForAssignment();
            $this->closeOrFail();

            return $item->assignments()->create([
                'user_id' => $user->id,
                'assigned_by' => $assignedBy->id,
                'quantity' => $this->quantity,
                'assigned_at' => now(),
            ]);
        });
    }

    public function returnToWarehouse(): void
    {
        DB::transaction(function () {
            $this->item->lockForAssignment();
            $this->closeOrFail();
        });
    }

    private function closeOrFail(): void
    {
        if (! $this->close()) {
            throw ValidationException::withMessages([
                'assignment' => 'This assignment has already ended.',
            ]);
        }
    }
}
