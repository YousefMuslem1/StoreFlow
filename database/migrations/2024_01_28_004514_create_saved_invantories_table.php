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
        Schema::create('saved_invantories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->tinyInteger('type_id');
            $table->tinyInteger('caliber_id');
            $table->tinyInteger('status');
            $table->tinyInteger('caliber_filter')->nullable();
            $table->tinyInteger('type_filter')->nullable();
            $table->foreignId('saved_invantory_date_id')->constrained()->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_invantories');
                    // $table->unsignedBigInteger('type_id');
            // $table->unsignedBigInteger('caliber_id');
            // $table->decimal('weight', 8, 2);
            // $table->decimal('selled_price', 8, 2);
    }
};
