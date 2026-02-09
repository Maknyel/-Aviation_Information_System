@extends('emails.layout')

@section('content')
    <h2>Request Status Update</h2>
    <p>Hello {{ $userName }},</p>
    <p>Your <strong>{{ $requestType }}</strong> has been updated with a new status.</p>

    <div class="info-box">
        <div class="label">Request ID</div>
        <div class="value">#{{ $requestId }}</div>
    </div>

    <div class="info-box">
        <div class="label">New Status</div>
        <div class="value">
            <span class="status-badge status-{{ strtolower($status) }}">{{ $status }}</span>
        </div>
    </div>

    <p>You can view the full details of your request by logging into the system.</p>

    <a href="{{ env('FRONTEND_URL', 'https://aviation.dl-hosting.net') }}/requests" class="btn">View Request</a>
@endsection
