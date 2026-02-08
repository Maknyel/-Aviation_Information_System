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
        'date_of_event',
        'chair',
        'podium',
        'tent',
        'tables',
        'booths',
        'sound_system',
        'extension',
        'microphones',
        'skirting',
        'flags',
        'others',
        'others_description',
        'status',
    ];

    protected $casts = [
        'date_of_event' => 'date',
        'chair' => 'boolean',
        'podium' => 'boolean',
        'tent' => 'boolean',
        'tables' => 'boolean',
        'booths' => 'boolean',
        'sound_system' => 'boolean',
        'extension' => 'boolean',
        'microphones' => 'boolean',
        'skirting' => 'boolean',
        'flags' => 'boolean',
        'others' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
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
