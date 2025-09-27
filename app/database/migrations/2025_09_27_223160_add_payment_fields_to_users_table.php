<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('stripe_customer_id')->nullable()->after('email_verified_at');
            $table->enum('role', ['user', 'admin', 'superadmin'])->default('user')->after('stripe_customer_id');
            $table->integer('free_tests_used')->default(0)->after('role');
            $table->boolean('has_payment_waiver')->default(false)->after('free_tests_used');
            $table->decimal('custom_discount_percentage', 5, 2)->nullable()->after('has_payment_waiver');
            $table->timestamp('waiver_expires_at')->nullable()->after('custom_discount_percentage');
            $table->text('waiver_reason')->nullable()->after('waiver_expires_at');
            
            $table->index(['role', 'has_payment_waiver']);
            $table->index('stripe_customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_customer_id',
                'role',
                'free_tests_used',
                'has_payment_waiver',
                'custom_discount_percentage',
                'waiver_expires_at',
                'waiver_reason'
            ]);
        });
    }
};