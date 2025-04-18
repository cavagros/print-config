<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('representation_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->string('description')->nullable();
            $table->decimal('base_price', 8, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default values
        DB::table('representation_zones')->insert([
            [
                'name' => 'Zone locale',
                'code' => 'ZL',
                'description' => 'Représentation dans la même ville',
                'base_price' => 0.00,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Zone régionale',
                'code' => 'ZR',
                'description' => 'Représentation dans la même région',
                'base_price' => 50.00,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Zone nationale',
                'code' => 'ZN',
                'description' => 'Représentation dans toute la France',
                'base_price' => 100.00,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Zone internationale',
                'code' => 'ZI',
                'description' => 'Représentation à l\'étranger',
                'base_price' => 200.00,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('representation_zones');
    }
}; 