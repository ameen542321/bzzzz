<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>التقرير الشهري - {{ $store->name }}</title>
    <style>
        @page { margin: 16mm 13mm; }

        :root {
            --ink: #111827;
            --muted: #6b7280;
            --line: #d9dde5;
            --panel: #f7f9fc;
            --accent: #2563eb;
            --ok: #047857;
            --bad: #b91c1c;
        }

        body {
            margin: 0;
            color: var(--ink);
            direction: rtl;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11.5px;
            line-height: 1.65;
            background: #fff;
        }

        .report-shell {
            border: 1px solid var(--line);
            border-radius: 12px;
            overflow: hidden;
        }

        .report-head {
            padding: 14px 16px 12px;
            border-bottom: 1px solid var(--line);
            background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
        }

        .title {
            margin: 0;
            font-size: 19px;
            font-weight: 700;
            color: #0f172a;
        }

        .meta {
            margin-top: 9px;
            width: 100%;
            border-collapse: collapse;
        }

        .meta td {
            width: 50%;
            padding: 6px 8px;
            border: 1px solid var(--line);
            background: #fff;
            font-size: 10.5px;
        }

        .meta strong { color: #374151; }

        .body-wrap { padding: 12px; }

        .kpi-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;
            margin-bottom: 8px;
        }

        .kpi-grid td {
            width: 25%;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: var(--panel);
            padding: 8px 9px;
            vertical-align: top;
        }

        .kpi-label {
            color: var(--muted);
            font-size: 10px;
            margin-bottom: 3px;
        }

        .kpi-number {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
        }

        .section {
            margin-top: 8px;
            border: 1px solid var(--line);
            border-radius: 10px;
            overflow: hidden;
        }

        .section-h {
            padding: 8px 11px;
            background: #eff6ff;
            border-bottom: 1px solid var(--line);
            color: var(--accent);
            font-size: 12px;
            font-weight: 700;
        }

        .tbl {
            width: 100%;
            border-collapse: collapse;
        }

        .tbl th,
        .tbl td {
            border-bottom: 1px solid #eceff4;
            padding: 8px 10px;
            font-size: 11px;
            text-align: right;
        }

        .tbl th {
            width: 60%;
            color: #475569;
            font-weight: 600;
            background: #fcfdff;
        }

        .tbl tr:last-child th,
        .tbl tr:last-child td { border-bottom: 0; }

        .total-row {
            background: #f8fafc;
            font-weight: 700;
            border-top: 2px solid #d6dbe5;
        }

        .positive { color: var(--ok); }
        .negative { color: var(--bad); }

        .hint {
            margin-top: 10px;
            padding: 7px 10px;
            border-right: 3px solid var(--accent);
            background: #f8fbff;
            color: #334155;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="report-shell">
        <div class="report-head">
            <h1 class="title">التقرير الشهري للمتجر</h1>
            <table class="meta">
                <tr>
                    <td><strong>المتجر:</strong> {{ $store->name }}</td>
                    <td><strong>الشهر:</strong> {{ $month }}</td>
                </tr>
            </table>
        </div>

        <div class="body-wrap">
            <table class="kpi-grid">
                <tr>
                    <td>
                        <div class="kpi-label">إجمالي المبيعات</div>
                        <div class="kpi-number">{{ number_format($totalSales, 2) }} ر.س</div>
                    </td>
                    <td>
                        <div class="kpi-label">عدد العمليات</div>
                        <div class="kpi-number">{{ number_format($operationsCount) }}</div>
                    </td>
                    <td>
                        <div class="kpi-label">الكاش</div>
                        <div class="kpi-number">{{ number_format($cashSales, 2) }} ر.س</div>
                    </td>
                    <td>
                        <div class="kpi-label">الشبكة</div>
                        <div class="kpi-number">{{ number_format($cardSales, 2) }} ر.س</div>
                    </td>
                </tr>
            </table>

            <div class="section">
                <div class="section-h">التكاليف والاستهلاك</div>
                <table class="tbl">
                    <tr>
                        <th>الاستهلاك الداخلي (المحاسب)</th>
                        <td>{{ number_format($internalUseSales, 2) }} ر.س</td>
                    </tr>
                    <tr>
                        <th>مشتريات المالك للاستهلاك</th>
                        <td>{{ number_format($ownerPurchases, 2) }} ر.س</td>
                    </tr>
                    <tr>
                        <th>إجمالي الاستهلاك</th>
                        <td>{{ number_format($totalConsumption, 2) }} ر.س</td>
                    </tr>
                    <tr>
                        <th>تكلفة المنتجات المباعة (تخصم من الربح)</th>
                        <td>{{ number_format($profitDeductionTotal ?? $monthlySoldProductsCost, 2) }} ر.س</td>
                    </tr>
                    <tr>
                        <th>المصروفات</th>
                        <td>{{ number_format($expensesTotal, 2) }} ر.س</td>
                    </tr>
                    <tr>
                        <th>الرواتب الشهرية (للتوضيح فقط)</th>
                        <td>{{ number_format($monthlySalaries, 2) }} ر.س</td>
                    </tr>
                    <tr>
                        <th>سحبيات الموظفين (للتوضيح فقط)</th>
                        <td>{{ number_format($withdrawalsTotal, 2) }} ر.س</td>
                    </tr>
                    <tr class="total-row">
                        <th>{{ $netAfterCosts < 0 ? 'الخسارة بعد التكاليف' : 'صافي الربح بعد التكاليف' }}</th>
                        <td class="{{ $netAfterCosts >= 0 ? 'positive' : 'negative' }}">
                            @if($netAfterCosts < 0)
                                خسارة بمقدار {{ number_format(abs($netAfterCosts), 2) }} ر.س
                            @else
                                أرباح صافية بمقدار {{ number_format($netAfterCosts, 2) }} ر.س
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            <div class="hint">
                طريقة الحساب: صافي النتيجة = المحصل - (تكلفة المنتجات المباعة + الاستهلاك الداخلي + مشتريات المالك للاستهلاك + المصروفات). تكلفة المنتجات المباعة هي تكلفة البضاعة التي تم بيعها وليست خصماً منفصلاً، والرواتب وسحبيات الموظفين قيم توضيحية فقط ولا تدخل في معادلة الربح.
            </div>
        </div>
    </div>
</body>
</html>
