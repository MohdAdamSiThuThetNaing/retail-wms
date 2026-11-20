<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Retail WMS</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            background: #f4f5fb;
            font-family: 'DM Sans', sans-serif;
        }

        .login-card {
            width: min(420px, calc(100% - 40px));
            padding: 36px;
            background: #fff;
            border: 1px solid #e8eaf0;
            border-radius: 16px;
            box-shadow: 0 15px 45px rgba(20, 30, 60, .08);
            box-sizing: border-box;
        }

        .login-logo {
            width: 48px;
            height: 48px;
            display: grid;
            place-items: center;
            border-radius: 13px;
            background: #ede9fe;
            color: #6d4aff;
            font-size: 24px;
            margin-bottom: 22px;
        }

        .login-card h1 {
            margin: 0 0 8px;
            font-size: 25px;
            color: #172033;
        }

        .login-card p {
            margin: 0 0 26px;
            color: #718096;
            font-size: 14px;
        }

        .login-field {
            margin-bottom: 18px;
        }

        .login-field label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #344054;
        }

        .login-field input {
            width: 100%;
            box-sizing: border-box;
            padding: 12px 14px;
            border: 1px solid #dfe3eb;
            border-radius: 9px;
            font-size: 14px;
        }

        .login-field input:focus {
            outline: none;
            border-color: #7655e8;
            box-shadow: 0 0 0 3px #ede9fe;
        }

        .login-button {
            width: 100%;
            padding: 13px;
            border: 0;
            border-radius: 9px;
            background: #7655e8;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .login-button:hover {
            background: #6241d5;
        }

        .login-error {
            padding: 12px;
            margin-bottom: 18px;
            border-radius: 8px;
            background: #fef2f2;
            color: #b42318;
            font-size: 13px;
        }
    </style>
</head>
<body>

    <div class="login-card">

        <h1>Welcome Back</h1>
        <p>Sign in to your Retail WMS administrator account.</p>

        @if($errors->any())
            <div class="login-error">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}">
            @csrf

            <div class="login-field">
                <label for="email">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="Enter your email"
                    required
                    autofocus
                >
            </div>

            <div class="login-field">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter your password"
                    required
                >
            </div>

            <button type="submit" class="login-button">
                Sign In
            </button>
        </form>
    </div>

</body>
</html>
