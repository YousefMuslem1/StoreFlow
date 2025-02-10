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
        Schema::create('supplier_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->integer('type'); // 1 money 2 gold
            $table->decimal('amount', 10, 2)->nullable(); // قيمة المال سالب اذا ارسلنا وموجب اذا استقبلنا وقد تكون مال او ذهب
            $table->decimal('price_per_gram', 10, 2)->nullable(); // سعر الذهب
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // الموظف الذي قام بالإدخال
            $table->decimal('expected_weight', 10, 2)->nullable(); // الوزن المتوقع
            $table->decimal('received_weight', 10, 2)->nullable(); // الوزن المستلم
            $table->integer('status')->default(1); // حالة العملية 1 معلق 2 مكتمل
            $table->text('note')->nullable();
            $table->text('logs')->nullable();
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_transactions');
    }
};
