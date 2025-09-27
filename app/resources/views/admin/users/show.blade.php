<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details - Admin</title>
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
                    <a href="{{ route('admin.users.index') }}" class="text-indigo-600 font-semibold">Users</a>
                    <a href="{{ route('admin.payments.index') }}" class="text-gray-600 hover:text-gray-900">Payments</a>
                    <a href="{{ route('admin.pricing.index') }}" class="text-gray-600 hover:text-gray-900">Pricing</a>
                    <span class="text-gray-700">{{ Auth::user()->name }}</span>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('admin.users.index') }}" class="text-indigo-600 hover:text-indigo-500 text-sm font-medium">← Back to Users</a>
            <h2 class="text-2xl font-bold text-gray-900 mt-2">User Details</h2>
        </div>

        <!-- User Info -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex items-center mb-6">
                <div class="flex-shrink-0 h-16 w-16">
                    <div class="h-16 w-16 rounded-full bg-gray-300 flex items-center justify-center">
                        <span class="text-2xl font-medium text-gray-700">{{ substr($user->name, 0, 1) }}</span>
                    </div>
                </div>
                <div class="ml-6">
                    <h3 class="text-xl font-semibold text-gray-900">{{ $user->name }}</h3>
                    <p class="text-gray-600">{{ $user->email }}</p>
                    <div class="mt-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            {{ $user->role === 'superadmin' ? 'bg-red-100 text-red-800' : 
                               ($user->role === 'admin' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <h4 class="text-sm font-medium text-gray-500 mb-2">Account Status</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Email Verified:</span>
                            <span class="text-sm font-medium {{ $user->email_verified_at ? 'text-green-600' : 'text-red-600' }}">
                                {{ $user->email_verified_at ? 'Yes' : 'No' }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Joined:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $user->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Last Login:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $user->updated_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-gray-500 mb-2">Test Usage</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Free Tests Used:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $user->free_tests_used }}/1</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Total Tests:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $user->speakingTests->count() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Certificates:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $user->certificates->count() }}</span>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-gray-500 mb-2">Payment Status</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Has Waiver:</span>
                            <span class="text-sm font-medium {{ $user->hasActiveWaiver() ? 'text-green-600' : 'text-gray-600' }}">
                                {{ $user->hasActiveWaiver() ? 'Yes' : 'No' }}
                            </span>
                        </div>
                        @if($user->hasActiveWaiver())
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Discount:</span>
                            <span class="text-sm font-medium text-green-600">{{ $user->custom_discount_percentage ?? 100 }}%</span>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Active Subscriptions:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $user->subscriptions->where('status', 'active')->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Tests -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Recent Tests</h3>
            </div>
            <div class="p-6">
                @if($user->speakingTests->count() > 0)
                <div class="space-y-4">
                    @foreach($user->speakingTests->take(5) as $test)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <div class="text-sm font-medium text-gray-900">
                                Test #{{ $test->id }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $test->created_at->format('M d, Y H:i') }}
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $test->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                   ($test->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ ucfirst($test->status) }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-gray-500 text-center py-4">No tests taken yet</p>
                @endif
            </div>
        </div>

        <!-- Payments -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Payment History</h3>
            </div>
            <div class="p-6">
                @if($user->payments->count() > 0)
                <div class="space-y-4">
                    @foreach($user->payments->take(5) as $payment)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <div class="text-sm font-medium text-gray-900">
                                ${{ number_format($payment->amount, 2) }} {{ strtoupper($payment->currency) }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $payment->description }}
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $payment->status === 'succeeded' ? 'bg-green-100 text-green-800' : 
                                   ($payment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ $payment->created_at->format('M d, Y') }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-gray-500 text-center py-4">No payments made yet</p>
                @endif
            </div>
        </div>

        <!-- Certificates -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Certificates</h3>
            </div>
            <div class="p-6">
                @if($user->certificates->count() > 0)
                <div class="space-y-4">
                    @foreach($user->certificates->take(5) as $certificate)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <div class="text-sm font-medium text-gray-900">
                                Certificate #{{ $certificate->certificate_number }}
                            </div>
                            <div class="text-sm text-gray-500">
                                Score: {{ $certificate->score_achieved }}% ({{ $certificate->grade }})
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $certificate->is_valid ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $certificate->is_valid ? 'Valid' : 'Invalid' }}
                            </span>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ $certificate->issued_at->format('M d, Y') }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-gray-500 text-center py-4">No certificates earned yet</p>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
