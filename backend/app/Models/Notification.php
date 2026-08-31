<?php

namespace App\Models;

use App\Events\NotificationCreated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::created(function (Notification $notification) {
            // Broadcasting is a best-effort side channel (e.g. Pusher) — never let it
            // block or fail the request that created this notification.
            try {
                broadcast(new NotificationCreated($notification));
            } catch (\Throwable $e) {
                report($e);
            }
        });
    }

    protected $fillable = [
        'user_id',
        'type',
        'reference_id',
        'title',
        'message',
        'is_read',
        'is_deleted',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
