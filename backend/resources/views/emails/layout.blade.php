<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Aviation Information System' }}</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f4f5f7; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .email-wrapper { width: 100%; background-color: #f4f5f7; padding: 40px 0; }
        .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .email-header { background-color: #4A7C59; padding: 30px 40px; text-align: center; }
        .email-header h1 { color: #ffffff; margin: 0; font-size: 22px; font-weight: 600; letter-spacing: 0.5px; }
        .email-header p { color: #d4e8da; margin: 5px 0 0; font-size: 13px; }
        .email-body { padding: 40px; color: #333333; line-height: 1.6; }
        .email-body h2 { color: #2d2d2d; font-size: 20px; margin-top: 0; margin-bottom: 20px; }
        .email-body p { margin: 0 0 15px; font-size: 15px; }
        .info-box { background-color: #f0f7f2; border-left: 4px solid #4A7C59; padding: 15px 20px; border-radius: 0 8px 8px 0; margin: 20px 0; }
        .info-box .label { font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
        .info-box .value { font-size: 16px; color: #1f2937; font-weight: 600; }
        .status-badge { display: inline-block; padding: 6px 16px; border-radius: 20px; font-size: 14px; font-weight: 600; }
        .status-approved { background-color: #d1fae5; color: #065f46; }
        .status-rejected { background-color: #fee2e2; color: #991b1b; }
        .status-pending { background-color: #fef3c7; color: #92400e; }
        .status-in_progress { background-color: #dbeafe; color: #1e40af; }
        .status-completed { background-color: #d1fae5; color: #065f46; }
        .btn { display: inline-block; padding: 12px 28px; background-color: #4A7C59; color: #ffffff !important; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 600; margin-top: 10px; }
        .btn:hover { background-color: #3d6a4b; }
        .email-footer { padding: 25px 40px; background-color: #f9fafb; border-top: 1px solid #e5e7eb; text-align: center; }
        .email-footer p { margin: 0; font-size: 12px; color: #9ca3af; }
        .email-footer a { color: #4A7C59; text-decoration: none; }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="email-header">
                <h1>Aviation Information System</h1>
                <p>National Aviation Academy of the Philippines</p>
            </div>
            <div class="email-body">
                @yield('content')
            </div>
            <div class="email-footer">
                <p>This is an automated message from the Aviation Information System.</p>
                <p style="margin-top: 8px;">&copy; {{ date('Y') }} National Aviation Academy of the Philippines</p>
            </div>
        </div>
    </div>
</body>
</html>
