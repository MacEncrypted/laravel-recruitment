<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'quantity',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['id', 'assigned_at', 'unassigned_at']);
    }

    public function currentUser()
    {
        return $this->users()
            ->wherePivotNull('unassigned_at');
    }
}
