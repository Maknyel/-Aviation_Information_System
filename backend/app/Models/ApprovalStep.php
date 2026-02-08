<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_type',
        'request_id',
        'step_order',
        'approver_role',
        'approver_id',
        'status',
        'remarks',
        'acted_at',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
