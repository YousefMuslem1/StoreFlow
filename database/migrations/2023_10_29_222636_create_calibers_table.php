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
        Schema::create('calibers', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('name');
            $table->unsignedDecimal('caliber_price', 10, 2)->default(0);
            $table->unsignedDecimal('transfarmed', 10, 2)->default(0.875);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('calibers');
    }
};
