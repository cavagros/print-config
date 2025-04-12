<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('print_configurations', function (Blueprint $table) {
            $table->string('stripe_price_id')->nullable()->after('total_price');
        });
    }

    public function down()
    {
        Schema::table('print_configurations', function (Blueprint $table) {
            $table->dropColumn('stripe_price_id');
        });
    }
}; 