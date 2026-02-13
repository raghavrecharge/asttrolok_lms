<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .email-container {
            background-color: #ffffff;
            max-width: 600px;
            margin: 0 auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #4a5568;
        }
        .otp-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 25px;
            text-align: center;
            font-size: 36px;
            font-weight: bold;
            color: #ffffff;
            letter-spacing: 8px;
            border-radius: 8px;
            margin: 25px 0;
        }
        h2 {
            color: #2d3748;
            margin-bottom: 15px;
        }
        p {
            color: #4a5568;
            line-height: 1.8;
            margin: 10px 0;
        }
        .warning {
            background-color: #fff5f5;
            border-left: 4px solid #f56565;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .warning p {
            color: #c53030;
            margin: 0;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            color: #a0aec0;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">🔮 Astrolok</div>
        </div>
        
        <h2>Namaste {{ $userName ?? 'User' }}!</h2>
        
        <p>Your One-Time Password (OTP) for secure login is:</p>
        
        <div class="otp-box">
            {{ $otp }}
        </div>
        
        <p style="text-align: center; font-size: 14px; color: #718096;">
            Valid for <strong>10 minutes</strong> only
        </p>
        
        <div class="warning">
            <p><strong>⚠️ Security Alert:</strong> Never share this OTP with anyone. Astrolok team will never ask for your OTP.</p>
        </div>
        
        <p>If you didn't request this OTP, please ignore this email or contact our support team.</p>
        
        <div class="footer">
            <p><strong>Astrolok Institute</strong></p>
            <p>Your trusted partner in astrology</p>
            <p>&copy; {{ date('Y') }} Astrolok. All rights reserved.</p>
        </div>
    </div>
</body>
</html>