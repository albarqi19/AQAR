<?php

namespace Database\Seeders;

use App\Models\Contract;
use App\Models\Payment;
use App\Models\Shop;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContractsAndPaymentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // الحصول على المحلات المؤجرة التي ليس لها عقود
        $occupiedShopsWithoutContracts = Shop::where('status', 'occupied')
            ->whereDoesntHave('contracts')
            ->limit(50) // حد أقصى 50 عقد
            ->get();

        // الحصول على المستأجرين المتاحين
        $availableTenants = Tenant::whereDoesntHave('contracts')->get();

        echo "المحلات المؤجرة بدون عقود: " . $occupiedShopsWithoutContracts->count() . PHP_EOL;
        echo "المستأجرين المتاحين: " . $availableTenants->count() . PHP_EOL;

        $contractNumber = Contract::max('contract_number') ? (int)Contract::max('contract_number') + 1 : 1000;

        foreach ($occupiedShopsWithoutContracts as $index => $shop) {
            if ($index < $availableTenants->count()) {
                $tenant = $availableTenants[$index];
                
                // إنشاء تواريخ العقد
                $startDate = Carbon::now()->subMonths(rand(1, 18));
                $duration = 12; // سنة واحدة
                $endDate = $startDate->copy()->addMonths($duration)->subDay();
                
                // حساب المبالغ المالية
                $annualRent = rand(50000, 300000);
                $taxRate = 15;
                $taxAmount = ($annualRent * $taxRate) / 100;
                $fixedAmounts = rand(0, 10000);
                $totalAmount = $annualRent + $taxAmount + $fixedAmounts;
                
                $paymentFrequencies = ['monthly', 'quarterly', 'semi_annual', 'annual'];
                $paymentFrequency = $paymentFrequencies[rand(0, 3)];
                
                $divisor = match($paymentFrequency) {
                    'monthly' => 12,
                    'quarterly' => 4,
                    'semi_annual' => 2,
                    default => 1
                };
                
                $paymentAmount = $totalAmount / $divisor;
                
                // تحديد حالة العقد
                $status = $endDate->isFuture() ? 'active' : 'expired';

                // إنشاء العقد
                $contract = Contract::create([
                    'shop_id' => $shop->id,
                    'tenant_id' => $tenant->id,
                    'contract_number' => (string)$contractNumber,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'duration_months' => $duration,
                    'annual_rent' => $annualRent,
                    'payment_amount' => $paymentAmount,
                    'payment_frequency' => $paymentFrequency,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                    'fixed_amounts' => $fixedAmounts,
                    'total_annual_amount' => $totalAmount,
                    'status' => $status,
                    'terms' => 'عقد إيجار تجاري وفقاً للأنظمة السعودية واللوائح المعمول بها في المملكة العربية السعودية.',
                ]);

                echo "تم إنشاء عقد رقم: {$contractNumber} للمحل: {$shop->shop_number} - المستأجر: {$tenant->name}" . PHP_EOL;

                // إنشاء المدفوعات للعقد
                $this->createPaymentsForContract($contract);

                $contractNumber++;
            }
        }

        echo "تم إنشاء العقود والمدفوعات بنجاح!" . PHP_EOL;
    }

    private function createPaymentsForContract(Contract $contract): void
    {
        $paymentCount = match($contract->payment_frequency) {
            'monthly' => 12,
            'quarterly' => 4,
            'semi_annual' => 2,
            default => 1
        };

        $monthsInterval = match($contract->payment_frequency) {
            'monthly' => 1,
            'quarterly' => 3,
            'semi_annual' => 6,
            default => 12
        };

        $paymentsCreated = 0;

        for ($i = 0; $i < $paymentCount; $i++) {
            $dueDate = $contract->start_date->copy()->addMonths($i * $monthsInterval);
            
            // تحديد حالة السداد (80% من المدفوعات المستحقة مدفوعة)
            $isPaid = $dueDate->isPast() && rand(1, 100) <= 80;
            
            $invoiceAmount = $contract->payment_amount;
            $paidAmount = $isPaid ? $invoiceAmount : (rand(1, 100) <= 20 ? rand(1000, $invoiceAmount) : 0);
            $remainingAmount = $invoiceAmount - $paidAmount;
            
            // تحديد حالة الدفع
            if ($paidAmount >= $invoiceAmount) {
                $status = 'paid';
            } elseif ($paidAmount > 0) {
                $status = 'partial';
            } elseif ($dueDate->isPast()) {
                $status = 'overdue';
            } else {
                $status = 'pending';
            }

            Payment::create([
                'contract_id' => $contract->id,
                'invoice_number' => 'INV-' . $contract->contract_number . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'invoice_date' => $dueDate->copy()->subDays(5),
                'invoice_amount' => $invoiceAmount,
                'paid_amount' => $paidAmount,
                'remaining_amount' => $remainingAmount,
                'due_date' => $dueDate,
                'payment_date' => $isPaid ? $dueDate->copy()->addDays(rand(-5, 15)) : null,
                'payment_method' => $paidAmount > 0 ? ['نقدي', 'تحويل بنكي', 'شيك', 'بطاقة ائتمان'][rand(0, 3)] : null,
                'status' => $status,
                'notes' => $isPaid ? 'تم السداد بالكامل' : ($paidAmount > 0 ? 'سداد جزئي' : null),
                'month' => $dueDate->month,
                'year' => $dueDate->year,
            ]);

            $paymentsCreated++;
        }

        echo "  - تم إنشاء {$paymentsCreated} مدفوعة للعقد رقم: {$contract->contract_number}" . PHP_EOL;
    }
}
