<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Farm Application Update</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #FAF7F0; margin: 0; padding: 24px; color: #1F2A22; }
        .email-container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #ECE6D6; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.04); }
        .header { background: #1F3A27; color: #ffffff; padding: 32px 28px; text-align: center; }
        .header h1 { margin: 0 0 6px 0; font-family: Georgia, serif; font-size: 24px; color: #ffffff; }
        .header p { margin: 0; color: #E0A93B; font-size: 14px; font-weight: 500; }
        .content { padding: 28px; }
        .badge-rejected { display: inline-block; background-color: #FDF0EE; color: #C53030; font-weight: bold; font-size: 14px; padding: 6px 16px; border-radius: 20px; text-transform: uppercase; }
        .reason-box { background: #FFF9E6; border-left: 4px solid #E0A93B; border-radius: 8px; padding: 16px; margin: 20px 0; }
        .footer { background: #FAF7F0; text-align: center; padding: 20px; font-size: 12px; color: #6B756D; border-top: 1px solid #ECE6D6; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>Organic Dairy Gilgit-Baltistan</h1>
            <p>HIMALAYAN FARMER VERIFICATION</p>
        </div>

        <div class="content">
            <div style="text-align: center; margin-bottom: 20px;">
                <span class="badge-rejected">Application Needs Attention</span>
            </div>

            <h2 style="font-family: Georgia, serif; color: #1F3A27; margin-top: 0; text-align: center;">Farm Review Feedback</h2>
            <p style="font-size: 15px; line-height: 1.5; color: #1F2A22;">
                Dear <strong>{{ $seller->sellerProfile->farm_name ?? $seller->name }}</strong>,<br>
                Thank you for applying to sell on Organic Dairy. After reviewing your farm profile, our quality assurance team was unable to approve your application at this stage.
            </p>

            <div class="reason-box">
                <strong style="color: #9C6B08; display: block; margin-bottom: 6px;">Reason / Required Action:</strong>
                <p style="margin: 0; font-size: 14px; color: #2D3748;">{{ $reason }}</p>
            </div>

            <p style="font-size: 14px; line-height: 1.6; color: #4A5568;">
                You may update your farm profile details in your portal or contact customer support at <strong>salam@organicdairy.test</strong> if you have any questions or additional certifications to submit.
            </p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Organic Dairy Gilgit-Baltistan &bull; Sustainable Agriculture Network
        </div>
    </div>
</body>
</html>
