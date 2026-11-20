<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
