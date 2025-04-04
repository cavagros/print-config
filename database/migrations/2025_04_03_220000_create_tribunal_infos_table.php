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
        Schema::create('tribunal_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('print_configuration_id')->constrained()->onDelete('cascade');
            $table->string('tribunal_name');
            $table->string('chamber')->nullable();
            $table->string('address');
            $table->string('postal_code');
            $table->string('city');
            $table->string('phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tribunal_infos');
    }
}; 