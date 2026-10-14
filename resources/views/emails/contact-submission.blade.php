<!doctype html>
<html lang="en">
<body style="font-family:Arial,sans-serif;color:#1d1d1d;line-height:1.6">
    <h1>New marketplace contact submission</h1>
    <p><strong>Name:</strong> {{ $submission->name }}</p>
    <p><strong>Email:</strong> {{ $submission->email }}</p>
    @if($submission->organisation)<p><strong>Organisation:</strong> {{ $submission->organisation }}</p>@endif
    <p><strong>Subject:</strong> {{ $submission->subject }}</p>
    <hr>
    <p>{!! nl2br(e($submission->message)) !!}</p>
</body>
</html>
