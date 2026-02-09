@extends('emails.layout')

@section('content')
    <h2>Welcome to Aviation Information System</h2>
    <p>Hello {{ $userName }},</p>
    <p>Your account has been created successfully. You have been assigned the role of <strong>{{ $role }}</strong>.</p>

    <div class="info-box">
        <div class="label">Your Role</div>
        <div class="value">{{ $role }}</div>
    </div>

    <p>You can now log in to the system to access your dashboard, submit requests, and manage your profile.</p>

    <a href="{{ env('FRONTEND_URL', 'https://aviation.dl-hosting.net') }}/login" class="btn">Log In Now</a>

    <p style="margin-top: 25px; font-size: 13px; color: #6b7280;">If you did not create this account, please contact the system administrator immediately.</p>
@endsection
