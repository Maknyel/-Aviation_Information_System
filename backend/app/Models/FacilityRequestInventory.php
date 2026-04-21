<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacilityRequestInventory extends Model
{
    protected $table = 'facility_requests_inventory';

    protected $fillable = ['facility_request_id', 'inventory_item_id', 'quantity'];

    public function facilityRequest()
    {
        return $this->belongsTo(FacilityRequest::class);
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
