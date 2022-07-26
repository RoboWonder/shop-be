<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up Confirmation</title>
</head>
<body>
    <h1>Hello, {{$email}}</h1>

    <p>
        Please click below link to confirm your account: <a href='{{ url('user/register/verification/' . $email_token) }}'>Confirmation Link</a>
    </p>

    <h2>Thanks & Regards,</h2>

    <h4>Shopbe</h4>
</body>
</html>
