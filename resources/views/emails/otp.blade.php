<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Kode OTP</title>
</head>
<body style="font-family: Arial, sans-serif;">
    <h2>Halo, {{ $name }}</h2>

    <p>Kamu menerima kode OTP untuk reset password.</p>

    <h1 style="letter-spacing: 5px;">{{ $otp }}</h1>

    <p>Kode ini berlaku selama <b>5 menit</b>.</p>

    <p>Jika kamu tidak merasa meminta reset password, abaikan email ini.</p>

    <br>
    <p>Terima kasih,<br>
    <b>Tim Support</b></p>
</body>
</html>
