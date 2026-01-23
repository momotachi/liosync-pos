<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - Liosync POS</title>
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

        /* Floating shapes */
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

        .shape-3 {
            width: 200px;
            height: 200px;
            background: white;
            top: 50%;
            right: 10%;
            animation-delay: 10s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(10deg); }
        }

        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 2.5rem;
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
            margin-bottom: 2rem;
        }

        .logo-icon {
            width: 320px;
            height: auto;
            margin: 0 auto 1rem;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }

        .logo-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #f97316;
            letter-spacing: -0.5px;
            margin-top: 0.5rem;
        }

        .logo-subtitle {
            color: #6b7280;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .welcome-text {
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .welcome-text h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 0.25rem;
        }

        .welcome-text p {
            color: #6b7280;
            font-size: 0.875rem;
        }

        .error-message {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border: 1px solid #fecaca;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .error-message .icon {
            color: #ef4444;
            font-size: 20px;
            vertical-align: middle;
            margin-right: 0.5rem;
        }

        .error-message h4 {
            color: #991b1b;
            font-size: 0.875rem;
            font-weight: 600;
            display: inline;
        }

        .error-message ul {
            color: #b91c1c;
            font-size: 0.75rem;
            margin-top: 0.5rem;
            padding-left: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
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

        .input-wrapper .toggle-password {
            position: absolute;
            left: auto;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 20px;
            cursor: pointer;
            transition: color 0.3s;
            padding: 4px;
        }

        .input-wrapper .toggle-password:hover {
            color: #f97316;
        }

        .input-wrapper .toggle-password.active {
            color: #f97316;
        }

        .form-input {
            width: 100%;
            padding: 0.875rem 4rem 0.875rem 3rem;
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

        .form-input:focus + .material-icons-round,
        .input-wrapper:focus-within .material-icons-round {
            color: #f97316;
        }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #f97316;
            cursor: pointer;
        }

        .remember-me label {
            font-size: 0.875rem;
            color: #6b7280;
            cursor: pointer;
            user-select: none;
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
            position: relative;
            overflow: hidden;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(249, 115, 22, 0.4);
        }

        .submit-btn:hover::before {
            left: 100%;
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .footer-text {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
        }

        .footer-text p {
            color: #6b7280;
            font-size: 0.813rem;
        }

        .footer-text a {
            color: #f97316;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .footer-text a:hover {
            color: #ea580c;
            text-decoration: underline;
        }

        .version-text {
            color: #9ca3af;
            font-size: 0.75rem;
            margin-top: 0.5rem;
        }

        /* Dark mode */
        html.dark body {
            background: linear-gradient(135deg, #1e1b4b 0%, #7c2d12 50%, #c2410c 100%);
        }

        html.dark .login-card {
            background: rgba(17, 24, 39, 0.95);
            border-color: rgba(55, 65, 81, 0.5);
        }

        html.dark .logo-subtitle,
        html.dark .welcome-text p {
            color: #9ca3af;
        }

        html.dark .welcome-text h2 {
            color: #f9fafb;
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

        html.dark .remember-me label {
            color: #9ca3af;
        }

        html.dark .footer-text {
            border-top-color: #374151;
        }

        html.dark .footer-text p {
            color: #9ca3af;
        }

        /* Dark mode toggle */
        .dark-mode-toggle {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            z-index: 100;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 50%;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            border: none;
        }

        html.dark .dark-mode-toggle {
            background: rgba(17, 24, 39, 0.9);
        }

        .dark-mode-toggle:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .dark-mode-toggle .material-icons-round {
            font-size: 24px;
            color: #6b7280;
        }

        .dark-mode-toggle:hover .material-icons-round {
            color: #f97316;
        }

        html.dark .dark-mode-toggle .material-icons-round {
            color: #fbbf24;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 1.5rem;
            }

            .logo-icon {
                width: 260px;
            }

            .welcome-text h2 {
                font-size: 1.25rem;
            }
        }

        @media (min-width: 481px) and (max-width: 768px) {
            .logo-icon {
                width: 290px;
            }
        }

        @media (min-width: 769px) {
            .logo-icon {
                width: 320px;
            }
        }
    </style>
</head>
<body>
    <!-- Floating Shapes -->
    <div class="floating-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>

    <!-- Dark Mode Toggle -->
    <button class="dark-mode-toggle" onclick="document.documentElement.classList.toggle('dark')">
        <span class="material-icons-round dark:hidden">dark_mode</span>
        <span class="material-icons-round hidden dark:block">light_mode</span>
    </button>

    <div class="login-container">
        <div class="login-card">
            <!-- Logo Section -->
            <div class="logo-section">
                <img src="{{ asset('images/liosync-login-logo.png') }}" alt="Liosync POS" class="logo-icon">
            </div>

            <!-- Welcome Text -->
            <div class="welcome-text">
                <h2>Welcome Back!</h2>
                <p>Sign in to access your account</p>
            </div>

            <!-- Error Message -->
            @if($errors->any())
                <div class="error-message">
                    <span class="material-icons-round icon">error_outline</span>
                    <h4>Login Failed</h4>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('login.process') }}">
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

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-wrapper">
                        <input class="form-input"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            type="password"
                            required
                            autocomplete="current-password">
                        <span class="material-icons-round">lock</span>
                        <span class="material-icons-round toggle-password" id="togglePassword" onclick="togglePasswordVisibility()">
                            visibility
                        </span>
                    </div>
                </div>

                <div class="form-options">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me</label>
                    </div>
                </div>

                <button type="submit" class="submit-btn">
                    Sign In
                </button>
            </form>

            <!-- Footer -->
            <div class="footer-text">
                <p>Don't have an account? <a href="{{ route('register') }}">Sign Up</a></p>
                <p>Forgot your password? <a href="{{ route('password.request') }}">Reset here</a></p>
                <p class="version-text">Liosync POS v1.0.0</p>
            </div>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('togglePassword');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.textContent = 'visibility_off';
                toggleIcon.classList.add('active');
            } else {
                passwordInput.type = 'password';
                toggleIcon.textContent = 'visibility';
                toggleIcon.classList.remove('active');
            }
        }
    </script>
</body>
</html>
