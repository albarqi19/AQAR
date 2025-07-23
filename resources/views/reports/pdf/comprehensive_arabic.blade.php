<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $period_name }} - تقرير شامل</title>
    <style>
        @page {
            margin: 15mm;
            size: A4;
        }
        
        * {
            font-family: 'DejaVu Sans', sans-serif;
        }
        
        body {
            direction: rtl;
            text-align: right;
            margin: 0;
            padding: 10px;
            background-color: #ffffff;
            color: #333;
            line-height: 1.4;
            font-size: 11px;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #2563eb;
            padding: 15px 0;
            margin-bottom: 20px;
            background: #f8fafc;
            border-radius: 5px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
            color: #1e40af;
        }
        
        .header p {
            margin: 5px 0;
            font-size: 10px;
            color: #64748b;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 10px;
        }
        
        .logo img {
            max-width: 80px;
            max-height: 80px;
        }
        
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .stat-cell {
            background: #f8fafc;
            padding: 10px;
            border: 1px solid #e2e8f0;
            text-align: center;
            width: 33.33%;
        }
        
        .stat-cell h4 {
            margin: 0 0 5px 0;
            color: #1e40af;
            font-size: 9px;
        }
        
        .stat-value {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            margin: 0;
        }
        
        .amount {
            color: #059669;
        }
        
        .expense {
            color: #dc2626;
        }
        
        .section {
            background: white;
            padding: 15px;
            margin-bottom: 15px;
            border: 1px solid #e2e8f0;
            border-radius: 3px;
        }
        
        .section h2 {
            margin: 0 0 10px 0;
            color: #1e40af;
            font-size: 12px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .data-table th,
        .data-table td {
            padding: 6px;
            text-align: right;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9px;
        }
        
        .data-table th {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #475569;
        }
        
        .highlight-box {
            background: #fef3c7;
            padding: 10px;
            border-radius: 3px;
            border-right: 3px solid #f59e0b;
            margin: 10px 0;
        }
        
        .highlight-box h3 {
            margin: 0 0 5px 0;
            font-size: 11px;
            color: #92400e;
        }
        
        .net-income {
            font-size: 16px;
            font-weight: bold;
            margin: 0;
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 8px;
        }
        
        .no-data {
            text-align: center;
            color: #64748b;
            font-style: italic;
            padding: 20px;
        }
    </style>
</head>
<body>
    <!-- Header with Logo -->
    <div class="header">
        <div class="logo">
            <img src="{{ public_path('logo.png') }}" alt="شعار الشركة" style="max-width: 80px; max-height: 80px;">
        </div>
        <h1>تقرير {{ $period_name }}</h1>
        <p>تم الإنشاء: {{ $generated_at->format('Y-m-d H:i') }}</p>
        <p>الفترة: من {{ $date_range[0] }} إلى {{ $date_range[1] }}</p>
    </div>

    <!-- Key Statistics -->
    <table class="stats-table">
        <tr>
            <td class="stat-cell">
                <h4>إجمالي العقود</h4>
                <p class="stat-value">{{ number_format($stats['total_contracts']) }}</p>
            </td>
            <td class="stat-cell">
                <h4>العقود النشطة</h4>
                <p class="stat-value">{{ number_format($stats['active_contracts']) }}</p>
            </td>
            <td class="stat-cell">
                <h4>إجمالي المدفوعات</h4>
                <p class="stat-value amount">{{ number_format($stats['total_payments']) }} ريال</p>
            </td>
        </tr>
        <tr>
            <td class="stat-cell">
                <h4>إجمالي المصروفات</h4>
                <p class="stat-value expense">{{ number_format($stats['total_expenses']) }} ريال</p>
            </td>
            <td class="stat-cell">
                <h4>عدد المباني</h4>
                <p class="stat-value">{{ number_format($stats['total_buildings']) }}</p>
            </td>
            <td class="stat-cell">
                <h4>معدل الإشغال</h4>
                <p class="stat-value">{{ $stats['occupancy_rate'] }}%</p>
            </td>
        </tr>
    </table>

    <!-- Net Income Highlight -->
    <div class="highlight-box">
        <h3>صافي الدخل للفترة</h3>
        <p class="net-income {{ ($stats['total_payments'] - $stats['total_expenses']) >= 0 ? 'amount' : 'expense' }}">
            {{ number_format($stats['total_payments'] - $stats['total_expenses']) }} ريال
        </p>
    </div>

    <!-- Monthly Payments -->
    @if(count($monthly_payments) > 0)
    <div class="section">
        <h2>المدفوعات الشهرية</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>الشهر</th>
                    <th>السنة</th>
                    <th>المبلغ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($monthly_payments as $payment)
                <tr>
                    <td>{{ $payment['month'] }}</td>
                    <td>{{ $payment['year'] }}</td>
                    <td class="amount">{{ number_format($payment['amount']) }} ريال</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Building Performance -->
    @if(count($building_performance) > 0)
    <div class="section">
        <h2>أداء المباني</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>اسم المبنى</th>
                    <th>إجمالي المحلات</th>
                    <th>المحلات المؤجرة</th>
                    <th>معدل الإشغال</th>
                    <th>إجمالي المدفوعات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($building_performance as $building)
                <tr>
                    <td>{{ $building['name'] }}</td>
                    <td>{{ $building['total_shops'] }}</td>
                    <td>{{ $building['occupied_shops'] }}</td>
                    <td>{{ $building['occupancy_rate'] }}%</td>
                    <td class="amount">{{ number_format($building['total_payments']) }} ريال</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Top Payments -->
    @if(count($top_payments) > 0)
    <div class="section">
        <h2>أعلى المدفوعات</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>رقم الدفعة</th>
                    <th>المستأجر</th>
                    <th>المبنى</th>
                    <th>المحل</th>
                    <th>المبلغ</th>
                    <th>تاريخ الدفع</th>
                </tr>
            </thead>
            <tbody>
                @foreach($top_payments as $payment)
                <tr>
                    <td>{{ $payment->invoice_number ?? 'غير محدد' }}</td>
                    <td>{{ $payment->contract->tenant->name ?? 'غير محدد' }}</td>
                    <td>{{ $payment->contract->shop->building->name ?? 'غير محدد' }}</td>
                    <td>{{ $payment->contract->shop->shop_number ?? 'غير محدد' }}</td>
                    <td class="amount">{{ number_format($payment->paid_amount) }} ريال</td>
                    <td>{{ $payment->payment_date ? $payment->payment_date->format('Y-m-d') : 'غير محدد' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- New Contracts -->
    @if(count($new_contracts) > 0)
    <div class="section">
        <h2>العقود الجديدة</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>رقم العقد</th>
                    <th>المستأجر</th>
                    <th>المبنى</th>
                    <th>المحل</th>
                    <th>الإيجار الشهري</th>
                    <th>تاريخ الإنشاء</th>
                </tr>
            </thead>
            <tbody>
                @foreach($new_contracts as $contract)
                <tr>
                    <td>{{ $contract->contract_number ?? 'غير محدد' }}</td>
                    <td>{{ $contract->tenant->name ?? 'غير محدد' }}</td>
                    <td>{{ $contract->shop->building->name ?? 'غير محدد' }}</td>
                    <td>{{ $contract->shop->shop_number ?? 'غير محدد' }}</td>
                    <td class="amount">{{ number_format($contract->monthly_rent ?? 0) }} ريال</td>
                    <td>{{ $contract->created_at ? $contract->created_at->format('Y-m-d') : 'غير محدد' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Expenses by Type -->
    @if(count($expenses_by_type) > 0)
    <div class="section">
        <h2>المصروفات حسب النوع</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>نوع المصروف</th>
                    <th>إجمالي المبلغ</th>
                    <th>النسبة</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalExpenses = $expenses_by_type->sum('total');
                @endphp
                @foreach($expenses_by_type as $expense)
                <tr>
                    <td>{{ $expense->expense_type }}</td>
                    <td class="expense">{{ number_format($expense->total) }} ريال</td>
                    <td>
                        @php
                            $percentage = $totalExpenses > 0 ? round(($expense->total / $totalExpenses) * 100, 1) : 0;
                        @endphp
                        {{ $percentage }}%
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>تم إنشاء هذا التقرير بواسطة نظام إدارة العقارات - {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>
