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
        Schema::create('landlords', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('اسم المكتب العقاري');
            $table->string('company_name')->nullable()->comment('اسم الشركة');
            $table->string('commercial_registration')->unique()->comment('السجل التجاري');
            $table->string('phone')->comment('رقم الهاتف');
            $table->string('email')->unique()->comment('البريد الإلكتروني');
            $table->text('address')->nullable()->comment('العنوان');
            $table->string('contact_person')->nullable()->comment('الشخص المسؤول');
            $table->decimal('commission_rate', 5, 2)->default(5.00)->comment('نسبة العمولة');
            $table->text('notes')->nullable()->comment('ملاحظات');
            $table->json('documents')->nullable()->comment('الوثائق المرفقة');
            $table->boolean('is_active')->default(true)->comment('هل المكتب نشط');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landlords');
    }
};
