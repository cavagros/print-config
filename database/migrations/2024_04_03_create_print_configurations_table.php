<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('print_configurations', function (Blueprint $table) {
            // Modifier la colonne status pour accepter des chaînes plus longues
            $table->string('status', 50)->change();
        });
    }

    public function down(): void
    {
        Schema::table('print_configurations', function (Blueprint $table) {
            $table->string('status', 20)->change();
        });
    }
}; 