<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">

    <style>
        body {
            font-family: 'Amiri', 'Cairo', serif;
            direction: rtl;
            text-align: right;
            background: #f8f8f8;
            margin: 0;
            padding: 25px;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
        }

        .logo-text {
            font-size: 28px;
            font-weight: bold;
            color: #4f46e5;
            margin-bottom: 5px;
        }

        .title {
            font-size: 22px;
            font-weight: bold;
            margin: 0;
            color: #222;
        }

        .month-title {
            font-size: 20px;
            font-weight: bold;
            margin: 25px 0 10px;
            color: #444;
            border-bottom: 2px solid #ddd;
            padding-bottom: 5px;
        }

        .stats-box {
            background: #ffffff;
            padding: 12px 15px;
            border-radius: 10px;
            border: 1px solid #ddd;
            margin-bottom: 15px;
            font-size: 15px;
        }

        .log-card {
            background: #ffffff;
            padding: 18px 20px;
            border-radius: 10px;
            border: 1px solid #ddd;
            margin-bottom: 15px;
        }

        .log-type {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .log-desc {
            font-size: 15px;
            color: #444;
            margin-bottom: 6px;
        }

        .log-date {
            font-size: 13px;
            color: #777;
        }

        /* ألوان أنواع العمليات الأساسية */
        .withdrawal { color: #2563eb; }
        .absence { color: #d97706; }
        .debt { color: #dc2626; }
        .credit_sale { color: #7c3aed; }
        .credit_sale_deducted { color: #059669; }
        .store_transfer { color: #4f46e5; }
        .salary_update { color: #374151; }

        /* أنواع المديونية الجديدة */
        .debt-add { color: #dc2626; font-weight:bold; }
        .debt-partial { color: #059669; font-weight:bold; }
        .debt-single { color: #10b981; font-weight:bold; }
        .debt-full { color: #065f46; font-weight:bold; }
    </style>
</head>

<body>

    <!-- الشعار النصي -->
    <div class="header">
        <div class="logo-text">CARLED</div>
        <h1 class="title">سجل العمليات — {{ $person->name }}</h1>
    </div>

    <!-- بيانات العامل -->
    <div style="background:#fff; padding:15px 20px; border-radius:10px; border:1px solid #ddd; margin-bottom:25px;">
        <p><strong>الاسم:</strong> {{ $person->name }}</p>
        <p><strong>المتجر:</strong> {{ $person->store->name }}</p>
        <p><strong>الراتب:</strong> {{ number_format($person->salary, 2) }} ريال</p>
    </div>

    <!-- تجميع حسب الشهر -->
    @foreach ($logs as $month => $monthLogs)

        <!-- عنوان الشهر -->
        <div class="month-title">
            {{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}
        </div>

        <!-- العمليات -->
        @foreach ($monthLogs as $log)
            <div class="log-card">

                <div class="log-type">
                    @switch($log->type)

                        @case('withdrawal')
                            <span class="withdrawal">سحب</span>
                        @break

                        @case('absence')
                            <span class="absence">غياب</span>
                        @break

                        @case('debt')
                            <span class="debt">مديونية</span>
                        @break

                        @case('debt_add')
                            <span class="debt-add">إضافة مديونية</span>
                        @break

                        @case('debt_collect_partial')
                            <span class="debt-partial">تحصيل جزئي</span>
                        @break

                        @case('debt_collect_single')
                            <span class="debt-single">تحصيل عملية واحدة</span>
                        @break

                        @case('debt_collect_full')
                            <span class="debt-full">تحصيل كامل</span>
                        @break

                        @case('credit_sale')
                            <span class="credit_sale">بيع آجل</span>
                        @break

                        @case('credit_sale_deducted')
                            <span class="credit_sale_deducted">تحصيل بيع آجل</span>
                        @break

                        @case('store_transfer')
                            <span class="store_transfer">نقل بين المتاجر</span>
                        @break

                        @case('salary_update')
                            <span class="salary_update">تعديل راتب</span>
                        @break

                    @endswitch
                </div>

                <div class="log-desc">
                    {{ $log->description }}
                </div>

                <div class="log-date">
                    {{ $log->logged_at->format('Y-m-d H:i') }}
                </div>

            </div>
        @endforeach

    @endforeach

    <!-- الإحصائيات النهائية -->
    <div style="margin-top:40px; font-size:20px; font-weight:bold; color:#333;">
        الإحصائيات الشهرية
    </div>

    @foreach ($stats as $month => $data)
        <div style="background:#fff; padding:15px 20px; border-radius:10px; border:1px solid #ddd; margin-top:15px;">
            <div style="font-size:17px; font-weight:bold; margin-bottom:8px;">
                {{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}
            </div>

            <div style="font-size:15px; color:#444;">
                - مجموع السحوبات: {{ number_format($data['withdrawal'], 2) }} ريال<br>
                - مجموع المديونيات: {{ number_format($data['debt'], 2) }} ريال<br>
                - مجموع البيع الآجل: {{ number_format($data['credit_sale'], 2) }} ريال<br>
                - مجموع التحصيل: {{ number_format($data['credit_sale_deducted'], 2) }} ريال
            </div>
        </div>
    @endforeach

</body>
</html>
