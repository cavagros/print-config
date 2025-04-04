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
        Schema::create('cabinet_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('print_configuration_id')->constrained()->onDelete('cascade');
            $table->string('cabinet_name');
            $table->string('address');
            $table->string('postal_code', 10);
            $table->string('city');
            $table->string('phone', 20);
            $table->string('contact_email');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cabinet_infos');
    }
}; 