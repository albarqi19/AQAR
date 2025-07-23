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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->onDelete('cascade')->comment('معرف العقد');
            $table->string('invoice_number')->unique()->comment('رقم الفاتورة');
            $table->date('invoice_date')->comment('تاريخ الفاتورة');
            $table->decimal('invoice_amount', 10, 2)->comment('قيمة الفاتورة');
            $table->decimal('paid_amount', 10, 2)->default(0)->comment('المبلغ المحصل');
            $table->decimal('remaining_amount', 10, 2)->default(0)->comment('المبلغ المتبقي');
            $table->date('due_date')->comment('تاريخ الاستحقاق');
            $table->date('payment_date')->nullable()->comment('تاريخ التحصيل');
            $table->enum('status', ['pending', 'partial', 'paid', 'overdue'])->default('pending')->comment('حالة الدفع');
            $table->string('payment_method')->nullable()->comment('طريقة الدفع');
            $table->text('notes')->nullable()->comment('ملاحظات');
            $table->string('receipt_file')->nullable()->comment('ملف الإيصال');
            $table->integer('month')->comment('الشهر');
            $table->integer('year')->comment('السنة');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
