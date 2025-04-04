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
            if (!Schema::hasColumn('print_configurations', 'status')) {
                $table->string('status')->default('pending');
            }
            if (!Schema::hasColumn('print_configurations', 'step')) {
                $table->integer('step')->default(1);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('print_configurations', function (Blueprint $table) {
            $table->dropColumn(['status', 'step']);
        });
    }
};
