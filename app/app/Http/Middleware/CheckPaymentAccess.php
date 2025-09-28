<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPaymentAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Not authenticated'], 401);
            }
            return redirect()->route('login');
        }

        // Check if user can take test
        if (!$user->canTakeTest()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => 'Payment required',
                    'message' => 'You need to purchase a test plan to continue. Your first test is free!',
                    'redirect' => route('payment.index')
                ], 402); // Payment Required
            }
            // Redirect to payment page
            return redirect()->route('payment.index')
                ->with('error', 'You need to purchase a test plan to continue. Your first test is free!');
        }

        return $next($request);
    }
}