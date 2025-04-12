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
        Schema::create('print_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->integer('pages');
            $table->enum('print_type', ['noir_blanc', 'couleur']);
            $table->enum('paper_type', ['standard', 'recycle', 'premium', 'photo']);
            $table->enum('format', ['A4', 'A3', 'A5']);
            $table->enum('binding_type', ['sans_reliure', 'agrafage', 'spirale', 'dos_colle']);
            $table->enum('delivery_type', ['retrait_magasin', 'livraison_standard', 'livraison_express']);
            $table->decimal('total_price', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_configurations');
    }
};
