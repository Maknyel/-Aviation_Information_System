<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacilityRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'department_id',
        'venue_requested',
        'location_room_number',
        'title_of_event',
        'time_of_event',
        'start_time',
        'end_time',
        'date_of_event',
        'status',
        'attachment_path',
        'assigned_to',
        'assigned_at',
    ];

    protected $casts = [
        'date_of_event' => 'date:Y-m-d',
        'assigned_at' => 'datetime',
    ];

    protected $appends = ['time_range'];

    /**
     * "08:00 - 10:00" style label; falls back to the lone time on older records.
     */
    public function getTimeRangeAttribute(): string
    {
        $start = $this->start_time ?: $this->time_of_event;

        return $this->end_time ? "{$start} - {$this->end_time}" : (string) $start;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function requestItems()
    {
        return $this->hasMany(FacilityRequestInventory::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class, 'request_id')->where('request_type', 'facility_request');
    }

    public function approvalSteps()
    {
        return $this->hasMany(ApprovalStep::class, 'request_id')->where('request_type', 'facility_request')->orderBy('step_order');
    }
}
