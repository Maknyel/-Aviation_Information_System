<?php

namespace App\Services;

use App\Models\FacilityRequest;

class ConflictDetector
{
    /**
     * Check if a venue is already booked for a given date and time
     *
     * @return array|null Returns conflicting request data or null if no conflict
     */
    public static function check(string $venue, string $date, string $time, ?int $excludeId = null): ?array
    {
        $query = FacilityRequest::where('venue_requested', $venue)
            ->whereDate('date_of_event', $date)
            ->where('time_of_event', $time)
            ->whereIn('status', ['pending', 'approved']);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $conflict = $query->first();

        if ($conflict) {
            return [
                'id' => $conflict->id,
                'title' => $conflict->title_of_event,
                'user' => $conflict->user->name ?? 'Unknown',
                'status' => $conflict->status,
                'date' => $conflict->date_of_event->format('Y-m-d'),
                'time' => $conflict->time_of_event,
            ];
        }

        return null;
    }

    /**
     * Get all bookings for a venue on a date
     */
    public static function getBookingsForDate(string $venue, string $date): array
    {
        return FacilityRequest::where('venue_requested', $venue)
            ->whereDate('date_of_event', $date)
            ->whereIn('status', ['pending', 'approved'])
            ->with('user')
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'title' => $r->title_of_event,
                'time' => $r->time_of_event,
                'status' => $r->status,
                'user' => $r->user->name ?? 'Unknown',
            ])
            ->toArray();
    }
}
