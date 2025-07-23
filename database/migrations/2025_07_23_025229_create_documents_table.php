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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            
            // Polymorphic relationship - يمكن ربط الوثيقة بأي نموذج
            $table->morphs('documentable');
            
            // معلومات الوثيقة
            $table->string('title'); // عنوان الوثيقة
            $table->text('description')->nullable(); // وصف الوثيقة
            $table->string('document_type'); // نوع الوثيقة (deed, soil_test, etc.)
            
            // معلومات الملف
            $table->string('file_name'); // اسم الملف الأصلي
            $table->string('file_path'); // مسار الملف المحفوظ
            $table->bigInteger('file_size')->nullable(); // حجم الملف بالبايت
            $table->string('mime_type')->nullable(); // نوع الملف
            
            // معلومات إضافية
            $table->foreignId('uploaded_by')->constrained('users'); // من رفع الوثيقة
            $table->boolean('is_active')->default(true); // هل الوثيقة فعالة
            $table->integer('sort_order')->default(0); // ترتيب العرض
            
            $table->timestamps();
            
            // فهارس للبحث السريع (morphs() ينشئ فهرس تلقائياً للعلاقة polymorphic)
            $table->index('document_type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
