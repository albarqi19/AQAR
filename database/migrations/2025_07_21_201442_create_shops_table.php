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
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained()->onDelete('cascade')->comment('معرف المبنى');
            $table->string('shop_number')->comment('رقم المحل');
            $table->integer('floor')->default(0)->comment('الدور');
            $table->decimal('area', 10, 2)->comment('المساحة بالمتر المربع');
            $table->string('shop_type')->nullable()->comment('نوع المحل');
            $table->enum('status', ['vacant', 'occupied', 'maintenance'])->default('vacant')->comment('حالة المحل');
            $table->text('description')->nullable()->comment('وصف المحل');
            $table->json('documents')->nullable()->comment('الوثائق المرفقة');
            $table->boolean('is_active')->default(true)->comment('هل المحل نشط');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
