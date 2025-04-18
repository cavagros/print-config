<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pleading_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->string('description')->nullable();
            $table->decimal('base_price', 8, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default values
        DB::table('pleading_types')->insert([
            [
                'name' => 'Plaidoirie simple',
                'code' => 'PS',
                'description' => 'Plaidoirie standard sans particularité',
                'base_price' => 0.00,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Plaidoirie complexe',
                'code' => 'PC',
                'description' => 'Plaidoirie avec plusieurs parties ou questions complexes',
                'base_price' => 50.00,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Plaidoirie urgente',
                'code' => 'PU',
                'description' => 'Plaidoirie nécessitant une préparation rapide',
                'base_price' => 100.00,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Plaidoirie d\'appel',
                'code' => 'PA',
                'description' => 'Plaidoirie devant une cour d\'appel',
                'base_price' => 75.00,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('pleading_types');
    }
}; 