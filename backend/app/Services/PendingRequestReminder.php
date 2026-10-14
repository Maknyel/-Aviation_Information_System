<?php

namespace App\Services;

use App\Models\FacilityRequest;
use App\Models\Notification;
use App\Models\User;
use App\Models\WorkOrder;
use Carbon\Carbon;

class PendingRequestReminder
{
    public const AFTER_DAYS = 2;
    public const TITLE = 'Pending Request Reminder';

    /**
     * Remind every Admin about requests that have sat pending, untouched, for 2+ days.
     * Each request reminds an Admin at most once per 2 days so the bell does not fill up.
     *
     * @return int number of reminders created
     */
    public static function run(): int
    {
        $cutoff = Carbon::now()->subDays(self::AFTER_DAYS);
        $admins = User::whereHas('role', fn($q) => $q->where('name', 'Admin'))->get();
        if ($admins->isEmpty()) {
            return 0;
        }

        $created = 0;
        $groups = [
            'facility_request' => FacilityRequest::where('status', 'pending')->where('updated_at', '<=', $cutoff)->get(),
            'work_order' => WorkOrder::where('status', 'pending')->where('updated_at', '<=', $cutoff)->get(),
        ];

        foreach ($groups as $type => $requests) {
            foreach ($requests as $request) {
                $days = max(self::AFTER_DAYS, (int) $request->created_at->diffInDays(Carbon::now()));
                $label = $type === 'facility_request'
                    ? "facility request for {$request->venue_requested}"
                    : "work order for {$request->location}";

                foreach ($admins as $admin) {
                    $recent = Notification::where('user_id', $admin->id)
                        ->where('type', $type)
                        ->where('reference_id', $request->id)
                        ->where('title', self::TITLE)
                        ->where('created_at', '>', $cutoff)
                        ->exists();
                    if ($recent) {
                        continue;
                    }

                    Notification::create([
                        'user_id' => $admin->id,
                        'type' => $type,
                        'reference_id' => $request->id,
                        'title' => self::TITLE,
                        'message' => "You have a pending request from {$days} days ago ({$label}).",
                        'is_read' => false,
                        'is_deleted' => false,
                    ]);
                    $created++;
                }
            }
        }

        return $created;
    }
}
