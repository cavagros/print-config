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
            $table->unsignedTinyInteger('step')->default(1)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('print_configurations', function (Blueprint $table) {
            $table->dropColumn('step');
        });
    }
}; 