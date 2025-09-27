<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pricing Plans Management - Admin</title>
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
                    <a href="{{ route('admin.pricing.index') }}" class="text-indigo-600 font-semibold">Pricing</a>
                    <span class="text-gray-700">{{ Auth::user()->name }}</span>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Pricing Plans Management</h2>
                <p class="text-gray-600">Manage pricing plans and features</p>
            </div>
            <button onclick="openCreateModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors">
                Create New Plan
            </button>
        </div>

        @if(session('success'))
        <div id="successAlert" class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded flex justify-between items-center">
            <span>{{ session('success') }}</span>
            <button onclick="closeAlert('successAlert')" class="text-green-700 hover:text-green-900">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        @endif

        @if(session('error'))
        <div id="errorAlert" class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded flex justify-between items-center">
            <span>{{ session('error') }}</span>
            <button onclick="closeAlert('errorAlert')" class="text-red-700 hover:text-red-900">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        @endif

        <!-- Dynamic Alert Container -->
        <div id="alertContainer" class="fixed top-4 right-4 z-50"></div>

        <!-- Pricing Plans Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tests</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Validity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($plans as $plan)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="text-sm font-medium text-gray-900">{{ $plan->name }}</div>
                                @if($plan->is_popular)
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    Popular
                                </span>
                                @endif
                            </div>
                            <div class="text-sm text-gray-500">{{ $plan->slug }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ${{ number_format($plan->price, 2) }} {{ strtoupper($plan->currency) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $plan->test_limit == 999 ? 'Unlimited' : $plan->test_limit }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $plan->validity_days }} days
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $plan->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $plan->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button onclick="openEditModal({{ $plan->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                            <button onclick="deletePlan({{ $plan->id }})" class="text-red-600 hover:text-red-900">Delete</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No pricing plans found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div id="planModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center p-4">
        <div class="modal-content bg-white rounded-lg shadow-xl max-w-2xl w-full">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 id="modalTitle" class="text-lg font-semibold text-gray-900">Create Pricing Plan</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form id="planForm">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                <input type="text" id="planName" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                                <input type="text" id="planSlug" name="slug" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea id="planDescription" name="description" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                                <input type="number" id="planPrice" name="price" step="0.01" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                                <select id="planCurrency" name="currency" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="usd">USD</option>
                                    <option value="eur">EUR</option>
                                    <option value="gbp">GBP</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Test Limit</label>
                                <input type="number" id="planTestLimit" name="test_limit" min="1" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Validity (Days)</label>
                                <input type="number" id="planValidityDays" name="validity_days" min="1" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Stripe Price ID (Optional)</label>
                                <input type="text" id="planStripePriceId" name="stripe_price_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Features (one per line)</label>
                            <textarea id="planFeatures" name="features" rows="3" placeholder="1 Speaking Test&#10;Detailed Feedback&#10;Certificate (if passed)" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                        </div>
                        
                        <div class="flex items-center space-x-6">
                            <label class="flex items-center">
                                <input type="checkbox" id="planIsPopular" name="is_popular" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700">Mark as Popular</span>
                            </label>
                            
                            <label class="flex items-center">
                                <input type="checkbox" id="planIsActive" name="is_active" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700">Active</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex space-x-3 mt-6">
                        <button type="button" onclick="closeModal()" class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="flex-1 bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700 transition-colors">
                            Save Plan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 19.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-gray-900">Confirm Action</h3>
                    </div>
                </div>
                <div class="mb-6">
                    <p id="confirmMessage" class="text-sm text-gray-500">Are you sure you want to perform this action?</p>
                </div>
                <div class="flex space-x-3">
                    <button onclick="closeConfirmModal()" class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400 transition-colors">
                        Cancel
                    </button>
                    <button id="confirmButton" class="flex-1 bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700 transition-colors">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Ensure modals are properly positioned */
        #planModal, #confirmModal {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            z-index: 9999 !important;
            display: none;
        }
        
        #planModal.show, #confirmModal.show {
            display: flex !important;
        }
        
        /* Prevent body scroll when modal is open */
        body.modal-open {
            overflow: hidden;
        }
        
        /* Modal backdrop */
        .modal-backdrop {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        /* Modal content */
        .modal-content {
            position: relative;
            z-index: 10000;
            max-height: 90vh;
            overflow-y: auto;
        }
    </style>

    <script>
        let currentPlanId = null;
        let isEditMode = false;

        function openCreateModal() {
            isEditMode = false;
            currentPlanId = null;
            document.getElementById('modalTitle').textContent = 'Create Pricing Plan';
            document.getElementById('planForm').reset();
            document.getElementById('planIsActive').checked = true;
            document.getElementById('planModal').classList.add('show');
            document.body.classList.add('modal-open');
        }

        function openEditModal(planId) {
            isEditMode = true;
            currentPlanId = planId;
            document.getElementById('modalTitle').textContent = 'Edit Pricing Plan';
            
            // Find the plan data from the table
            const planRow = document.querySelector(`button[onclick="openEditModal(${planId})"]`).closest('tr');
            const planName = planRow.querySelector('td:first-child .text-sm.font-medium').textContent;
            const planSlug = planRow.querySelector('td:first-child .text-sm.text-gray-500').textContent;
            const planPrice = planRow.querySelector('td:nth-child(2)').textContent.replace('$', '').replace(' USD', '');
            const planStatus = planRow.querySelector('td:nth-child(5) span').textContent.toLowerCase();
            
            // Populate form with existing data
            document.getElementById('planName').value = planName;
            document.getElementById('planSlug').value = planSlug;
            document.getElementById('planPrice').value = planPrice;
            document.getElementById('planIsActive').checked = planStatus === 'active';
            
            document.getElementById('planModal').classList.add('show');
            document.body.classList.add('modal-open');
        }

        function closeModal() {
            document.getElementById('planModal').classList.remove('show');
            document.body.classList.remove('modal-open');
        }

        function deletePlan(planId) {
            currentPlanId = planId;
            document.getElementById('confirmMessage').textContent = 'Are you sure you want to delete this pricing plan? This action cannot be undone.';
            document.getElementById('confirmButton').onclick = confirmDelete;
            document.getElementById('confirmModal').classList.add('show');
            document.body.classList.add('modal-open');
        }

        function confirmDelete() {
            fetch(`/admin/pricing/${currentPlanId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Plan deleted successfully!', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(data.message || 'Error deleting plan', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Error deleting plan', 'error');
            })
            .finally(() => {
                closeConfirmModal();
            });
        }

        function closeConfirmModal() {
            document.getElementById('confirmModal').classList.remove('show');
            document.body.classList.remove('modal-open');
        }

        function closeAlert(alertId) {
            document.getElementById(alertId).style.display = 'none';
        }

        function showAlert(message, type = 'success') {
            const alertContainer = document.getElementById('alertContainer');
            const alertId = 'alert-' + Date.now();
            const bgColor = type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
            
            const alertHtml = `
                <div id="${alertId}" class="mb-4 ${bgColor} border px-4 py-3 rounded flex justify-between items-center shadow-lg">
                    <span>${message}</span>
                    <button onclick="closeAlert('${alertId}')" class="ml-4 text-current hover:opacity-75">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            `;
            
            alertContainer.insertAdjacentHTML('beforeend', alertHtml);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                const alert = document.getElementById(alertId);
                if (alert) {
                    alert.remove();
                }
            }, 5000);
        }

        document.getElementById('planForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const features = formData.get('features').split('\n').filter(f => f.trim());
            
            const data = {
                name: formData.get('name'),
                slug: formData.get('slug'),
                description: formData.get('description'),
                price: parseFloat(formData.get('price')),
                currency: formData.get('currency'),
                test_limit: parseInt(formData.get('test_limit')),
                validity_days: parseInt(formData.get('validity_days')),
                is_popular: formData.get('is_popular') === 'on',
                is_active: formData.get('is_active') === 'on',
                features: features
            };

            const url = isEditMode ? `/admin/pricing/${currentPlanId}` : '/admin/pricing';
            const method = isEditMode ? 'PATCH' : 'POST';

            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message || 'Plan saved successfully!', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(data.message || 'Error saving plan', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Error saving plan', 'error');
            });
        });

        // Close modal when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.id === 'planModal') {
                closeModal();
            }
            if (e.target.id === 'confirmModal') {
                closeConfirmModal();
            }
        });

        // Close modal with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (document.getElementById('planModal').classList.contains('show')) {
                    closeModal();
                }
                if (document.getElementById('confirmModal').classList.contains('show')) {
                    closeConfirmModal();
                }
            }
        });
    </script>
</body>
</html>
