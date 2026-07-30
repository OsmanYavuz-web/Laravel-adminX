<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Welcome to :app', ['app' => config('app.name')]) }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #374151; margin: 0; padding: 0; background-color: #f3f4f6; }
        .wrapper { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #6366f1, #4f46e5); color: #fff; padding: 32px 24px; border-radius: 12px 12px 0 0; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; }
        .body { background: #fff; padding: 32px 24px; border-radius: 0 0 12px 12px; }
        .footer { text-align: center; padding: 24px; color: #9ca3af; font-size: 12px; }
        .btn { display: inline-block; padding: 12px 24px; background: #6366f1; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; margin: 16px 0; }
        .text-center { text-align: center; }
        .text-muted { color: #6b7280; font-size: 14px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header"><h1>{{ __('Welcome!') }}</h1></div>
        <div class="body">
            <p>{{ __('Hello :name,', ['name' => $name]) }}</p>
            <p>{{ __('Welcome to :app! Your account has been created successfully.', ['app' => config('app.name')]) }}</p>
            <p>{{ __('You can now log in with your registered email address and password.') }}</p>
            <div class="text-center">
                <a href="{{ route('login') }}" class="btn">{{ __('Log In') }}</a>
            </div>
            <p class="text-muted">{{ __('If you did not create this account, please ignore this email.') }}</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}</p>
        </div>
    </div>
</body>
</html>
