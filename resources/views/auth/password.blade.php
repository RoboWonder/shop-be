<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shopbe - Reset Password</title>
</head>
<body>
<h1>Hello, {{$email}}</h1>

<p>
    Your temporary password is: <b>{{ $pwd }}</b> <br />
    This password will expire in {{ config('auth.reminder.expire', 5) }} minutes.
</p>

<h2>Thanks & Regards,</h2>

<h4>Shopbe</h4>
</body>
</html>
