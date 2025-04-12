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
        Schema::create('configurations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('total_price', 10, 2);
            $table->boolean('is_paid')->default(false);
            $table->boolean('is_subscription')->default(false);
            $table->string('subscription_id')->nullable();
            $table->string('subscription_status')->nullable();
            $table->string('status')->default('draft');
            $table->integer('step')->default(1);
            $table->integer('total_pages')->nullable();
            $table->string('format')->nullable();
            $table->string('binding_type')->nullable();
            $table->boolean('recto_verso')->default(false);
            $table->boolean('color')->default(false);
            $table->string('procedure_type')->nullable();
            $table->string('reference')->nullable();
            $table->string('id_dossier')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configurations');
    }
};
