<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('print_configurations', function (Blueprint $table) {
            $table->timestamp('paid_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('print_configurations', function (Blueprint $table) {
            $table->dropColumn('paid_at');
        });
    }
}; 