<!DOCTYPE html>
<html>
<head>
    <title>SMTP Test</title>
</head>
<body>
    <h2>SMTP Configuration Successful</h2>
    <p>{{ $body ?? 'Your SMTP settings have been configured correctly and your system can now send emails.' }}</p>
    @if(isset($trackingToken))
        <img src="{{ route('email.track_open', ['token' => $trackingToken]) }}" width="1" height="1" style="display:none;" />
    @endif
</body>
</html>
