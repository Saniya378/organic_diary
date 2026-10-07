<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #FAF7F0; color: #2D3748; margin: 0; padding: 20px; }
        .container { max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #E2E8F0; }
        .header { background-color: #2F5D3A; color: #ffffff; padding: 24px; text-align: center; }
        .content { padding: 30px 24px; line-height: 1.6; }
        .btn { display: inline-block; background-color: #2F5D3A; color: #ffffff !important; padding: 12px 28px; text-decoration: none; border-radius: 25px; font-weight: bold; margin: 20px 0; }
        .footer { background-color: #F8F9FA; padding: 16px; text-align: center; font-size: 12px; color: #718096; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin: 0; font-family: Georgia, serif;">Organic Dairy Marketplace</h2>
            <p style="margin: 4px 0 0; font-size: 13px; opacity: 0.9;">Gilgit-Baltistan Fresh Pure Dairy</p>
        </div>
        <div class="content">
            <p>Hello <strong>{{ $user->name }}</strong>,</p>
            <p>You recently requested to reset your password for your Organic Dairy account. Click the button below to set a new password:</p>
            
            <div style="text-align: center;">
                <a href="{{ $resetUrl }}" class="btn">Reset My Password</a>
            </div>

            <p style="font-size: 13px; color: #718096;">This password reset link is valid for <strong>60 minutes</strong>. If you did not request a password reset, you can safely ignore this email.</p>
            
            <hr style="border: none; border-top: 1px solid #E2E8F0; margin: 20px 0;">
            <p style="font-size: 12px; color: #A0AEC0; word-break: break-all;">If you're having trouble clicking the button, copy and paste this URL into your browser:<br>{{ $resetUrl }}</p>
        </div>
        <div class="footer">
            &copy; 2026 Organic Dairy Marketplace. Pure mountain dairy delivered to your door.
        </div>
    </div>
</body>
</html>
