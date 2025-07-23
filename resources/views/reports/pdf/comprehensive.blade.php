<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $period_name }} - تقرير شامل</title>
    <style>
        @page {
            margin: 20mm;
            size: A4;
        }
        
        body {
            font-family: 'Arial Unicode MS', 'Tahoma', 'DejaVu Sans', sans-serif;
            direction: rtl;
            text-align: right;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            color: #333;
            line-height: 1.6;
            font-size: 12px;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            float: right;
            margin-left: 20px;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #2563eb;
            padding: 30px 20px;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            border-radius: 8px;
            position: relative;
        }
        
        .header::after {
            content: "";
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: #fbbf24;
            border-radius: 2px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .header p {
            margin: 8px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        
        .company-info {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5px;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        
        .header p {
            margin: 10px 0 0 0;
            font-size: 16px;
            opacity: 0.9;
        }
        
        .stats-grid {
            width: 100%;
            margin-bottom: 30px;
            border-collapse: collapse;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            text-align: center;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            margin: 5px;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            width: 4px;
            height: 100%;
            background: #2563eb;
        }
        
        .stat-card h3 {
            margin: 0 0 10px 0;
            color: #374151;
            font-size: 13px;
            font-weight: 600;
        }
        
        .stat-card .value {
            font-size: 20px;
            font-weight: bold;
            color: #1f2937;
            margin: 0;
        }
        
        .section {
            background: white;
            padding: 25px;
            margin-bottom: 25px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid #e5e7eb;
        }
        
        .section h2 {
            margin: 0 0 20px 0;
            color: #1e40af;
            font-size: 18px;
            border-bottom: 2px solid #dbeafe;
            padding-bottom: 10px;
            font-weight: 600;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 11px;
        }
        
        .table th,
        .table td {
            padding: 12px 8px;
            text-align: right;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }
        
        .table th {
            background-color: #f8fafc;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #d1d5db;
        }
        
        .table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .amount {
            font-weight: bold;
            color: #059669;
        }
        
        .expense {
            color: #dc2626;
            font-weight: bold;
        }
        
        .highlight {
            background: linear-gradient(135deg, #fef3c7, #fed7aa);
            padding: 20px;
            border-radius: 8px;
            border-right: 4px solid #f59e0b;
            margin: 20px 0;
        }
        
        .highlight h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
            color: #92400e;
        }
        
        .highlight .net-income {
            font-size: 22px;
            font-weight: bold;
            margin: 0;
        }
        
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            color: #6b7280;
            font-size: 11px;
        }
        
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 60px;
            color: rgba(37, 99, 235, 0.05);
            z-index: -1;
            font-weight: bold;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
            background-color: #2563eb;
            color: white;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .badge.success { background-color: #059669; }
        .badge.warning { background-color: #d97706; }
        .badge.danger { background-color: #dc2626; }
        
        .stat-card h3 {
            margin: 0 0 10px 0;
            color: #007bff;
            font-size: 14px;
        }
        
        .stat-card .value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin: 0;
        }
        
        .section {
            background: white;
            padding: 25px;
            margin-bottom: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .section h2 {
            margin: 0 0 20px 0;
            color: #007bff;
            font-size: 20px;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 10px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .table th,
        .table td {
            padding: 12px;
            text-align: right;
            border-bottom: 1px solid #e9ecef;
        }
        
        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #495057;
        }
        
        .table tbody tr:hover {
            background-color: #f5f5f5;
        }
        
        .chart-placeholder {
            background: linear-gradient(45deg, #f0f8ff, #e6f3ff);
            height: 200px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #007bff;
            font-size: 16px;
            margin: 15px 0;
            border: 2px dashed #007bff;
        }
        
        .progress-bar {
            background-color: #e9ecef;
            border-radius: 10px;
            height: 20px;
            overflow: hidden;
            margin: 10px 0;
        }
        
        .progress-fill {
            background: linear-gradient(90deg, #28a745, #20c997);
            height: 100%;
            transition: width 0.3s ease;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            background-color: #007bff;
            color: white;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .badge.success { background-color: #28a745; }
        .badge.warning { background-color: #ffc107; color: #212529; }
        .badge.danger { background-color: #dc3545; }
        
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e9ecef;
            color: #6c757d;
            font-size: 14px;
        }
        
        .amount {
            font-weight: bold;
            color: #28a745;
        }
        
        .expense {
            color: #dc3545;
        }
        
        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }
        
        .col {
            flex: 1;
            padding: 0 10px;
            margin-bottom: 20px;
        }
        
        .highlight {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #ffc107;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <!-- Watermark -->
    <div class="watermark">عقاري</div>
    
    <!-- Company Info -->
    <div class="company-info">
        <div style="display: flex; align-items: center; justify-content: center;">
            <img src="{{ public_path('logo.png') }}" alt="شعار الشركة" class="logo" style="margin-left: 15px;">
            <div>
                <div class="company-name">نظام إدارة العقارات - عقاري</div>
                <div style="color: #6b7280; font-size: 12px;">تقرير شامل ومفصل</div>
            </div>
        </div>
    </div>

    <!-- Header -->
    <div class="header">
        <div style="text-align: center; margin-bottom: 20px;">
            <h2 style="margin: 0; color: white; font-size: 32px;">🏢 عَقاري</h2>
            <p style="margin: 5px 0; opacity: 0.9;">نظام إدارة العقارات المتقدم</p>
        </div>
        <h1>تقرير {{ $period_name }}</h1>
        <p>تاريخ الإنشاء: {{ $generated_at->format('Y/m/d - H:i') }}</p>
        <p>فترة التقرير: من {{ $date_range[0] }} إلى {{ $date_range[1] }}</p>
    </div>

    <!-- Key Statistics -->
    <div class="section">
        <h2>📊 الإحصائيات الرئيسية</h2>
        <table class="stats-grid">
            <tr>
                <td class="stat-card">
                    <h3>إجمالي العقود</h3>
                    <p class="value">{{ number_format($stats['total_contracts']) }}</p>
                </td>
                <td class="stat-card">
                    <h3>العقود النشطة</h3>
                    <p class="value">{{ number_format($stats['active_contracts']) }}</p>
                </td>
                <td class="stat-card">
                    <h3>إجمالي المدفوعات</h3>
                    <p class="value amount">{{ number_format($stats['total_payments']) }} ر.س</p>
                </td>
            </tr>
            <tr>
                <td class="stat-card">
                    <h3>إجمالي المصروفات</h3>
                    <p class="value expense">{{ number_format($stats['total_expenses']) }} ر.س</p>
                </td>
                <td class="stat-card">
                    <h3>عدد المباني</h3>
                    <p class="value">{{ number_format($stats['total_buildings']) }}</p>
                </td>
                <td class="stat-card">
                    <h3>معدل الإشغال</h3>
                    <p class="value">{{ $stats['occupancy_rate'] }}%</p>
                </td>
            </tr>
        </table>
    </div>

    <!-- Net Income Highlight -->
    <div class="highlight">
        <h3>💰 صافي الدخل للفترة</h3>
        <p class="net-income">
            <span class="{{ ($stats['total_payments'] - $stats['total_expenses']) >= 0 ? 'amount' : 'expense' }}">
                {{ number_format($stats['total_payments'] - $stats['total_expenses']) }} ريال سعودي
            </span>
        </p>
        <div style="font-size: 11px; color: #6b7280; margin-top: 10px;">
            المدفوعات: {{ number_format($stats['total_payments']) }} ر.س | 
            المصروفات: {{ number_format($stats['total_expenses']) }} ر.س
        </div>
    </div>

    <!-- Monthly Payments Chart -->
    @if(count($monthly_payments) > 0)
    <div class="section">
        <h2>📈 المدفوعات الشهرية</h2>
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 40%;">الشهر</th>
                    <th style="width: 20%;">السنة</th>
                    <th style="width: 40%;">المبلغ</th>
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
        <h2>🏢 أداء المباني</h2>
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 25%;">اسم المبنى</th>
                    <th style="width: 15%;">إجمالي المحلات</th>
                    <th style="width: 15%;">المحلات المؤجرة</th>
                    <th style="width: 15%;">معدل الإشغال</th>
                    <th style="width: 30%;">إجمالي المدفوعات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($building_performance as $building)
                <tr>
                    <td style="font-weight: 600;">{{ $building['name'] }}</td>
                    <td>{{ $building['total_shops'] }}</td>
                    <td>{{ $building['occupied_shops'] }}</td>
                    <td>
                        <span class="badge {{ $building['occupancy_rate'] >= 80 ? 'success' : ($building['occupancy_rate'] >= 60 ? 'warning' : 'danger') }}">
                            {{ $building['occupancy_rate'] }}%
                        </span>
                    </td>
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
        <table class="table">
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
                    <td>{{ $payment->payment_number }}</td>
                    <td>{{ $payment->contract->tenant->name ?? 'غير محدد' }}</td>
                    <td>{{ $payment->contract->shop->building->name ?? 'غير محدد' }}</td>
                    <td>{{ $payment->contract->shop->shop_number ?? 'غير محدد' }}</td>
                    <td class="amount">{{ number_format($payment->paid_amount) }} ر.س</td>
                    <td>{{ $payment->payment_date->format('Y-m-d') }}</td>
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
        <table class="table">
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
                    <td>{{ $contract->contract_number }}</td>
                    <td>{{ $contract->tenant->name ?? 'غير محدد' }}</td>
                    <td>{{ $contract->shop->building->name ?? 'غير محدد' }}</td>
                    <td>{{ $contract->shop->shop_number ?? 'غير محدد' }}</td>
                    <td class="amount">{{ number_format($contract->monthly_rent) }} ر.س</td>
                    <td>{{ $contract->created_at->format('Y-m-d') }}</td>
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
        <div class="chart-placeholder">
            رسم بياني دائري للمصروفات حسب النوع
        </div>
        <table class="table">
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
                    <td>
                        <span class="badge">{{ $expense->expense_type }}</span>
                    </td>
                    <td class="expense">{{ number_format($expense->total) }} ر.س</td>
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
        <p>تم إنشاء هذا التقرير بواسطة نظام إدارة العقارات</p>
        <p>{{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>
