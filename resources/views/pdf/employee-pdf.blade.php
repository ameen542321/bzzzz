<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">

    <style>
        body {
            font-family: 'Amiri', 'Cairo', serif;
            direction: rtl;
            text-align: right;
            font-size: 15px;
            line-height: 1.9;
            color: #222;
            padding: 25px;
        }

        /* الشعار + العنوان */
        .report-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .report-header .logo {
            font-size: 32px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .report-header .employee-name {
            font-size: 22px;
            font-weight: bold;
            margin-top: 10px;
            color: #444;
        }

        .report-header .title {
            font-size: 18px;
            margin-top: 5px;
            color: #666;
        }

        h2 {
            margin: 35px 0 15px;
            font-size: 20px;
            font-weight: bold;
            border-right: 5px solid #444;
            padding-right: 12px;
            color: #222;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 35px;
            font-size: 14px;
        }

        th, td {
            border: 1px solid #bbb;
            padding: 10px;
        }

        th {
            background: #f7f7f7;
            font-weight: bold;
            color: #333;
            text-align: center;
        }

        tbody tr:nth-child(even) {
            background: #fafafa;
        }

        tbody tr:nth-child(odd) {
            background: #ffffff;
        }

        tbody td {
            text-align: center;
        }

        .section {
            margin-bottom: 45px;
        }

        .footer {
            margin-top: 50px;
            font-size: 14px;
            text-align: center;
            border-top: 1px solid #ccc;
            padding-top: 10px;
            color: #444;
        }

        /* ألوان المديونية */
        .debt-add { color: #dc2626; font-weight:bold; }
        .debt-partial { color: #059669; font-weight:bold; }
        .debt-single { color: #10b981; font-weight:bold; }
        .debt-full { color: #065f46; font-weight:bold; }
    </style>

</head>

<body>

    <!-- الشعار + اسم الموظف + عنوان التقرير -->
    <div class="report-header">
        <div class="logo">{{ config('app.name', 'Carled') }}</div>
        <div class="employee-name">{{ $person->name }}</div>
        <div class="title">تقرير مفصل</div>
        <div class="title">الفترة: {{ $report_month }}</div>
    </div>

    <!-- بيانات العامل -->
    <div class="section">
        <h2>1. بيانات العامل</h2>
        <table>
            <tr><th>#</th><th>الحقل</th><th>القيمة</th></tr>
            <tr><td>1</td><td>اسم العامل</td><td>{{ $person->name }}</td></tr>
            <tr><td>2</td><td>رقم الجوال</td><td>{{ $person->phone ?? '—' }}</td></tr>
            <tr><td>3</td><td>الراتب الشهري</td><td>{{ number_format($person->salary, 2) }} ريال</td></tr>
            <tr><td>4</td><td>المتجر</td><td>{{ $person->store->name }}</td></tr>
            <tr><td>5</td><td>اسم المالك</td><td>{{ $person->store->user->name }}</td></tr>
        </table>
    </div>

    <!-- السحوبات -->
    <div class="section">
        <h2>2. السحوبات</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>المبلغ</th>
                    <th>التاريخ</th>
                    <th>الوصف</th>
                </tr>
            </thead>
            <tbody>
                @forelse($withdrawals as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ number_format($item->amount, 2) }}</td>
                        <td>{{ $item->date }}</td>
                        <td>{{ $item->description ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">لا توجد سحوبات</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- الغياب -->
    <div class="section">
        <h2>3. الغياب</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>التاريخ</th>
                    <th>الوصف</th>
                    <th>سجّل بواسطة</th>
                </tr>
            </thead>
            <tbody>
                @forelse($absences as $index => $absence)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $absence->date }}</td>
                        <td>{{ $absence->description ?? '—' }}</td>
                        <td>{{ $absence->addedBy->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">لا يوجد غياب</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- المديونية -->
    <div class="section">
        <h2>4. المديونية</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>النوع</th>
                    <th>المبلغ</th>
                    <th>الوصف</th>
                    <th>التاريخ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($debts as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>

                        <!-- نوع العملية -->
                        <td>
                            @php
                                $debtType = $item->log_type ?? $item->action_name ?? $item->type ?? null;
                            @endphp
                            @switch($debtType)
                                @case('debt_add') <span class="debt-add">إضافة</span> @break
                                @case('debt_collect_partial') <span class="debt-partial">تحصيل جزئي</span> @break
                                @case('debt_collect_single') <span class="debt-single">تحصيل عملية</span> @break
                                @case('debt_collect_full') <span class="debt-full">تحصيل كامل</span> @break
                                @default <span class="debt-add">مديونية</span>
                            @endswitch
                        </td>

                        <td>{{ number_format($item->amount, 2) }}</td>
                        <td>{{ $item->description ?? '—' }}</td>
                        <td>{{ $item->date }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">لا توجد مديونية</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- بيع آجل غير محصّل -->
    <div class="section">
        <h2>5. بيع آجل غير محصّل</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>المبلغ</th>
                    <th>الوصف</th>
                    <th>التاريخ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($creditSalesPending as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ number_format($item->amount, 2) }}</td>
                        <td>{{ $item->description ?? '—' }}</td>
                        <td>{{ $item->date }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">لا توجد عمليات بيع آجل غير محصّلة</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- بيع آجل محصّل -->
    <div class="section">
        <h2>6. بيع آجل محصّل</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>المبلغ</th>
                    <th>الوصف</th>
                    <th>التاريخ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($creditSalesCollected as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ number_format($item->amount, 2) }}</td>
                        <td>{{ $item->description ?? '—' }}</td>
                        <td>{{ $item->date }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">لا توجد عمليات بيع آجل محصّلة</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        تم إنشاء التقرير بواسطة: {{ $created_by->name }}
    </div>

</body>
</html>
