<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reset Password - Liosync POS</title>
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

        .reset-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 450px;
        }

        .reset-card {
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

        .password-strength {
            margin-top: 0.5rem;
            font-size: 0.75rem;
            color: #6b7280;
        }

        .strength-meter {
            height: 4px;
            background: #e5e7eb;
            border-radius: 2px;
            margin-top: 0.25rem;
            overflow: hidden;
        }

        .strength-meter-fill {
            height: 100%;
            width: 0;
            transition: width 0.3s, background-color 0.3s;
        }

        .strength-weak { background: #ef4444; width: 33%; }
        .strength-medium { background: #f59e0b; width: 66%; }
        .strength-strong { background: #10b981; width: 100%; }

        html.dark body {
            background: linear-gradient(135deg, #1e1b4b 0%, #7c2d12 50%, #c2410c 100%);
        }

        html.dark .reset-card {
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

    <div class="reset-container">
        <div class="reset-card">
            <!-- Logo Section -->
            <div class="logo-section">
                <img src="{{ asset('images/liosync-logo.png') }}" alt="Liosync POS" class="logo-icon">
            </div>

            <!-- Header Text -->
            <div class="header-text">
                <h2>Reset Password</h2>
                <p>Enter your new password below.</p>
            </div>

            <!-- Error Message -->
            @if($errors->any())
                <div class="error-message">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <!-- Form -->
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="form-group">
                    <label class="form-label" for="password">New Password</label>
                    <div class="input-wrapper">
                        <input class="form-input"
                            id="password"
                            name="password"
                            placeholder="Min. 8 characters"
                            type="password"
                            required
                            autocomplete="new-password">
                        <span class="material-icons-round">lock</span>
                        <span class="material-icons-round toggle-password" onclick="togglePassword('password', this)">
                            visibility
                        </span>
                    </div>
                    <div class="strength-meter">
                        <div class="strength-meter-fill" id="strengthMeter"></div>
                    </div>
                    <div class="password-strength" id="strengthText">Minimum 8 characters</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Confirm New Password</label>
                    <div class="input-wrapper">
                        <input class="form-input"
                            id="password_confirmation"
                            name="password_confirmation"
                            placeholder="Confirm your new password"
                            type="password"
                            required
                            autocomplete="new-password">
                        <span class="material-icons-round">lock</span>
                        <span class="material-icons-round toggle-password" onclick="togglePassword('password_confirmation', this)">
                            visibility
                        </span>
                    </div>
                </div>

                <button type="submit" class="submit-btn">
                    Reset Password
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

    <script>
        function togglePassword(inputId, iconElement) {
            const passwordInput = document.getElementById(inputId);

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                iconElement.textContent = 'visibility_off';
                iconElement.classList.add('active');
            } else {
                passwordInput.type = 'password';
                iconElement.textContent = 'visibility';
                iconElement.classList.remove('active');
            }
        }

        // Password strength checker
        const passwordInput = document.getElementById('password');
        const strengthMeter = document.getElementById('strengthMeter');
        const strengthText = document.getElementById('strengthText');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;

            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/\d/)) strength++;
            if (password.match(/[^a-zA-Z\d]/)) strength++;

            strengthMeter.className = 'strength-meter-fill';

            if (strength < 2) {
                strengthMeter.classList.add('strength-weak');
                strengthText.textContent = 'Weak password';
            } else if (strength < 4) {
                strengthMeter.classList.add('strength-medium');
                strengthText.textContent = 'Medium password';
            } else {
                strengthMeter.classList.add('strength-strong');
                strengthText.textContent = 'Strong password';
            }
        });
    </script>
</body>
</html>
