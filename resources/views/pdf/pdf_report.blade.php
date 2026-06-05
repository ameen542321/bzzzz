<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>{{ $report_title ?? 'تقرير إغلاق الشفت' }}</title>
    <style>
        @page { margin: 10mm 9mm 12mm; }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            direction: rtl;
            color: #0f172a;
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10.8px;
            line-height: 1.65;
            background: #eef2f7;
        }

        .report-shell {
            background: #ffffff;
            border: 1px solid #d8dee9;
            overflow: hidden;
        }

        .hero {
            position: relative;
            padding: 0;
            color: #ffffff;
            background: #0f2f4f;
            border-bottom: 4px solid #f59e0b;
        }

        .hero:before {
            content: "";
            position: absolute;
            right: 0;
            top: 0;
            width: 70%;
            height: 100%;
            background: #123c69;
        }

        .hero:after {
            content: "";
            position: absolute;
            left: -58px;
            top: -70px;
            width: 205px;
            height: 205px;
            border-radius: 105px;
            background: #1d9a8a;
            opacity: .20;
        }

        .hero-table {
            position: relative;
            z-index: 2;
            width: 100%;
            min-height: 104px;
            border-collapse: collapse;
        }

        .brand-cell {
            width: 54%;
            padding: 18px 22px 17px;
            vertical-align: middle;
        }

        .brand-layout {
            width: 100%;
            border-collapse: collapse;
        }

        .brand-icon-cell {
            width: 58px;
            vertical-align: middle;
        }

        .report-emblem {
            width: 46px;
            height: 46px;
            padding: 8px 7px;
            background: #f59e0b;
            border: 2px solid #ffd58a;
            border-radius: 14px;
        }

        .report-line {
            display: block;
            height: 4px;
            margin-bottom: 5px;
            background: #ffffff;
            border-radius: 4px;
        }

        .report-line-short { width: 62%; }
        .report-line-mid { width: 82%; }
        .report-line-wide { width: 100%; margin-bottom: 0; }

        .brand-text-cell { vertical-align: middle; }

        .project-name {
            margin-bottom: 4px;
            color: #ffe8ad;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.3px;
        }

        .report-title {
            margin: 0;
            color: #ffffff;
            font-size: 21px;
            line-height: 1.25;
            font-weight: 700;
        }

        .report-subtitle {
            margin: 5px 0 0;
            color: #dbeafe;
            font-size: 10.5px;
            font-weight: 700;
        }

        .hero-side {
            width: 46%;
            padding: 14px 18px 14px 22px;
            vertical-align: middle;
        }

        .meta-card {
            width: 100%;
            padding: 9px 11px 8px;
            color: #ffffff;
            background: #123c69;
            border: 1px solid #315c86;
            border-right: 4px solid #1d9a8a;
            border-radius: 12px;
        }

        .meta-card-title {
            margin-bottom: 6px;
            padding-bottom: 5px;
            color: #ffffff;
            border-bottom: 1px solid #315c86;
            font-size: 11px;
            font-weight: 700;
        }

        .meta-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-grid td {
            padding: 2px 0;
            color: #ffffff;
            font-size: 9.5px;
            font-weight: 700;
        }

        .meta-label {
            display: inline-block;
            min-width: 72px;
            color: #b9d4f1;
            font-weight: 700;
        }

        .block {
            margin: 12px 15px 0;
            padding: 0;
            border: 1px solid #dbe4ef;
            border-radius: 14px;
            overflow: hidden;
            background: #ffffff;
        }

        .block-title {
            margin: 0;
            padding: 9px 12px;
            color: #123c69;
            background: #f8fafc;
            border-bottom: 1px solid #dbe4ef;
            font-size: 13px;
            font-weight: 700;
        }

        .block-body { padding: 10px 12px 12px; }

        .cards {
            width: 100%;
            border-collapse: separate;
            border-spacing: 7px;
            margin: -7px;
        }

        .cards td {
            width: 25%;
            min-height: 68px;
            padding: 9px 10px;
            vertical-align: top;
            background: #f8fafc;
            border: 1px solid #dbe4ef;
            border-top: 4px solid #123c69;
            border-radius: 12px;
        }

        .cards tr:first-child td:nth-child(1) { border-top-color: #0f766e; }
        .cards tr:first-child td:nth-child(2) { border-top-color: #b45309; }
        .cards tr:first-child td:nth-child(3) { border-top-color: #f59e0b; }
        .cards tr:first-child td:nth-child(4) { border-top-color: #7c3aed; }
        .cards tr:nth-child(2) td:nth-child(3) { border-top-color: #047857; }

        .k-label {
            display: block;
            margin-bottom: 4px;
            color: #64748b;
            font-size: 9.2px;
            font-weight: 700;
        }

        .k-value {
            color: #0f172a;
            font-size: 13px;
            font-weight: 700;
            line-height: 1.35;
        }

        .note-box {
            margin-bottom: 10px;
            padding: 9px 10px;
            color: #334155;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-right: 5px solid #f59e0b;
            border-radius: 12px;
            font-size: 10px;
        }

        .note-info {
            background: #eff6ff;
            border-color: #bfdbfe;
            border-right-color: #3b82f6;
        }

        table.grid {
            width: 100%;
            border-collapse: collapse;
        }

        table.grid th,
        table.grid td {
            padding: 7px 8px;
            border-bottom: 1px solid #edf2f7;
            text-align: right;
            font-size: 10.2px;
            vertical-align: top;
        }

        table.grid th {
            color: #334155;
            background: #f8fafc;
            font-weight: 700;
        }

        table.grid tr:nth-child(even) td { background: #fbfdff; }
        table.grid tr:last-child td { border-bottom: 0; }

        .muted { color: #64748b; }
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }

        .badge {
            display: inline-block;
            padding: 2px 7px 3px;
            border-radius: 999px;
            font-size: 9.5px;
            font-weight: 700;
            border: 1px solid transparent;
            white-space: nowrap;
        }

        .b-cash { background: #dcfce7; color: #166534; border-color: #86efac; }
        .b-card { background: #dbeafe; color: #1e40af; border-color: #93c5fd; }
        .b-mixed { background: #f3e8ff; color: #6b21a8; border-color: #d8b4fe; }
        .b-credit { background: #fef3c7; color: #92400e; border-color: #fde68a; }
        .b-other { background: #e5e7eb; color: #374151; border-color: #d1d5db; }

        .products-list { margin: 0; padding: 0; list-style: none; }
        .products-list li { margin: 0 0 4px 0; }

        .breakdown-grid th { width: 34%; }
        .breakdown-grid td { font-weight: 700; }

        .operations-grid th,
        .operations-grid td {
            padding: 6px 5px;
            font-size: 9.2px;
        }

        .sale-products-row td {
            background: #f8fafc !important;
            border-bottom: 1px solid #dbe4ef;
        }

        .sale-products-cell {
            color: #475569;
            font-size: 9.4px;
            line-height: 1.75;
        }

        .sale-products-title {
            color: #123c69;
            font-weight: 700;
            margin-left: 6px;
        }

        .amount-positive { color: #047857; font-weight: 700; }
        .amount-negative { color: #b91c1c; font-weight: 700; }

        .status-good { color: #047857; font-weight: 700; }
        .status-bad { color: #b91c1c; font-weight: 700; }

        .footer {
            margin: 12px 15px 14px;
            padding-top: 8px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            font-size: 9px;
            text-align: center;
        }
    </style>
</head>
<body>
@php
    $dataArray = $data ?? [];

    $storeName = $dataArray['store_name'] ?? ($store->name ?? '-');
    $accountantName = $dataArray['accountant_name'] ?? ($accountant->name ?? 'غير معروف');

    $totalSales = (float) ($dataArray['total_sales'] ?? 0);
    $salesValueAtSellPrice = (float) ($dataArray['products_details']['sales_value'] ?? $totalSales);
    $costValue = (float) ($dataArray['products_details']['cost_value'] ?? 0);
    $productsProfit = (float) ($dataArray['products_details']['profit'] ?? ($salesValueAtSellPrice - $costValue));
    $cashSales = (float) ($dataArray['sales_breakdown']['cash_from_new_sales'] ?? 0);
    $cardSales = (float) ($dataArray['sales_breakdown']['card_from_new_sales'] ?? 0);
    $creditSales = (float) ($dataArray['sales_breakdown']['credit_sales'] ?? 0);
    $officialCreditSales = (float) ($dataArray['sales_breakdown']['official_credit'] ?? 0);
    $paymentGaps = (float) ($dataArray['sales_breakdown']['payment_gaps'] ?? 0);
    $internalUse = (float) ($dataArray['sales_breakdown']['internal_use'] ?? 0);
    $cashCollections = (float) ($dataArray['credit_collections']['total'] ?? 0);
    $currentPeriodCollections = (float) ($dataArray['credit_collections']['from_current_period'] ?? 0);
    $oldPeriodCollections = (float) ($dataArray['credit_collections']['from_old_period'] ?? 0);
    $outgoingTotal = (float) ($dataArray['outgoing_today']['total'] ?? 0);
    $laborTotal = (float) ($dataArray['labor_total'] ?? 0);
    $netProfit = (float) ($dataArray['net_profit'] ?? 0);

    $salesRows = $dataArray['details_tables']['all_sales'] ?? [];
    $salesRows = is_iterable($salesRows) ? $salesRows : [];
    $operationsCount = is_countable($salesRows) ? count($salesRows) : 0;

    $expensesRows = $dataArray['details_tables']['expenses_list'] ?? [];
    $expensesRows = is_iterable($expensesRows) ? $expensesRows : [];

    $withdrawalsRows = $dataArray['details_tables']['withdrawals_list'] ?? [];
    $withdrawalsRows = is_iterable($withdrawalsRows) ? $withdrawalsRows : [];

    $collectionsRows = $dataArray['details_tables']['collections'] ?? [];
    $collectionsRows = is_iterable($collectionsRows) ? $collectionsRows : [];

    $expectedCash = (float) ($dataArray['cash_details']['expected'] ?? 0);
    $actualCash = (float) ($dataArray['cash_details']['actual'] ?? 0);
    $diffCash = (float) ($dataArray['cash_details']['difference'] ?? 0);

    $paymentBadge = function ($type) {
        return match ((string) $type) {
            'cash' => ['label' => 'نقد', 'class' => 'b-cash'],
            'card' => ['label' => 'شبكة', 'class' => 'b-card'],
            'mixed' => ['label' => 'ميكس', 'class' => 'b-mixed'],
            'credit' => ['label' => 'آجل', 'class' => 'b-credit'],
            default => ['label' => ((string) $type ?: 'غير محدد'), 'class' => 'b-other'],
        };
    };
@endphp

<div class="report-shell">
    <div class="hero">
        <table class="hero-table">
            <tr>
                <td class="brand-cell">
                    <table class="brand-layout">
                        <tr>
                            <td class="brand-icon-cell">
                                <div class="report-emblem">
                                    <span class="report-line report-line-short"></span>
                                    <span class="report-line report-line-mid"></span>
                                    <span class="report-line report-line-wide"></span>
                                </div>
                            </td>
                            <td class="brand-text-cell">
                                <div class="project-name">CARLED</div>
                                <h1 class="report-title">تقرير الإقفال التفصيلي</h1>
                                <p class="report-subtitle">سجل العمليات والمصروفات ومطابقة الصندوق</p>
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="hero-side">
                    <div class="meta-card">
                        <div class="meta-card-title">بيانات التقرير</div>
                        <table class="meta-grid">
                            <tr>
                                <td><span class="meta-label">المتجر</span>{{ $storeName }}</td>
                            </tr>
                            <tr>
                                <td><span class="meta-label">المحاسب</span>{{ $accountantName }}</td>
                            </tr>
                            <tr>
                                <td><span class="meta-label">الفترة</span>{{ $dataArray['start_time'] ?? '-' }} إلى {{ $dataArray['end_time'] ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><span class="meta-label">الإصدار</span>{{ $dataArray['report_date'] ?? now()->format('Y-m-d H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="block">
        <h2 class="block-title">ملخص التقرير المفصل</h2>
        <div class="block-body">
            {{-- نعرض فقط ملاحظة الإغلاق الخاصة بالشفت، بدون أي رسائل سياسة حذف هنا. --}}
            <div class="note-box note-info">
                <strong>تنبيه مهم:</strong>
                هذا التقرير متاح لمدة 90 يوماً من تاريخ الإصدار، وبعدها قد لا يمكن الوصول إليه.
            </div>

            @if(!empty($dataArray['notes']))
                <div class="note-box">
                    <strong>ملاحظة الإغلاق:</strong>
                    {{ $dataArray['notes'] }}
                </div>
            @endif

            <table class="cards">
            <tr>
                <td><span class="k-label">قيمة المبيعات (سعر البيع)</span><span class="k-value">{{ number_format($salesValueAtSellPrice, 2) }} ر.س</span></td>
                <td><span class="k-label">قيمة التكلفة</span><span class="k-value">{{ number_format($costValue, 2) }} ر.س</span></td>
                <td><span class="k-label">كاش المبيعات</span><span class="k-value">{{ number_format($cashSales, 2) }} ر.س</span></td>
                <td><span class="k-label">شبكة المبيعات</span><span class="k-value">{{ number_format($cardSales, 2) }} ر.س</span></td>
            </tr>
            <tr>
                <td><span class="k-label">المنصرفات (مصروف + سحب)</span><span class="k-value">{{ number_format($outgoingTotal, 2) }} ر.س</span></td>
                <td><span class="k-label">أجرة اليد</span><span class="k-value">{{ number_format($laborTotal, 2) }} ر.س</span></td>
                <td><span class="k-label">صافي الربح</span><span class="k-value">{{ number_format($netProfit, 2) }} ر.س</span></td>
                <td><span class="k-label">عدد عمليات البيع</span><span class="k-value">{{ number_format($operationsCount) }}</span></td>
            </tr>
            </table>
        </div>
    </div>

    <div class="block">
        <h2 class="block-title">تفصيل المبيعات اليومية</h2>
        <div class="block-body">
            <table class="grid breakdown-grid">
                <tbody>
                    <tr>
                        <th>إجمالي المبيعات المسجلة</th>
                        <td>{{ number_format($totalSales, 2) }} ر.س</td>
                        <th>عدد عمليات البيع</th>
                        <td>{{ number_format($operationsCount) }}</td>
                    </tr>
                    <tr>
                        <th>مبيعات الكاش</th>
                        <td>{{ number_format($cashSales, 2) }} ر.س</td>
                        <th>مبيعات الشبكة</th>
                        <td>{{ number_format($cardSales, 2) }} ر.س</td>
                    </tr>
                    <tr>
                        <th>مبيعات الآجل</th>
                        <td>{{ number_format($creditSales, 2) }} ر.س</td>
                        <th>الآجل الرسمي</th>
                        <td>{{ number_format($officialCreditSales, 2) }} ر.س</td>
                    </tr>
                    <tr>
                        <th>فروقات الدفع</th>
                        <td>{{ number_format($paymentGaps, 2) }} ر.س</td>
                        <th>الاستخدام الداخلي</th>
                        <td>{{ number_format($internalUse, 2) }} ر.س</td>
                    </tr>
                    <tr>
                        <th>قيمة المنتجات بسعر البيع</th>
                        <td>{{ number_format($salesValueAtSellPrice, 2) }} ر.س</td>
                        <th>تكلفة المنتجات بسعر التكلفة</th>
                        <td>{{ number_format($costValue, 2) }} ر.س</td>
                    </tr>
                    <tr>
                        <th>ربح المنتجات</th>
                        <td class="{{ $productsProfit >= 0 ? 'amount-positive' : 'amount-negative' }}">{{ number_format($productsProfit, 2) }} ر.س</td>
                        <th>أجرة اليد</th>
                        <td>{{ number_format($laborTotal, 2) }} ر.س</td>
                    </tr>
                    <tr>
                        <th>تحصيلات المديونيات</th>
                        <td>{{ number_format($cashCollections, 2) }} ر.س</td>
                        <th>من نفس الفترة / سابق</th>
                        <td>{{ number_format($currentPeriodCollections, 2) }} / {{ number_format($oldPeriodCollections, 2) }} ر.س</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="block">
        <h2 class="block-title">سجل عمليات البيع التفصيلي</h2>
        <div class="block-body">
        <table class="grid operations-grid">
            <thead>
                <tr>
                    <th class="text-center" style="width: 42px;">#</th>
                    <th class="text-center" style="width: 58px;">الوقت</th>
                    <th class="text-center" style="width: 66px;">الدفع</th>
                    <th style="width: 70px;">الموظف</th>
                    <th style="width: 70px;">المحاسب</th>
                    <th class="text-center" style="width: 54px;">منتجات</th>
                    <th class="text-center" style="width: 66px;">الإجمالي</th>
                    <th class="text-center" style="width: 66px;">التكلفة</th>
                    <th class="text-center" style="width: 66px;">الربح</th>
                    <th class="text-center" style="width: 62px;">أجرة اليد</th>
                    <th class="text-center" style="width: 66px;">المستلم</th>
                </tr>
            </thead>
            <tbody>
            @forelse($salesRows as $sale)
                @php
                    $sale = (array) $sale;
                    $badge = $paymentBadge($sale['type'] ?? '');
                    $products = $sale['products'] ?? [];
                    $hasProducts = is_array($products) && count($products) > 0;
                    $hasLaborOnly = !$hasProducts && ((float)($sale['labor_total'] ?? 0) > 0);
                    $saleProfit = (float) ($sale['profit'] ?? 0);
                @endphp
                <tr>
                    <td class="text-center">{{ $sale['id'] ?? '-' }}</td>
                    <td class="text-center">{{ $sale['time'] ?? '--' }}</td>
                    <td class="text-center"><span class="badge {{ $badge['class'] }}">{{ $badge['label'] }}</span></td>
                    <td>{{ $sale['employee'] ?? '---' }}</td>
                    <td>{{ $sale['accountant'] ?? '---' }}</td>
                    <td class="text-center">{{ number_format((int)($sale['products_count'] ?? ($hasProducts ? count($products) : 0))) }}</td>
                    <td class="text-center">{{ number_format((float)($sale['total'] ?? 0), 2) }}</td>
                    <td class="text-center">{{ number_format((float)($sale['cost'] ?? 0), 2) }}</td>
                    <td class="text-center {{ $saleProfit >= 0 ? 'amount-positive' : 'amount-negative' }}">{{ number_format($saleProfit, 2) }}</td>
                    <td class="text-center">{{ number_format((float)($sale['labor_total'] ?? 0), 2) }}</td>
                    <td class="text-center">{{ number_format((float)($sale['received'] ?? 0), 2) }}</td>
                </tr>
                <tr class="sale-products-row">
                    <td colspan="11" class="sale-products-cell">
                        <span class="sale-products-title">تفاصيل العملية:</span>
                        @if($hasProducts)
                            <ul class="products-list">
                                @foreach($products as $product)
                                    <li>
                                        {{ $product['name'] ?? 'منتج' }} —
                                        الكمية {{ $product['quantity'] ?? 0 }} ×
                                        سعر الوحدة {{ number_format((float)($product['price'] ?? 0), 2) }} =
                                        {{ number_format((float)($product['total'] ?? 0), 2) }} ر.س
                                        | تكلفة وحدة المخزون {{ number_format((float)($product['cost_unit_price'] ?? $product['cost_price'] ?? 0), 2) }}
                                        | إجمالي التكلفة {{ number_format((float)($product['cost_total'] ?? 0), 2) }} ر.س
                                        | الربح {{ number_format((float)($product['profit'] ?? 0), 2) }} ر.س
                                    </li>
                                @endforeach
                            </ul>
                        @elseif($hasLaborOnly)
                            <strong>شغل يد</strong>
                            <span class="muted">{{ $sale['labor_desc'] ?? 'بدون وصف' }}</span>
                        @else
                            <span class="muted">لا توجد منتجات مرتبطة بهذه العملية.</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center muted">لا توجد عمليات بيع ضمن الفترة المحددة.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="block">
        <h2 class="block-title">سجل المصروفات</h2>
        <div class="block-body">
        <table class="grid">
            <thead>
                <tr>
                    <th class="text-center" style="width: 80px;">الوقت</th>
                    <th style="width: 100px;">النوع</th>
                    <th>البيان / الملاحظة</th>
                    <th class="text-center" style="width: 100px;">المبلغ</th>
                </tr>
            </thead>
            <tbody>
            @forelse($expensesRows as $exp)
                <tr>
                    <td class="text-center">{{ $exp['time'] ?? '--' }}</td>
                    <td>{{ $exp['category'] ?? 'مصروف عام' }}</td>
                    <td>{{ $exp['reason'] ?? '—' }}</td>
                    <td class="text-center">{{ number_format((float)($exp['amount'] ?? 0), 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center muted">لا توجد مصروفات ضمن هذه الفترة.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="block">
        <h2 class="block-title">مطابقة الصندوق</h2>
        <div class="block-body">
        <table class="grid">
            <tbody>
                <tr>
                    <td style="width: 220px;"><strong>الكاش المتوقع</strong></td>
                    <td>{{ number_format($expectedCash, 2) }} ر.س</td>
                </tr>
                <tr>
                    <td><strong>الكاش الفعلي المسلم</strong></td>
                    <td>{{ number_format($actualCash, 2) }} ر.س</td>
                </tr>
                <tr>
                    <td><strong>الحالة</strong></td>
                    <td class="{{ $diffCash == 0 ? 'status-good' : ($diffCash > 0 ? 'status-good' : 'status-bad') }}">
                        @if($diffCash == 0)
                            مطابق تمامًا
                        @elseif($diffCash > 0)
                            فائض {{ number_format($diffCash, 2) }} ر.س
                        @else
                            عجز {{ number_format(abs($diffCash), 2) }} ر.س
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
        </div>
    </div>

    @if(is_countable($collectionsRows) && count($collectionsRows) > 0)
    <div class="block">
        <h2 class="block-title">تحصيلات المديونيات</h2>
        <div class="block-body">
        <table class="grid">
            <thead>
                <tr>
                    <th class="text-center" style="width: 80px;">الوقت</th>
                    <th>الاسم</th>
                    <th style="width: 120px;">النوع</th>
                    <th class="text-center" style="width: 100px;">المبلغ</th>
                </tr>
            </thead>
            <tbody>
            @foreach($collectionsRows as $col)
                <tr>
                    <td class="text-center">{{ isset($col['collection_date']) ? \Carbon\Carbon::parse($col['collection_date'])->format('h:i A') : '--' }}</td>
                    <td>{{ $col['employee_name'] ?? '—' }}</td>
                    <td>{{ ($col['type'] ?? '') === 'current' ? 'من شفت اليوم' : 'مديونية سابقة' }}</td>
                    <td class="text-center">{{ number_format((float)($col['collected_in_shift'] ?? 0), 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
    </div>
    @endif

    @if(is_countable($withdrawalsRows) && count($withdrawalsRows) > 0)
    <div class="block">
        <h2 class="block-title">سجل السحوبات</h2>
        <div class="block-body">
        <table class="grid">
            <thead>
                <tr>
                    <th class="text-center" style="width: 80px;">الوقت</th>
                    <th>البيان</th>
                    <th class="text-center" style="width: 100px;">المبلغ</th>
                </tr>
            </thead>
            <tbody>
            @foreach($withdrawalsRows as $w)
                <tr>
                    <td class="text-center">{{ $w['time'] ?? '--' }}</td>
                    <td>{{ $w['reason'] ?? 'سحب نقدي' }}</td>
                    <td class="text-center">{{ number_format((float)($w['amount'] ?? 0), 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
    </div>
    @endif

    <div class="footer">
        تم إنشاء هذا التقرير آلياً من CARLED — تقرير الإقفال التفصيلي
    </div>
</div>
</body>
</html>
