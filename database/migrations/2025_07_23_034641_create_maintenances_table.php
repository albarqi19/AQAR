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
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            
            // Polymorphic relationship - يمكن ربط الصيانة بمبنى أو محل
            $table->morphs('maintainable');
            
            // معلومات الصيانة
            $table->date('maintenance_date'); // تاريخ الصيانة
            $table->string('maintenance_type'); // نوع الصيانة
            $table->text('description'); // وصف الصيانة
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])
                  ->default('pending'); // حالة الصيانة
            $table->text('notes')->nullable(); // ملاحظات
            $table->decimal('cost', 10, 2)->nullable(); // التكلفة (اختياري)
            
            // معلومات إضافية
            $table->string('contractor_name')->nullable(); // اسم المقاول
            $table->string('contractor_phone')->nullable(); // رقم المقاول
            $table->date('scheduled_date')->nullable(); // التاريخ المجدول
            $table->date('completed_date')->nullable(); // تاريخ الإنجاز
            $table->foreignId('created_by')->constrained('users'); // من أنشأ السجل
            
            $table->timestamps();
            
            // فهارس للبحث السريع (morphs() ينشئ فهرس تلقائياً للعلاقة polymorphic)
            $table->index('maintenance_type');
            $table->index('status');
            $table->index('maintenance_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};
