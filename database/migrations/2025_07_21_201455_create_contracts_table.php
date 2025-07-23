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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade')->comment('معرف المحل');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade')->comment('معرف المستأجر');
            $table->string('contract_number')->unique()->comment('رقم العقد');
            $table->date('start_date')->comment('تاريخ بداية العقد');
            $table->date('end_date')->comment('تاريخ نهاية العقد');
            $table->integer('duration_months')->comment('مدة العقد بالأشهر');
            $table->decimal('annual_rent', 10, 2)->comment('قيمة الإيجار السنوي');
            $table->decimal('payment_amount', 10, 2)->comment('قيمة الدفعة');
            $table->enum('payment_frequency', ['monthly', 'quarterly', 'semi_annual', 'annual'])->default('annual')->comment('دورية السداد');
            $table->decimal('tax_rate', 5, 2)->default(15.00)->comment('نسبة الضريبة');
            $table->decimal('tax_amount', 10, 2)->comment('قيمة الضريبة');
            $table->decimal('fixed_amounts', 10, 2)->default(0)->comment('المبالغ الثابتة');
            $table->decimal('total_annual_amount', 10, 2)->comment('القيمة الإجمالية السنوية');
            $table->enum('status', ['active', 'expired', 'terminated', 'renewal_pending'])->default('active')->comment('حالة العقد');
            $table->text('terms')->nullable()->comment('شروط العقد');
            $table->json('documents')->nullable()->comment('وثائق العقد');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
