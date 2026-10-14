<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'description',
    ];

    public const TYPES = ['Office', 'Student Org', 'Faculty', 'Employee'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function facilityRequests()
    {
        return $this->hasMany(FacilityRequest::class);
    }

    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class);
    }
}
