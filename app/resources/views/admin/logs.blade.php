<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Logs - Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-semibold text-gray-900">Admin Dashboard</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.dashboard') }}" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                    <a href="{{ route('admin.users.index') }}" class="text-gray-600 hover:text-gray-900">Users</a>
                    <a href="{{ route('admin.payments.index') }}" class="text-gray-600 hover:text-gray-900">Payments</a>
                    <a href="{{ route('admin.pricing.index') }}" class="text-gray-600 hover:text-gray-900">Pricing</a>
                    <span class="text-gray-700">{{ Auth::user()->name }}</span>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900">System Logs</h2>
            <p class="text-gray-600">View application logs and system activity</p>
        </div>

        <!-- Logs Container -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">Laravel Log</h3>
                    <button onclick="refreshLogs()" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors text-sm">
                        Refresh
                    </button>
                </div>
            </div>
            <div class="p-6">
                @if(!empty($logs))
                <div class="bg-gray-900 text-green-400 p-4 rounded-lg font-mono text-sm overflow-auto max-h-96">
                    @foreach($logs as $log)
                    @if(!empty(trim($log)))
                    <div class="mb-1">{{ $log }}</div>
                    @endif
                    @endforeach
                </div>
                @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No logs available</h3>
                    <p class="mt-1 text-sm text-gray-500">Log file is empty or doesn't exist.</p>
                </div>
                @endif
            </div>
        </div>

        <!-- System Information -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">System Info</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">PHP Version:</span>
                        <span class="text-sm font-medium text-gray-900">{{ PHP_VERSION }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Laravel Version:</span>
                        <span class="text-sm font-medium text-gray-900">{{ app()->version() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Environment:</span>
                        <span class="text-sm font-medium text-gray-900">{{ app()->environment() }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Database</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Driver:</span>
                        <span class="text-sm font-medium text-gray-900">{{ config('database.default') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Host:</span>
                        <span class="text-sm font-medium text-gray-900">{{ config('database.connections.'.config('database.default').'.host') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Database:</span>
                        <span class="text-sm font-medium text-gray-900">{{ config('database.connections.'.config('database.default').'.database') }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Cache & Sessions</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Cache Driver:</span>
                        <span class="text-sm font-medium text-gray-900">{{ config('cache.default') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Session Driver:</span>
                        <span class="text-sm font-medium text-gray-900">{{ config('session.driver') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Queue Driver:</span>
                        <span class="text-sm font-medium text-gray-900">{{ config('queue.default') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function refreshLogs() {
            location.reload();
        }
    </script>
</body>
</html>
