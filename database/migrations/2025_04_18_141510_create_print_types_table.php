<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('print_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->decimal('price_ht', 8, 2);
            $table->boolean('is_color')->default(false);
            $table->boolean('is_double_sided')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default values
        DB::table('print_types')->insert([
            [
                'name' => 'Recto seul noir et blanc',
                'description' => 'Impression recto en noir et blanc',
                'price_ht' => 0.20,
                'is_color' => false,
                'is_double_sided' => false,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Recto seul couleur',
                'description' => 'Impression recto en couleur',
                'price_ht' => 0.30,
                'is_color' => true,
                'is_double_sided' => false,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Recto verso noir et blanc',
                'description' => 'Impression recto verso en noir et blanc',
                'price_ht' => 0.15,
                'is_color' => false,
                'is_double_sided' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Recto verso couleur',
                'description' => 'Impression recto verso en couleur',
                'price_ht' => 0.20,
                'is_color' => true,
                'is_double_sided' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_types');
    }
};
