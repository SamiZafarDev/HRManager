<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Request</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .logo {
            max-width: 150px;
        }
        .content {
            padding: 20px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #4CAF50;
            color: white !important;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            margin: 15px 0;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #777;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        .code {
            font-family: monospace;
            font-size: 18px;
            letter-spacing: 2px;
            background: #f5f5f5;
            padding: 10px;
            display: inline-block;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="https://yourcompany.com/logo.png" alt="Company Logo" class="logo">
    </div>

    <div class="content">
        <h2>Forgot Your Password?</h2>
        <p>Hello,</p>
        <p>We received a request to reset your password for your account at <strong>Koderspedia</strong>.</p>

        <!-- Option 1: Reset Link (Recommended for Laravel) -->
        <p>Click the button below to reset your password:</p>
        <a href="{reset_link}" class="button">Reset Password</a>
        {{-- <p>This link will expire in {expiry_time}.</p> --}}

        <!-- Option 2: OTP Code (Alternative) -->
        <!-- <p>Your one-time password reset code is:</p>
        <div class="code">{otp_code}</div>
        <p>This code will expire in {expiry_time}.</p> -->

        <p>If you didn't request this, please ignore this email or contact support.</p>
    </div>

    <div class="footer">
        {{-- <p>© {year} {Company Name}. All rights reserved.</p>
        <p>
            <a href="{privacy_policy_link}">Privacy Policy</a> |
            <a href="{contact_link}">Contact Us</a>
        </p> --}}
        <p><em>For security reasons, never share this email or the reset link with anyone.</em></p>
    </div>
</body>
</html>
