<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedRequestForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_type',
        'request_id',
        'saved_by',
        'notes',
    ];

    public function savedBy()
    {
        return $this->belongsTo(User::class, 'saved_by');
    }

    public function getRequestAttribute()
    {
        if ($this->request_type === 'facility_request') {
            return FacilityRequest::with(['user', 'department'])->find($this->request_id);
        }
        return WorkOrder::with(['user', 'department', 'assignee'])->find($this->request_id);
    }
}
