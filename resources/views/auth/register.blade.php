<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register - Liosync POS</title>
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

        .register-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .register-card {
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
            width: 120px;
            height: auto;
            margin: 0 auto 1rem;
        }

        .logo-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #f97316;
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

        .success-message {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 1px solid #bbf7d0;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .success-message p {
            color: #166534;
            font-size: 0.875rem;
        }

        .form-section {
            margin-bottom: 1.5rem;
        }

        .form-section-title {
            font-size: 1rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f97316;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .required-indicator {
            color: #ef4444;
            font-weight: bold;
            font-size: 1rem;
            margin-left: 2px;
        }

        .optional-indicator {
            color: #9ca3af;
            font-size: 0.75rem;
            font-weight: 400;
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

        .form-input:focus + .material-icons-round,
        .input-wrapper:focus-within .material-icons-round {
            color: #f97316;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-select {
            width: 100%;
            padding: 0.875rem 3rem 0.875rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 0.9375rem;
            color: #111827;
            background: white;
            transition: all 0.3s;
            font-family: 'Plus Jakarta Sans', sans-serif;
            cursor: pointer;
        }

        .form-select:focus {
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
            position: relative;
            overflow: hidden;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(249, 115, 22, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
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

        /* Dark mode */
        html.dark body {
            background: linear-gradient(135deg, #1e1b4b 0%, #7c2d12 50%, #c2410c 100%);
        }

        html.dark .register-card {
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

        .dark-mode-toggle:hover {
            transform: scale(1.1);
        }

        .dark-mode-toggle .material-icons-round {
            font-size: 24px;
            color: #6b7280;
        }

        html.dark .dark-mode-toggle .material-icons-round {
            color: #fbbf24;
        }

        @media (max-width: 480px) {
            .register-card {
                padding: 1.5rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .logo-icon {
                width: 100px;
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
        <span class="material-icons-round">dark_mode</span>
    </button>

    <div class="register-container">
        <div class="register-card">
            <!-- Logo Section -->
            <div class="logo-section">
                <img src="{{ asset('images/liosync-logo.png') }}" alt="Liosync POS" class="logo-icon">
                <p class="logo-subtitle">Point of Sale System</p>
            </div>

            <!-- Welcome Text -->
            <div class="welcome-text">
                <h2>Create Company Account</h2>
                <p>Register your company to get started</p>
                <p style="font-size: 0.75rem; margin-top: 0.5rem;">You can add branches after registration</p>
                <div style="display: flex; justify-content: center; gap: 1.5rem; margin-top: 1rem; font-size: 0.75rem;">
                    <span><span style="color: #ef4444; font-weight: bold;">*</span> Required</span>
                    <span style="color: #9ca3af;">(Optional)</span>
                </div>
            </div>

            <!-- Error Message -->
            @if($errors->any())
                <div class="error-message">
                    <span class="material-icons-round icon">error_outline</span>
                    <h4>Registration Failed</h4>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Registration Form -->
            <form method="POST" action="{{ route('register.process') }}">
                @csrf

                <!-- Company Information Section -->
                <div class="form-section">
                    <div class="form-section-title">Company Information</div>

                    <div class="form-group">
                        <label class="form-label" for="company_name">
                            Company Name
                            <span class="required-indicator">*</span>
                        </label>
                        <div class="input-wrapper">
                            <input class="form-input"
                                id="company_name"
                                name="company_name"
                                value="{{ old('company_name') }}"
                                placeholder="Enter company name"
                                type="text"
                                required>
                            <span class="material-icons-round">business</span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="company_code">
                                Company Code
                                <span class="optional-indicator">(Optional)</span>
                            </label>
                            <div class="input-wrapper">
                                <input class="form-input"
                                    id="company_code"
                                    name="company_code"
                                    value="{{ old('company_code') }}"
                                    placeholder="AUTO (optional)"
                                    type="text"
                                    maxlength="50">
                                <span class="material-icons-round">tag</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="company_type">
                                Business Type
                                <span class="required-indicator">*</span>
                            </label>
                            <select class="form-select" id="company_type" name="company_type" required>
                                <option value="">Select type</option>
                                <option value="resto" {{ old('company_type') == 'resto' ? 'selected' : '' }}>Restaurant / Cafe</option>
                                <option value="toko" {{ old('company_type') == 'toko' ? 'selected' : '' }}>Retail Store</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="company_structure">
                            Company Structure
                            <span class="required-indicator">*</span>
                        </label>
                        <select class="form-select" id="company_structure" name="company_structure" required>
                            <option value="">Select structure</option>
                            <option value="multi" {{ old('company_structure') == 'multi' ? 'selected' : '' }}>Multi-Branch (Multiple locations)</option>
                            <option value="single" {{ old('company_structure') == 'single' ? 'selected' : '' }}>Single-Branch (One location)</option>
                        </select>
                        <small style="color: #6b7280; font-size: 0.75rem;">Multi-Branch: Manage multiple branches. Single-Branch: One location only.</small>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="company_phone">
                                Phone Number
                                <span class="optional-indicator">(Optional)</span>
                            </label>
                            <div class="input-wrapper">
                                <input class="form-input"
                                    id="company_phone"
                                    name="company_phone"
                                    value="{{ old('company_phone') }}"
                                    placeholder="08xxxxxxxxxx"
                                    type="tel">
                                <span class="material-icons-round">phone</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="tax_id">
                                Tax ID (NPWP)
                                <span class="optional-indicator">(Optional)</span>
                            </label>
                            <div class="input-wrapper">
                                <input class="form-input"
                                    id="tax_id"
                                    name="tax_id"
                                    value="{{ old('tax_id') }}"
                                    placeholder="Optional"
                                    type="text"
                                    maxlength="100">
                                <span class="material-icons-round">receipt</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="company_address">
                            Address
                            <span class="optional-indicator">(Optional)</span>
                        </label>
                        <div class="input-wrapper">
                            <input class="form-input"
                                id="company_address"
                                name="company_address"
                                value="{{ old('company_address') }}"
                                placeholder="Company address"
                                type="text">
                            <span class="material-icons-round">location_on</span>
                        </div>
                    </div>
                </div>

                <!-- Admin Account Section -->
                <div class="form-section">
                    <div class="form-section-title">Admin Account</div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="admin_name">
                                Full Name
                                <span class="required-indicator">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input class="form-input"
                                    id="admin_name"
                                    name="admin_name"
                                    value="{{ old('admin_name') }}"
                                    placeholder="Admin full name"
                                    type="text"
                                    required
                                    autocomplete="name">
                                <span class="material-icons-round">person</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="admin_email">
                                Email
                                <span class="required-indicator">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input class="form-input"
                                    id="admin_email"
                                    name="admin_email"
                                    value="{{ old('admin_email') }}"
                                    placeholder="admin@example.com"
                                    type="email"
                                    required
                                    autocomplete="email">
                                <span class="material-icons-round">email</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="admin_password">
                                Password
                                <span class="required-indicator">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input class="form-input"
                                    id="admin_password"
                                    name="admin_password"
                                    placeholder="Min. 8 characters"
                                    type="password"
                                    required
                                    autocomplete="new-password">
                                <span class="material-icons-round">lock</span>
                                <span class="material-icons-round toggle-password" onclick="togglePassword('admin_password', this)">
                                    visibility
                                </span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="admin_phone">
                                Admin Phone
                                <span class="optional-indicator">(Optional)</span>
                            </label>
                            <div class="input-wrapper">
                                <input class="form-input"
                                    id="admin_phone"
                                    name="admin_phone"
                                    value="{{ old('admin_phone') }}"
                                    placeholder="08xxxxxxxxxx"
                                    type="tel"
                                    autocomplete="tel">
                                <span class="material-icons-round">smartphone</span>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="submit-btn" id="submitBtn">
                    Create Account
                </button>
            </form>

            <!-- Footer -->
            <div class="footer-text">
                <p>Already have an account? <a href="{{ route('login') }}">Sign In</a></p>
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

        // Auto-generate company code from company name if empty
        document.getElementById('company_name').addEventListener('blur', function() {
            const companyCode = document.getElementById('company_code');

            // Auto-generate company code if empty
            if (!companyCode.value) {
                const companyName = this.value.trim();
                if (companyName.length > 0) {
                    const code = companyName.substring(0, 3).toUpperCase() + Math.floor(100 + Math.random() * 900);
                    companyCode.value = code;
                }
            }
        });

        // Prevent double submission
        document.getElementById('submitBtn').addEventListener('click', function(e) {
            const form = this.closest('form');
            if (form.checkValidity()) {
                this.disabled = true;
                this.innerHTML = 'Creating Account...';
            }
        });
    </script>
</body>
</html>
