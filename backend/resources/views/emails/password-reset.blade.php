@extends('emails.layout')

@section('content')
    <h2>Reset Your Password</h2>
    <p>Hello {{ $userName }},</p>
    <p>We received a request to reset the password for your account. Click the button below to set a new password.</p>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $url }}" class="btn">Reset Password</a>
    </div>

    <div class="info-box">
        <div class="label">Important</div>
        <div class="value" style="font-size: 14px; font-weight: normal;">This link will expire in 60 minutes.</div>
    </div>

    <p>If you did not request a password reset, no action is needed. Your account remains secure.</p>

    <p style="margin-top: 25px; font-size: 13px; color: #6b7280;">If the button above doesn't work, copy and paste this URL into your browser:</p>
    <p style="font-size: 12px; color: #4A7C59; word-break: break-all;">{{ $url }}</p>
@endsection
