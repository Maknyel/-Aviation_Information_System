<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\EmailHelper;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    /**
     * Get available email templates.
     */
    public function templates()
    {
        return response()->json([
            'success' => true,
            'data' => EmailHelper::getAvailableTemplates(),
        ]);
    }

    /**
     * Preview an email template (returns rendered HTML).
     */
    public function preview(Request $request)
    {
        $request->validate([
            'template' => 'required|string|in:status-update,assignment,approval-required,welcome',
        ]);

        $template = $request->template;
        $data = EmailHelper::getSampleData($template);

        try {
            $html = view("emails.{$template}", $data)->render();
            return response()->json([
                'success' => true,
                'data' => [
                    'html' => $html,
                    'template' => $template,
                    'sample_data' => $data,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to render template: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send a test email.
     */
    public function sendTest(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'template' => 'required|string|in:status-update,assignment,approval-required,welcome',
        ]);

        $success = EmailHelper::sendTest($request->email, $request->template);

        ActivityLog::log(
            'email_test',
            "Test email ({$request->template}) sent to {$request->email}",
            null, null, null,
            auth()->id()
        );

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Test email sent successfully' : 'Failed to send test email. Check your SMTP configuration.',
        ]);
    }

    /**
     * Get current SMTP configuration status (no secrets exposed).
     */
    public function smtpStatus()
    {
        $configured = config('mail.mailers.smtp.host') !== 'mailpit'
            && config('mail.mailers.smtp.username') !== null
            && config('mail.mailers.smtp.username') !== 'null';

        return response()->json([
            'success' => true,
            'data' => [
                'configured' => $configured,
                'mailer' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'encryption' => config('mail.mailers.smtp.encryption'),
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
            ],
        ]);
    }
}
