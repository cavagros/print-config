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
            $table->string('expe_suivi')->nullable();
            $table->timestamp('date_expedition')->nullable();
            $table->boolean('livre')->default(false);
            $table->timestamp('date_livraison')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('print_configurations', function (Blueprint $table) {
            $table->dropColumn(['expe_suivi', 'date_expedition', 'livre', 'date_livraison']);
        });
    }
}; 