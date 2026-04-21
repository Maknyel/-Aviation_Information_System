<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'facility_field',
        'category',
        'total_quantity',
        'unit',
        'condition',
        'notes',
    ];

    public function assignments()
    {
        return $this->hasMany(InventoryAssignment::class);
    }

    public function activeAssignments()
    {
        return $this->hasMany(InventoryAssignment::class)->whereNull('returned_at');
    }

}
