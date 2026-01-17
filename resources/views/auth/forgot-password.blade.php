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
            max-width: 450px;
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

        .status-message {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 1px solid #bbf7d0;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .status-message p {
            color: #166534;
            font-size: 0.875rem;
            margin: 0;
        }

        .error-message {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border: 1px solid #fecaca;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .error-message p {
            color: #991b1b;
            font-size: 0.875rem;
            margin: 0;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper .material-icons-round {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 20px;
            transition: color 0.3s;
        }

        .input-wrapper:focus-within .material-icons-round {
            color: #f97316;
        }

        .form-input {
            width: 100%;
            padding: 0.875rem 3rem 0.875rem 3rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 0.9375rem;
            color: #111827;
            background: white;
            transition: all 0.3s;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .form-input::placeholder {
            color: #9ca3af;
        }

        .form-input:focus {
            outline: none;
            border-color: #f97316;
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.1);
        }

        .submit-btn {
            width: 100%;
            padding: 0.875rem;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            font-family: 'Plus Jakarta Sans', sans-serif;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(249, 115, 22, 0.3);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(249, 115, 22, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
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

        html.dark .form-label {
            color: #e5e7eb;
        }

        html.dark .form-input {
            background: rgba(31, 41, 55, 0.8);
            border-color: #374151;
            color: #f9fafb;
        }

        html.dark .form-input::placeholder {
            color: #6b7280;
        }

        html.dark .form-input:focus {
            border-color: #f97316;
            background: rgba(31, 41, 55, 1);
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
                <p>Enter your email address and we'll send you a link to reset your password.</p>
            </div>

            <!-- Status Message -->
            @if(session('status'))
                <div class="status-message">
                    <p>{{ session('status') }}</p>
                </div>
            @endif

            <!-- Error Message -->
            @if($errors->any())
                <div class="error-message">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <!-- Form -->
            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <div class="input-wrapper">
                        <input class="form-input"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="Enter your email"
                            type="email"
                            required
                            autocomplete="email">
                        <span class="material-icons-round">email</span>
                    </div>
                </div>

                <button type="submit" class="submit-btn">
                    Send Password Reset Link
                </button>
            </form>

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
