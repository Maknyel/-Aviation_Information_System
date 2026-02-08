<?php

namespace App\Services;

class PriorityClassifier
{
    /**
     * Keywords mapped to priority levels for work orders
     */
    private static $urgentKeywords = [
        'fire', 'flood', 'gas leak', 'electrical shock', 'smoke', 'sparks',
        'collapsed', 'broken pipe', 'water damage', 'power outage', 'blackout',
        'emergency', 'danger', 'hazard', 'safety risk', 'exposed wire',
        'short circuit', 'burst', 'explosion', 'injury', 'accident',
    ];

    private static $highKeywords = [
        'leak', 'broken', 'not working', 'malfunctioning', 'damaged',
        'no water', 'no electricity', 'clogged', 'overflowing', 'cracked',
        'faulty', 'defective', 'failed', 'inoperable', 'out of order',
        'security', 'lock broken', 'door stuck', 'roof leak',
    ];

    private static $mediumKeywords = [
        'repair', 'fix', 'replace', 'maintenance', 'worn out', 'flickering',
        'noisy', 'slow', 'dripping', 'loose', 'rusty', 'paint', 'dent',
        'stain', 'crack', 'chipped', 'squeaky', 'unstable',
    ];

    /**
     * Locations that bump priority up
     */
    private static $criticalLocations = [
        'hangar', 'laboratory', 'server room', 'electrical room',
        'main building', 'admin office', 'clinic',
    ];

    /**
     * Auto-classify priority based on description and location
     */
    public static function classify(string $description, string $location = ''): string
    {
        $text = strtolower($description . ' ' . $location);

        // Check urgent keywords first
        foreach (self::$urgentKeywords as $keyword) {
            if (str_contains($text, $keyword)) {
                return 'urgent';
            }
        }

        // Check high keywords
        $highScore = 0;
        foreach (self::$highKeywords as $keyword) {
            if (str_contains($text, $keyword)) {
                $highScore++;
            }
        }

        // Check medium keywords
        $mediumScore = 0;
        foreach (self::$mediumKeywords as $keyword) {
            if (str_contains($text, $keyword)) {
                $mediumScore++;
            }
        }

        // Location boost
        $locationBoost = false;
        foreach (self::$criticalLocations as $loc) {
            if (str_contains(strtolower($location), $loc)) {
                $locationBoost = true;
                break;
            }
        }

        // Determine priority
        if ($highScore >= 2 || ($highScore >= 1 && $locationBoost)) {
            return 'high';
        }

        if ($highScore >= 1 || $mediumScore >= 2) {
            return $locationBoost ? 'high' : 'medium';
        }

        if ($mediumScore >= 1) {
            return 'medium';
        }

        return 'low';
    }
}
