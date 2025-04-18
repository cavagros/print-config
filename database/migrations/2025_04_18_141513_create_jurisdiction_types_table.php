<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('jurisdiction_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default values
        DB::table('jurisdiction_types')->insert([
            [
                'name' => 'Tribunal de Grande Instance',
                'code' => 'TGI',
                'description' => 'Juridiction de droit commun en première instance',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Cour d\'Appel',
                'code' => 'CA',
                'description' => 'Juridiction de second degré',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Cour de Cassation',
                'code' => 'CASS',
                'description' => 'Juridiction suprême',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Conseil d\'État',
                'code' => 'CE',
                'description' => 'Juridiction administrative suprême',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('jurisdiction_types');
    }
}; 