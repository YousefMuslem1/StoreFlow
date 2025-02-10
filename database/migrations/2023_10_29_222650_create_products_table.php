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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->text('ident')->nullable();
            $table->text('short_ident')->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->float('measurement')->nullable();
            $table->decimal('ounce_price')->default(1);
            $table->float('caliber_selled_price')->nullable(); //سعر الصياغة
            $table->float('selled_price')->nullable();
            $table->integer('status')->default(2); // 2->available 1->selled
            $table->timestamp('selled_date')->nullable();
            $table->integer('user_id')->nullable();
            $table->foreignId('caliber_id')->constrained()->onDelete('cascade');
            $table->foreignId('type_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('products');
    }
};
