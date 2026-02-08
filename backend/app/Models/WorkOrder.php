<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'department_id',
        'location',
        'room_number',
        'date',
        'time',
        'description_of_problem',
        'image',
        'requisitioner',
        'priority',
        'status',
        'assigned_to',
        'assigned_at',
        'completed_at',
        'admin_remarks',
    ];

    protected $casts = [
        'date' => 'date',
        'assigned_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class, 'request_id')->where('request_type', 'work_order');
    }

    public function approvalSteps()
    {
        return $this->hasMany(ApprovalStep::class, 'request_id')->where('request_type', 'work_order')->orderBy('step_order');
    }
}
