<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Forgot Password - Liosync POS</title>
    <link rel="icon" type="image/png" href="{{ asset('images/liosync-logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 50%, #c2410c 100%);
            background-size: 200% 200%;
            animation: gradientShift 15s ease infinite;
            padding: 1rem;
            position: relative;
            overflow: hidden;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .floating-shapes {
            position: fixed;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.1;
            animation: float 20s infinite;
        }

        .shape-1 {
            width: 400px;
            height: 400px;
            background: white;
            top: -200px;
            left: -200px;
            animation-delay: 0s;
        }

        .shape-2 {
            width: 300px;
            height: 300px;
            background: white;
            bottom: -150px;
            right: -150px;
            animation-delay: 5s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(10deg); }
        }

        .forgot-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 500px;
        }

        .forgot-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-section {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .logo-icon {
            width: 100px;
            height: auto;
            margin: 0 auto 1rem;
        }

        .header-text {
            text-align: center;
            margin-bottom: 2rem;
        }

        .header-text h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 0.5rem;
        }

        .header-text p {
            color: #6b7280;
            font-size: 0.875rem;
        }

        .info-card {
            background: #fef3c7;
            border: 1px solid #fbbf24;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .info-card .icon {
            text-align: center;
            margin-bottom: 1rem;
        }

        .info-card .icon .material-icons-round {
            font-size: 48px;
            color: #f59e0b;
        }

        .info-card h3 {
            text-align: center;
            font-size: 1.125rem;
            font-weight: 600;
            color: #92400e;
            margin-bottom: 0.75rem;
        }

        .info-card p {
            color: #78350f;
            font-size: 0.875rem;
            line-height: 1.5;
        }

        .info-card ul {
            margin-top: 1rem;
            padding-left: 1.25rem;
        }

        .info-card li {
            color: #78350f;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }

        .contact-info {
            background: #f3f4f6;
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .contact-info p {
            color: #374151;
            font-size: 0.875rem;
            margin: 0.5rem 0;
        }

        .contact-info p strong {
            color: #111827;
        }

        .back-link {
            text-align: center;
            margin-top: 1.5rem;
        }

        .back-link a {
            color: #6b7280;
            text-decoration: none;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            transition: color 0.3s;
        }

        .back-link a:hover {
            color: #f97316;
        }

        html.dark body {
            background: linear-gradient(135deg, #1e1b4b 0%, #7c2d12 50%, #c2410c 100%);
        }

        html.dark .forgot-card {
            background: rgba(17, 24, 39, 0.95);
            border-color: rgba(55, 65, 81, 0.5);
        }
    </style>
</head>
<body>
    <!-- Floating Shapes -->
    <div class="floating-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
    </div>

    <div class="forgot-container">
        <div class="forgot-card">
            <!-- Logo Section -->
            <div class="logo-section">
                <img src="{{ asset('images/liosync-logo.png') }}" alt="Liosync POS" class="logo-icon">
            </div>

            <!-- Header Text -->
            <div class="header-text">
                <h2>Forgot Password?</h2>
                <p>Please contact your administrator to reset your password.</p>
            </div>

            <!-- Info Card -->
            <div class="info-card">
                <div class="icon">
                    <span class="material-icons-round">support_agent</span>
</div>
                <h3>Password Reset Procedure</h3>
                <p>To reset your password, please follow these steps:</p>
                <ul>
                    <li>Contact your company administrator</li>
                    <li>Request a password reset</li>
li>Administrator will reset your password</li>
                    <li>After reset, you can change your password in Profile/Settings</li>
                </ul>

                <div class="contact-info">
                    <p><strong>Why this process?</strong></p>
                    <p>This ensures security and prevents unauthorized access to your account.</p>
                </div>
            </div>

            <!-- Back to Login -->
            <div class="back-link">
                <a href="{{ route('login') }}">
                    <span class="material-icons-round" style="font-size: 18px;">arrow_back</span>
                    Back to Login
                </a>
            </div>
        </div>
    </div>
</body>
</html>
