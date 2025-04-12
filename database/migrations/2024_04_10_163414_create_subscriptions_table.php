<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('print_configuration_id')->constrained()->onDelete('cascade');
            $table->string('name')->default('default');
            $table->string('stripe_id')->nullable();
            $table->string('stripe_status')->default('incomplete');
            $table->string('stripe_price')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('eur');
            $table->json('metadata')->nullable();
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'stripe_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
}; 