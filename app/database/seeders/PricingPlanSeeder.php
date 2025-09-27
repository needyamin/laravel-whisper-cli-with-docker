<?php

namespace Database\Seeders;

use App\Models\PricingPlan;
use Illuminate\Database\Seeder;

class PricingPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Single Test',
                'slug' => 'single-test',
                'description' => 'Take one English speaking test',
                'price' => 9.99,
                'currency' => 'usd',
                'test_limit' => 1,
                'validity_days' => 30,
                'is_popular' => false,
                'is_active' => true,
                'features' => [
                    '1 Speaking Test',
                    'Detailed Feedback',
                    'Certificate (if passed)',
                    '30-day validity'
                ],
            ],
            [
                'name' => 'Test Pack',
                'slug' => 'test-pack',
                'description' => 'Take 5 English speaking tests',
                'price' => 39.99,
                'currency' => 'usd',
                'test_limit' => 5,
                'validity_days' => 60,
                'is_popular' => true,
                'is_active' => true,
                'features' => [
                    '5 Speaking Tests',
                    'Detailed Feedback',
                    'Certificates (if passed)',
                    '60-day validity',
                    'Best Value'
                ],
            ],
            [
                'name' => 'Unlimited Tests',
                'slug' => 'unlimited-tests',
                'description' => 'Unlimited English speaking tests for 30 days',
                'price' => 99.99,
                'currency' => 'usd',
                'test_limit' => 999,
                'validity_days' => 30,
                'is_popular' => false,
                'is_active' => true,
                'features' => [
                    'Unlimited Tests',
                    'Detailed Feedback',
                    'Certificates (if passed)',
                    '30-day validity',
                    'Perfect for intensive practice'
                ],
            ],
        ];

        foreach ($plans as $plan) {
            PricingPlan::create($plan);
        }
    }
}