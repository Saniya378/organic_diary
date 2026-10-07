<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registration Verification Code</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #FAF7F0; margin: 0; padding: 24px; color: #1F2A22; }
        .email-container { max-width: 580px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #ECE6D6; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.04); }
        .header { background: #1F3A27; color: #ffffff; padding: 32px 28px; text-align: center; }
        .header h1 { margin: 0 0 6px 0; font-family: Georgia, serif; font-size: 24px; color: #ffffff; }
        .header p { margin: 0; color: #E0A93B; font-size: 14px; font-weight: 500; letter-spacing: 0.5px; }
        .content { padding: 32px 28px; text-align: center; }
        .greeting { font-size: 18px; font-weight: 600; color: #1F3A27; margin-bottom: 12px; }
        .subtext { font-size: 15px; line-height: 1.6; color: #4A5568; margin-bottom: 24px; }
        .otp-box { background: #F6F4EB; border: 2px dashed #E0A93B; border-radius: 14px; padding: 20px; display: inline-block; margin: 12px auto 24px; }
        .otp-code { font-family: 'Courier New', Courier, monospace; font-size: 36px; font-weight: bold; letter-spacing: 8px; color: #1F3A27; }
        .otp-timer { font-size: 13px; color: #718096; margin-top: 6px; }
        .security-notice { background: #FFF8E1; border-radius: 10px; padding: 14px 18px; font-size: 13px; color: #9C6B08; text-align: left; margin-top: 24px; }
        .footer { background: #FAF7F0; text-align: center; padding: 20px; font-size: 12px; color: #6B756D; border-top: 1px solid #ECE6D6; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>Organic Dairy Gilgit-Baltistan</h1>
            <p>100% PURE &bull; MOUNTAIN HARVESTED &bull; FARM DIRECT</p>
        </div>

        <div class="content">
            <div class="greeting">Welcome, {{ $name }}!</div>
            <p class="subtext">
                Thank you for joining Organic Dairy Gilgit-Baltistan. Please use the 6-digit verification code below to verify your email address and activate your account:
            </p>

            <div class="otp-box">
                <div class="otp-code">{{ $otp }}</div>
                <div class="otp-timer">&#9201; Valid for {{ $expireMinutes }} minutes</div>
            </div>

            <p style="font-size: 14px; color: #4A5568;">
                Enter this code on the registration verification screen to complete your registration.
            </p>

            <div class="security-notice">
                <strong>&#128274; Security Warning:</strong> Never share this code with anyone. Organic Dairy support representatives will never ask for your verification code or password. If you did not request this registration, you can safely disregard this email.
            </div>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Organic Dairy Marketplace. High-Altitude Organic Farms of Gilgit-Baltistan.
        </div>
    </div>
</body>
</html>
