<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center">
    <div class="w-full max-w-sm bg-white rounded-2xl shadow-sm p-8">
        <h1 class="text-2xl font-semibold text-gray-900 mb-6">Sign in</h1>

        @if ($errors->any())
            <p class="text-sm text-red-600 mb-4">{{ $errors->first() }}</p>
        @endif

        <form method="POST" action="/login" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input
                    id="email" name="email" type="email" autocomplete="email" required
                    value="{{ old('email') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input
                    id="password" name="password" type="password" autocomplete="current-password" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>
            <div class="flex items-center gap-2">
                <input id="remember" name="remember" type="checkbox" class="rounded">
                <label for="remember" class="text-sm text-gray-600">Remember me</label>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-blue-700 transition-colors">
                Sign in
            </button>
        </form>
    </div>
</body>
</html>
