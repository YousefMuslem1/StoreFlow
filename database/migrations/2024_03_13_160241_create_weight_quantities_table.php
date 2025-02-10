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
        Schema::create('weight_quantities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quantity_id');
            $table->foreignId('product_id')->nullable(); 
            $table->decimal('ounce_price')->default(1);
            $table->decimal('weight', 10, 2);
            $table->decimal('price', 10, 2)->nullable();
            $table->tinyInteger('status')->nullable(); // 1 => selled, 2  => merged, 3 => recycled
            $table->text('notice')->nullable();
            $table->unsignedBigInteger('user_id'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('weight_quantities');
    }
};
