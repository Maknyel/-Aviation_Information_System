<?php

namespace App\Services;

use App\Models\User;
use App\Models\WorkOrder;

class SmartAssigner
{
    /**
     * Map common problem keywords to skill categories
     */
    private static $skillMap = [
        'electrical' => ['electrical', 'wiring', 'outlet', 'switch', 'circuit', 'power', 'light', 'bulb', 'voltage', 'fuse'],
        'plumbing' => ['plumbing', 'pipe', 'faucet', 'leak', 'drain', 'toilet', 'water', 'sink', 'clog'],
        'IT' => ['computer', 'network', 'internet', 'wifi', 'printer', 'server', 'software', 'hardware', 'projector', 'monitor'],
        'carpentry' => ['door', 'window', 'cabinet', 'desk', 'chair', 'wood', 'hinge', 'lock', 'shelf', 'table'],
        'general' => ['paint', 'clean', 'wall', 'floor', 'ceiling', 'roof', 'ac', 'aircon', 'fan', 'ventilation'],
    ];

    /**
     * Recommend the best technician for a work order
     *
     * @return array List of recommended staff with scores
     */
    public static function recommend(string $description, string $location = ''): array
    {
        $requiredSkill = self::detectSkill($description);

        // Get all staff/admin users with their skills and current workload
        $staff = User::whereHas('role', fn($q) => $q->whereIn('name', ['Staff', 'Admin']))
            ->with('skills')
            ->withCount([
                'assignedWorkOrders as active_tasks' => function ($q) {
                    $q->whereIn('status', ['pending', 'in_progress', 'approved']);
                },
            ])
            ->get();

        $recommendations = [];

        foreach ($staff as $user) {
            $score = 0;

            // Skill match (0-50 points)
            $matchingSkill = $user->skills->firstWhere('skill', $requiredSkill);
            if ($matchingSkill) {
                $proficiencyScores = ['expert' => 50, 'intermediate' => 35, 'beginner' => 20];
                $score += $proficiencyScores[$matchingSkill->proficiency] ?? 20;
            } elseif ($user->skills->firstWhere('skill', 'general')) {
                $score += 10; // General skill fallback
            }

            // Workload (0-30 points, less work = higher score)
            $activeTasks = $user->active_tasks ?? 0;
            $workloadScore = max(0, 30 - ($activeTasks * 6));
            $score += $workloadScore;

            // Availability bonus (20 points if no active tasks)
            if ($activeTasks === 0) {
                $score += 20;
            }

            $recommendations[] = [
                'id' => $user->id,
                'name' => $user->name,
                'score' => $score,
                'active_tasks' => $activeTasks,
                'matching_skill' => $matchingSkill ? $matchingSkill->skill : null,
                'proficiency' => $matchingSkill ? $matchingSkill->proficiency : null,
            ];
        }

        // Sort by score descending
        usort($recommendations, fn($a, $b) => $b['score'] - $a['score']);

        return [
            'detected_skill' => $requiredSkill,
            'recommendations' => array_slice($recommendations, 0, 5),
        ];
    }

    /**
     * Detect the required skill from problem description
     */
    public static function detectSkill(string $description): string
    {
        $text = strtolower($description);
        $scores = [];

        foreach (self::$skillMap as $skill => $keywords) {
            $scores[$skill] = 0;
            foreach ($keywords as $keyword) {
                if (str_contains($text, $keyword)) {
                    $scores[$skill]++;
                }
            }
        }

        arsort($scores);
        $topSkill = array_key_first($scores);

        return ($scores[$topSkill] > 0) ? $topSkill : 'general';
    }
}
