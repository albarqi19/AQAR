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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            
            // Polymorphic relationship - يمكن ربط المصروف بمبنى أو محل
            $table->morphs('expensable');
            
            // معلومات المصروف
            $table->date('expense_date'); // تاريخ المصروف
            $table->string('expense_type'); // نوع المصروف
            $table->text('description'); // وصف المصروف
            $table->decimal('amount', 10, 2); // المبلغ (إجباري)
            $table->string('currency', 3)->default('SAR'); // العملة
            
            // معلومات إضافية
            $table->text('notes')->nullable(); // ملاحظات
            $table->string('vendor_name')->nullable(); // اسم المورد
            $table->string('vendor_phone')->nullable(); // رقم المورد
            $table->string('invoice_number')->nullable(); // رقم الفاتورة
            $table->string('receipt_path')->nullable(); // مسار الإيصال المرفوع
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending'); // حالة الدفع
            $table->date('paid_date')->nullable(); // تاريخ الدفع
            $table->foreignId('created_by')->constrained('users'); // من أنشأ السجل
            
            $table->timestamps();
            
            // فهارس للبحث السريع (morphs() ينشئ فهرس تلقائياً للعلاقة polymorphic)
            $table->index('expense_type');
            $table->index('status');
            $table->index('expense_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
