<?php

namespace App\Exports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaymentsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $payments;

    public function __construct($payments = null)
    {
        $this->payments = $payments;
    }

    public function collection()
    {
        return $this->payments ?: Payment::with(['contract.tenant', 'contract.shop.building'])->get();
    }

    public function headings(): array
    {
        return [
            'رقم الدفعة',
            'رقم العقد',
            'المستأجر',
            'المحل',
            'المبنى',
            'المبلغ',
            'نوع الدفعة',
            'تاريخ الدفع',
            'طريقة الدفع',
            'ملاحظات',
            'تاريخ الإنشاء'
        ];
    }

    public function map($payment): array
    {
        return [
            $payment->payment_number,
            $payment->contract->contract_number ?? 'غير محدد',
            $payment->contract->tenant->name ?? 'غير محدد',
            $payment->contract->shop->shop_number ?? 'غير محدد',
            $payment->contract->shop->building->name ?? 'غير محدد',
            number_format($payment->amount, 2),
            $payment->payment_type,
            $payment->payment_date->format('Y-m-d'),
            $payment->payment_method,
            $payment->notes ?? '',
            $payment->created_at->format('Y-m-d H:i:s')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
        ];
    }
}
