<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pricing Plans - English Speaking Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-semibold text-gray-900">English Speaking Test</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                    <span class="text-gray-700">{{ Auth::user()->name }}</span>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900">Choose Your Plan</h2>
            <p class="mt-2 text-gray-600">Your first test is free! After that, choose a plan that suits your needs.</p>
        </div>

        @if(session('error'))
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            {{ session('error') }}
        </div>
        @endif

        @if(session('success'))
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
        @endif

        <!-- User Status -->
        <div class="mb-8 bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Your Status</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $user->free_tests_used }}/1</div>
                    <div class="text-sm text-gray-600">Free Tests Used</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold {{ $user->hasActiveWaiver() ? 'text-green-600' : 'text-gray-400' }}">
                        {{ $user->hasActiveWaiver() ? 'Yes' : 'No' }}
                    </div>
                    <div class="text-sm text-gray-600">Payment Waiver</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold {{ $user->activeSubscription() ? 'text-green-600' : 'text-gray-400' }}">
                        {{ $user->activeSubscription() ? $user->activeSubscription()->remaining_tests : '0' }}
                    </div>
                    <div class="text-sm text-gray-600">Remaining Tests</div>
                </div>
            </div>
        </div>

        <!-- Pricing Plans -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($plans as $plan)
            <div class="bg-white rounded-lg shadow-lg overflow-hidden {{ $plan->is_popular ? 'ring-2 ring-indigo-500' : '' }}">
                @if($plan->is_popular)
                <div class="bg-indigo-500 text-white text-center py-2 text-sm font-semibold">
                    Most Popular
                </div>
                @endif
                
                <div class="p-6">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $plan->name }}</h3>
                    <p class="text-gray-600 mb-4">{{ $plan->description }}</p>
                    
                    <div class="mb-6">
                        <span class="text-4xl font-bold text-gray-900">${{ number_format($plan->price, 2) }}</span>
                        <span class="text-gray-600">/ {{ $plan->test_limit == 999 ? 'month' : 'plan' }}</span>
                    </div>

                    <ul class="space-y-3 mb-6">
                        @foreach($plan->features as $feature)
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700">{{ $feature }}</span>
                        </li>
                        @endforeach
                    </ul>

                    @if($user->hasActiveWaiver())
                    <button class="w-full bg-green-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-green-700 transition-colors">
                        Free (Waiver Applied)
                    </button>
                    @elseif($user->getEffectiveDiscountPercentage() > 0)
                    <div class="mb-2">
                        <span class="text-sm text-gray-500 line-through">${{ number_format($plan->price, 2) }}</span>
                        <span class="text-lg font-bold text-green-600 ml-2">${{ number_format($plan->getPriceWithDiscount($user->getEffectiveDiscountPercentage()), 2) }}</span>
                        <span class="text-sm text-green-600">({{ $user->getEffectiveDiscountPercentage() }}% off)</span>
                    </div>
                    <button onclick="selectPlan({{ $plan->id }})" class="w-full bg-indigo-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-indigo-700 transition-colors">
                        Purchase with Discount
                    </button>
                    @else
                    <button onclick="selectPlan({{ $plan->id }})" class="w-full bg-indigo-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-indigo-700 transition-colors">
                        Choose Plan
                    </button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <!-- Payment Form Modal -->
        <div id="paymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Complete Payment</h3>
                        
                        <div id="paymentInfo" class="mb-4 p-4 bg-gray-50 rounded-lg">
                            <!-- Payment info will be populated here -->
                        </div>

                        <form id="paymentForm">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Card Details</label>
                                <div id="cardElement" class="p-3 border border-gray-300 rounded-lg">
                                    <!-- Stripe Elements will create form elements here -->
                                </div>
                                <div id="cardErrors" class="text-red-600 text-sm mt-2"></div>
                            </div>

                            <div class="flex space-x-3">
                                <button type="button" onclick="closePaymentModal()" class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400 transition-colors">
                                    Cancel
                                </button>
                                <button type="submit" id="submitButton" class="flex-1 bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700 transition-colors">
                                    Pay <span id="paymentAmount">$0.00</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const stripe = Stripe('{{ config("services.stripe.public_key") }}');
        let elements;
        let cardElement;
        let currentPlanId;
        let paymentIntentClientSecret;

        async function selectPlan(planId) {
            try {
                const response = await fetch('/payment/create-intent', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ plan_id: planId })
                });

                const data = await response.json();

                if (data.success) {
                    currentPlanId = planId;
                    paymentIntentClientSecret = data.client_secret;
                    
                    // Update payment info
                    document.getElementById('paymentInfo').innerHTML = `
                        <div class="flex justify-between">
                            <span>Plan:</span>
                            <span class="font-semibold">${data.plan_name}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Amount:</span>
                            <span class="font-semibold">$${data.amount}</span>
                        </div>
                        ${data.discount_applied ? `
                        <div class="flex justify-between text-green-600">
                            <span>Discount:</span>
                            <span class="font-semibold">${data.discount_percentage}% off</span>
                        </div>
                        ` : ''}
                    `;
                    
                    document.getElementById('paymentAmount').textContent = `$${data.amount}`;
                    
                    // Initialize Stripe Elements
                    elements = stripe.elements();
                    cardElement = elements.create('card');
                    cardElement.mount('#cardElement');
                    
                    // Show modal
                    document.getElementById('paymentModal').classList.remove('hidden');
                } else {
                    alert(data.message || 'Error creating payment. Please try again.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error creating payment. Please try again.');
            }
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').classList.add('hidden');
            if (cardElement) {
                cardElement.destroy();
            }
        }

        document.getElementById('paymentForm').addEventListener('submit', async (event) => {
            event.preventDefault();

            const submitButton = document.getElementById('submitButton');
            submitButton.disabled = true;
            submitButton.textContent = 'Processing...';

            try {
                const { error, paymentIntent } = await stripe.confirmCardPayment(
                    paymentIntentClientSecret,
                    {
                        payment_method: {
                            card: cardElement,
                        }
                    }
                );

                if (error) {
                    document.getElementById('cardErrors').textContent = error.message;
                    submitButton.disabled = false;
                    submitButton.innerHTML = 'Pay <span id="paymentAmount">$0.00</span>';
                } else if (paymentIntent.status === 'succeeded') {
                    // Payment succeeded, notify server
                    const response = await fetch('/payment/success', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            payment_intent_id: paymentIntent.id,
                            payment_id: currentPlanId
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        alert('Payment successful! You can now take your test.');
                        window.location.href = data.redirect || '/test';
                    } else {
                        alert(data.message || 'Payment verification failed. Please contact support.');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Payment processing error. Please try again.');
                submitButton.disabled = false;
                submitButton.innerHTML = 'Pay <span id="paymentAmount">$0.00</span>';
            }
        });
    </script>
</body>
</html>
