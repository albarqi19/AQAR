<?php

namespace App\Exports;

use App\Models\Contract;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ContractsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $contracts;

    public function __construct($contracts = null)
    {
        $this->contracts = $contracts;
    }

    public function collection()
    {
        return $this->contracts ?: Contract::with(['tenant', 'shop.building', 'payments'])->get();
    }

    public function headings(): array
    {
        return [
            'رقم العقد',
            'المستأجر',
            'رقم هاتف المستأجر',
            'المحل',
            'المبنى',
            'بداية العقد',
            'نهاية العقد',
            'الإيجار الشهري',
            'قيمة التأمين',
            'حالة العقد',
            'تاريخ الإنشاء',
            'إجمالي المدفوعات',
            'المبلغ المتبقي'
        ];
    }

    public function map($contract): array
    {
        $totalPaid = $contract->payments->sum('amount');
        $totalRent = $contract->monthly_rent * $contract->start_date->diffInMonths($contract->end_date);
        $remaining = $totalRent + $contract->security_deposit - $totalPaid;

        return [
            $contract->contract_number,
            $contract->tenant->name ?? 'غير محدد',
            $contract->tenant->phone ?? 'غير محدد',
            $contract->shop->shop_number ?? 'غير محدد',
            $contract->shop->building->name ?? 'غير محدد',
            $contract->start_date->format('Y-m-d'),
            $contract->end_date->format('Y-m-d'),
            number_format($contract->monthly_rent, 2),
            number_format($contract->security_deposit, 2),
            $contract->status,
            $contract->created_at->format('Y-m-d'),
            number_format($totalPaid, 2),
            number_format($remaining, 2)
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
