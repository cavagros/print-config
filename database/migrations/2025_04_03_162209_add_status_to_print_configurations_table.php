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
        Schema::table('print_configurations', function (Blueprint $table) {
            $table->enum('status', ['pending', 'uploaded', 'validated', 'completed'])
                  ->default('pending')
                  ->after('is_paid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('print_configurations', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
