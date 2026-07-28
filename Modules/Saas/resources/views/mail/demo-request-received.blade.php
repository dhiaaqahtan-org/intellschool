<!doctype html>
<html lang="en">
<body>
    <h1>New demo request</h1>
    <dl>
        <dt>Name</dt><dd>{{ $demoRequest->name }}</dd>
        <dt>School</dt><dd>{{ $demoRequest->school }}</dd>
        <dt>Email</dt><dd><a href="mailto:{{ $demoRequest->email }}">{{ $demoRequest->email }}</a></dd>
        @if($demoRequest->phone)<dt>Phone</dt><dd>{{ $demoRequest->phone }}</dd>@endif
        @if($demoRequest->school_size)<dt>School size</dt><dd>{{ $demoRequest->school_size }}</dd>@endif
        @if($demoRequest->message)<dt>Request</dt><dd>{{ $demoRequest->message }}</dd>@endif
        <dt>Language</dt><dd>{{ $demoRequest->locale }}</dd>
        <dt>Consent recorded</dt><dd>{{ $demoRequest->consent_at?->toIso8601String() }}</dd>
    </dl>
</body>
</html>
