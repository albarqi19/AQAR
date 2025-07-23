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
        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained()->onDelete('cascade')->comment('معرف الحي');
            $table->foreignId('landlord_id')->constrained()->onDelete('cascade')->comment('معرف المكتب العقاري المدير');
            $table->string('name')->comment('اسم المبنى');
            $table->string('building_number')->nullable()->comment('رقم المبنى');
            $table->text('address')->nullable()->comment('العنوان التفصيلي');
            $table->integer('floors_count')->default(1)->comment('عدد الأدوار');
            $table->integer('total_shops')->default(0)->comment('إجمالي المحلات');
            $table->decimal('total_area', 10, 2)->nullable()->comment('المساحة الإجمالية');
            $table->year('construction_year')->nullable()->comment('سنة البناء');
            $table->text('description')->nullable()->comment('وصف المبنى');
            $table->json('documents')->nullable()->comment('الوثائق المرفقة');
            $table->boolean('is_active')->default(true)->comment('هل المبنى نشط');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buildings');
    }
};
