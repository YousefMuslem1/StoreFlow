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
        Schema::create('supplier_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->decimal('fixed_gold', 10, 2)->default(0); // الذهب المثبت
            $table->decimal('fixed_money', 10, 2)->default(0); // المال المثبت
            $table->decimal('unfixed_gold', 10, 2)->default(0); // الذهب الغير مثبت
            $table->decimal('unfixed_money', 10, 2)->default(0); // المال الغير مثبت
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_balances');
    }
};
