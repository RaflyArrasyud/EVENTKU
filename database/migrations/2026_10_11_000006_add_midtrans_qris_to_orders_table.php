<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->uuid('checkout_key')->nullable()->unique()->after('bukti_bayar');
            $table->string('payment_method', 32)->default('manual')->after('checkout_key');
            $table->string('gateway_order_id')->nullable()->unique()->after('payment_method');
            $table->string('payment_status', 32)->nullable()->after('gateway_order_id');
            $table->text('qr_string')->nullable()->after('payment_status');
            $table->text('payment_qr_url')->nullable()->after('qr_string');
            $table->timestamp('payment_expires_at')->nullable()->after('payment_qr_url');
            $table->string('gateway_transaction_id')->nullable()->after('payment_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'checkout_key',
                'payment_method',
                'gateway_order_id',
                'payment_status',
                'qr_string',
                'payment_qr_url',
                'payment_expires_at',
                'gateway_transaction_id',
            ]);
        });
    }
};
