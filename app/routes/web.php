<?php

use Illuminate\Support\Facades\Route;
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
    Route::post('/test/start', [SpeakingTestController::class, 'startTest'])->name('test.start');
    Route::post('/test/submit', [SpeakingTestController::class, 'submitTest'])->name('test.submit');
    Route::get('/test/results/{testId}', [SpeakingTestController::class, 'getResults'])->name('test.results');
    
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
