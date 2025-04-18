<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('binding_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->decimal('price_ht', 8, 2);
            $table->integer('min_pages')->default(1);
            $table->integer('max_pages')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default values
        DB::table('binding_types')->insert([
            [
                'name' => 'Agrafe',
                'description' => 'Reliure par agrafage',
                'price_ht' => 0.50,
                'min_pages' => 1,
                'max_pages' => 50,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Spirale plastique',
                'description' => 'Reliure par spirale plastique',
                'price_ht' => 1.00,
                'min_pages' => 1,
                'max_pages' => 200,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Spirale métallique',
                'description' => 'Reliure par spirale métallique',
                'price_ht' => 1.50,
                'min_pages' => 1,
                'max_pages' => 300,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Reliure rigide',
                'description' => 'Reliure rigide professionnelle',
                'price_ht' => 5.00,
                'min_pages' => 50,
                'max_pages' => 500,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('binding_types');
    }
}; 