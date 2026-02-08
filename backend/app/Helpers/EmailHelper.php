<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Mail;
use App\Mail\SystemEmail;
use Illuminate\Support\Facades\Log;

class EmailHelper
{
    /**
     * Send a general email using a template.
     */
    public static function send(string $to, string $subject, string $template, array $data = []): bool
    {
        try {
            Mail::to($to)->send(new SystemEmail($subject, $template, $data));
            Log::info("Email sent to {$to} | Subject: {$subject}");
            return true;
        } catch (\Exception $e) {
            Log::error("Email failed to {$to} | Error: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Send request status update notification.
     */
    public static function sendStatusUpdate(string $to, string $userName, string $requestType, int $requestId, string $status): bool
    {
        return self::send($to, "Request #{$requestId} Status Update", 'emails.status-update', [
            'userName' => $userName,
            'requestType' => $requestType === 'facility_request' ? 'Facility Request' : 'Work Order',
            'requestId' => $requestId,
            'status' => ucfirst($status),
        ]);
    }

    /**
     * Send new request assignment notification to staff.
     */
    public static function sendAssignment(string $to, string $staffName, string $requestType, int $requestId, string $description): bool
    {
        return self::send($to, "New Assignment: {$requestType} #{$requestId}", 'emails.assignment', [
            'staffName' => $staffName,
            'requestType' => $requestType,
            'requestId' => $requestId,
            'description' => $description,
        ]);
    }

    /**
     * Send approval required notification.
     */
    public static function sendApprovalRequired(string $to, string $approverName, string $requestType, int $requestId, string $requesterName): bool
    {
        return self::send($to, "Approval Required: {$requestType} #{$requestId}", 'emails.approval-required', [
            'approverName' => $approverName,
            'requestType' => $requestType,
            'requestId' => $requestId,
            'requesterName' => $requesterName,
        ]);
    }

    /**
     * Send welcome email to new user.
     */
    public static function sendWelcome(string $to, string $userName, string $role): bool
    {
        return self::send($to, 'Welcome to Aviation Information System', 'emails.welcome', [
            'userName' => $userName,
            'role' => $role,
        ]);
    }

    /**
     * Send a test email for template preview.
     */
    public static function sendTest(string $to, string $template): bool
    {
        $sampleData = self::getSampleData($template);
        $subject = "Test Email - {$template}";
        return self::send($to, $subject, "emails.{$template}", $sampleData);
    }

    /**
     * Get sample data for template testing.
     */
    public static function getSampleData(string $template): array
    {
        $samples = [
            'status-update' => [
                'userName' => 'Juan Dela Cruz',
                'requestType' => 'Facility Request',
                'requestId' => 1001,
                'status' => 'Approved',
            ],
            'assignment' => [
                'staffName' => 'Maria Santos',
                'requestType' => 'Work Order',
                'requestId' => 2005,
                'description' => 'Air conditioning unit in Room 301 is not cooling properly. Needs immediate inspection and repair.',
            ],
            'approval-required' => [
                'approverName' => 'Admin User',
                'requestType' => 'Facility Request',
                'requestId' => 1002,
                'requesterName' => 'Pedro Garcia',
            ],
            'welcome' => [
                'userName' => 'New Student',
                'role' => 'Student',
            ],
        ];

        return $samples[$template] ?? [];
    }

    /**
     * Get list of available templates.
     */
    public static function getAvailableTemplates(): array
    {
        return [
            ['name' => 'status-update', 'label' => 'Status Update Notification'],
            ['name' => 'assignment', 'label' => 'Task Assignment Notification'],
            ['name' => 'approval-required', 'label' => 'Approval Required Notification'],
            ['name' => 'welcome', 'label' => 'Welcome Email'],
        ];
    }
}
