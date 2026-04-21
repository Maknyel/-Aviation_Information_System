<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_item_id',
        'quantity_assigned',
        'assigned_location',
        'reference_type',
        'reference_id',
        'assigned_by',
        'assigned_at',
        'expected_return_at',
        'returned_at',
        'notes',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'expected_return_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
