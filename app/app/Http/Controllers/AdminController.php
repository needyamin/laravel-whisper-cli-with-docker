<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SpeakingTest;
use App\Models\PricingPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Show admin dashboard.
     */
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_tests' => SpeakingTest::count(),
            'total_payments' => Payment::successful()->sum('amount'),
            'active_subscriptions' => Subscription::active()->count(),
            'recent_users' => User::latest()->take(5)->get(),
            'recent_payments' => Payment::successful()->with('user')->latest()->take(5)->get(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    /**
     * Show all users.
     */
    public function users(Request $request)
    {
        $query = User::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->with(['payments', 'subscriptions', 'speakingTests'])
                      ->latest()
                      ->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show user details.
     */
    public function showUser(User $user)
    {
        $user->load(['payments', 'subscriptions.pricingPlan', 'speakingTests.paragraph', 'certificates']);
        
        return view('admin.users.show', compact('user'));
    }

    /**
     * Update user role.
     */
    public function updateUserRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:user,admin,superadmin'
        ]);

        // Prevent demoting superadmin
        if ($user->isSuperAdmin() && $request->role !== 'superadmin' && !Auth::user()->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot change superadmin role.'
            ], 403);
        }

        $user->update(['role' => $request->role]);

        Log::info('User role updated', [
            'user_id' => $user->id,
            'new_role' => $request->role,
            'updated_by' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User role updated successfully.'
        ]);
    }

    /**
     * Grant payment waiver to user.
     */
    public function grantWaiver(Request $request, User $user)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
            'expires_at' => 'nullable|date|after:now',
            'discount_percentage' => 'nullable|numeric|min:0|max:100'
        ]);

        $user->update([
            'has_payment_waiver' => true,
            'waiver_reason' => $request->reason,
            'waiver_expires_at' => $request->expires_at,
            'custom_discount_percentage' => $request->discount_percentage ?? 100,
        ]);

        Log::info('Payment waiver granted', [
            'user_id' => $user->id,
            'reason' => $request->reason,
            'expires_at' => $request->expires_at,
            'discount_percentage' => $request->discount_percentage,
            'granted_by' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment waiver granted successfully.'
        ]);
    }

    /**
     * Revoke payment waiver from user.
     */
    public function revokeWaiver(User $user)
    {
        $user->update([
            'has_payment_waiver' => false,
            'waiver_reason' => null,
            'waiver_expires_at' => null,
            'custom_discount_percentage' => null,
        ]);

        Log::info('Payment waiver revoked', [
            'user_id' => $user->id,
            'revoked_by' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment waiver revoked successfully.'
        ]);
    }

    /**
     * Reset user's free test usage.
     */
    public function resetFreeTests(User $user)
    {
        $user->update(['free_tests_used' => 0]);

        Log::info('Free tests reset', [
            'user_id' => $user->id,
            'reset_by' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Free tests reset successfully.'
        ]);
    }

    /**
     * Show payments.
     */
    public function payments(Request $request)
    {
        $query = Payment::with('user');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $payments = $query->latest()->paginate(20);

        return view('admin.payments.index', compact('payments'));
    }

    /**
     * Show subscriptions.
     */
    public function subscriptions(Request $request)
    {
        $query = Subscription::with(['user', 'pricingPlan']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $subscriptions = $query->latest()->paginate(20);

        return view('admin.subscriptions.index', compact('subscriptions'));
    }

    /**
     * Show pricing plans management.
     */
    public function pricingPlans()
    {
        $plans = PricingPlan::latest()->get();
        return view('admin.pricing.index', compact('plans'));
    }

    /**
     * Create new pricing plan.
     */
    public function createPricingPlan(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:pricing_plans',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'test_limit' => 'required|integer|min:1',
            'validity_days' => 'required|integer|min:1',
            'is_popular' => 'boolean',
            'is_active' => 'boolean',
            'features' => 'nullable|array',
            'stripe_price_id' => 'nullable|string',
        ]);

        $plan = PricingPlan::create($request->all());

        Log::info('Pricing plan created', [
            'plan_id' => $plan->id,
            'plan_name' => $plan->name,
            'created_by' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pricing plan created successfully.',
            'plan' => $plan
        ]);
    }

    /**
     * Update pricing plan.
     */
    public function updatePricingPlan(Request $request, PricingPlan $plan)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:pricing_plans,slug,' . $plan->id,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'test_limit' => 'required|integer|min:1',
            'validity_days' => 'required|integer|min:1',
            'is_popular' => 'boolean',
            'is_active' => 'boolean',
            'features' => 'nullable|array',
            'stripe_price_id' => 'nullable|string',
        ]);

        $plan->update($request->all());

        Log::info('Pricing plan updated', [
            'plan_id' => $plan->id,
            'plan_name' => $plan->name,
            'updated_by' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pricing plan updated successfully.',
            'plan' => $plan
        ]);
    }

    /**
     * Delete pricing plan.
     */
    public function deletePricingPlan(PricingPlan $plan)
    {
        // Check if plan has active subscriptions
        if ($plan->subscriptions()->active()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete plan with active subscriptions.'
            ], 400);
        }

        $plan->delete();

        Log::info('Pricing plan deleted', [
            'plan_id' => $plan->id,
            'plan_name' => $plan->name,
            'deleted_by' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pricing plan deleted successfully.'
        ]);
    }

    /**
     * Show system logs.
     */
    public function logs(Request $request)
    {
        $logFile = storage_path('logs/laravel.log');
        
        if (!file_exists($logFile)) {
            $logs = [];
        } else {
            $logs = file_get_contents($logFile);
            $logs = array_reverse(explode("\n", $logs));
            $logs = array_slice($logs, 0, 1000); // Last 1000 lines
        }

        return view('admin.logs', compact('logs'));
    }
}