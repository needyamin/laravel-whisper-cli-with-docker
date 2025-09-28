<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\SpeechController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\SpeakingTestController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AdminController;

// Home page
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Payment routes
    Route::get('/payment', [PaymentController::class, 'index'])->name('payment.index');
    Route::post('/payment/create-intent', [PaymentController::class, 'createPaymentIntent'])->name('payment.create-intent');
    Route::post('/payment/success', [PaymentController::class, 'handleSuccessfulPayment'])->name('payment.success');
    Route::get('/payment/history', [PaymentController::class, 'history'])->name('payment.history');
    
    // Test routes (with payment check)
    Route::get('/test', [SpeakingTestController::class, 'showTest'])->name('test.show')->middleware('check.payment');
    Route::post('/test/start', [SpeakingTestController::class, 'startTest'])->name('test.start')->middleware('check.payment');
    Route::post('/test/submit', [SpeakingTestController::class, 'submitTest'])->name('test.submit')->middleware('check.payment');
    Route::get('/test/results/{testId}', [SpeakingTestController::class, 'getResults'])->name('test.results')->middleware('check.payment');
    
    // Test history and certificates
    Route::get('/test-history', [DashboardController::class, 'testHistory'])->name('test.history');
    Route::get('/certificates', [DashboardController::class, 'certificates'])->name('certificates');
    Route::get('/certificate/{certificateId}/download', [DashboardController::class, 'downloadCertificate'])->name('certificate.download');
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    
    // User management
    Route::get('/users', [AdminController::class, 'users'])->name('users.index');
    Route::get('/users/{user}', [AdminController::class, 'showUser'])->name('users.show');
    Route::patch('/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('users.update-role');
    Route::post('/users/{user}/waiver', [AdminController::class, 'grantWaiver'])->name('users.grant-waiver');
    Route::delete('/users/{user}/waiver', [AdminController::class, 'revokeWaiver'])->name('users.revoke-waiver');
    Route::post('/users/{user}/reset-free-tests', [AdminController::class, 'resetFreeTests'])->name('users.reset-free-tests');
    
    // Payment management
    Route::get('/payments', [AdminController::class, 'payments'])->name('payments.index');
    Route::get('/subscriptions', [AdminController::class, 'subscriptions'])->name('subscriptions.index');
    
    // Pricing plans management
    Route::get('/pricing', [AdminController::class, 'pricingPlans'])->name('pricing.index');
    Route::post('/pricing', [AdminController::class, 'createPricingPlan'])->name('pricing.store');
    Route::patch('/pricing/{plan}', [AdminController::class, 'updatePricingPlan'])->name('pricing.update');
    Route::delete('/pricing/{plan}', [AdminController::class, 'deletePricingPlan'])->name('pricing.destroy');
    
    // System logs
    Route::get('/logs', [AdminController::class, 'logs'])->name('logs');
});

// Stripe webhook (no auth required)
Route::post('/stripe/webhook', [PaymentController::class, 'webhook'])->name('stripe.webhook');

// Debug route for testing
Route::get('/debug/test-submit', function() {
    return response()->json([
        'message' => 'Debug route working',
        'user' => Auth::check() ? Auth::user()->name : 'Not authenticated',
        'csrf_token' => csrf_token()
    ]);
})->middleware('auth');

// Simple debug route (no auth required)
Route::post('/debug/simple', function(Request $request) {
    return response()->json([
        'message' => 'Simple debug route working',
        'method' => $request->method(),
        'test_id' => $request->test_id ?? 'not provided',
        'timestamp' => now()->toISOString()
    ]);
});

// Debug route for test start
Route::post('/debug/test-start', function(Request $request) {
    try {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'error' => 'Not authenticated',
                'message' => 'User not logged in'
            ], 401);
        }
        
        return response()->json([
            'message' => 'Debug test start route working',
            'user' => $user->name,
            'user_id' => $user->id,
            'can_take_test' => $user->canTakeTest(),
            'has_waiver' => $user->hasActiveWaiver(),
            'free_tests_used' => $user->free_tests_used,
            'test_id' => $request->test_id ?? 'not provided',
            'csrf_token' => csrf_token()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Debug route error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
})->middleware('auth');





// Test route for certificates view
Route::get('/test-certificates', function() {
    $user = Auth::user();
    $certificates = $user->certificates()
        ->with('test.paragraph')
        ->where('is_valid', true)
        ->latest()
        ->get();
    return view('certificates.index', compact('certificates'));
})->middleware('auth');
