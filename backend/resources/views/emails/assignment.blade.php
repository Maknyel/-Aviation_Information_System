@extends('emails.layout')

@section('content')
    <h2>New Task Assignment</h2>
    <p>Hello {{ $staffName }},</p>
    <p>A new <strong>{{ $requestType }}</strong> has been assigned to you.</p>

    <div class="info-box">
        <div class="label">Request ID</div>
        <div class="value">#{{ $requestId }}</div>
    </div>

    <div class="info-box">
        <div class="label">Description</div>
        <div class="value" style="font-size: 14px; font-weight: 400;">{{ $description }}</div>
    </div>

    <p>Please review and take action on this assignment as soon as possible.</p>

    <a href="{{ env('FRONTEND_URL', 'https://aviation.dl-hosting.net') }}/requests" class="btn">View Assignment</a>
@endsection
