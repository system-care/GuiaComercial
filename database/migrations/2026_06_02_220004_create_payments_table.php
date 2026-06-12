<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->string('asaas_payment_id')->nullable()->unique();
            $table->decimal('value', 10, 2);
            // PENDING | RECEIVED | CONFIRMED | OVERDUE | REFUNDED | CANCELED
            $table->string('status', 30)->default('PENDING')->index();
            // BOLETO | CREDIT_CARD | PIX | UNDEFINED
            $table->string('billing_type', 30)->default('UNDEFINED');
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('invoice_url', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
