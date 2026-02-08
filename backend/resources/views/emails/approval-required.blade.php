@extends('emails.layout')

@section('content')
    <h2>Approval Required</h2>
    <p>Hello {{ $approverName }},</p>
    <p>A new <strong>{{ $requestType }}</strong> requires your approval.</p>

    <div class="info-box">
        <div class="label">Request ID</div>
        <div class="value">#{{ $requestId }}</div>
    </div>

    <div class="info-box">
        <div class="label">Requested By</div>
        <div class="value">{{ $requesterName }}</div>
    </div>

    <p>Please review and approve or reject this request at your earliest convenience.</p>

    <a href="{{ config('app.url') }}" class="btn">Review Request</a>
@endsection
