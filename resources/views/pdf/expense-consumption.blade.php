<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Amiri', 'Cairo', 'DejaVu Sans', sans-serif; font-size: 12px; color: #222; }
        h1, h2 { margin: 0 0 8px 0; }
        .muted { color: #666; font-size: 11px; }
        .stats { margin: 12px 0; }
        .stats td { padding: 6px 10px; border: 1px solid #ddd; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 7px; text-align: right; }
        th { background: #f5f5f5; }
        .badge-op { color: #1d4ed8; font-weight: bold; }
        .badge-owner { color: #047857; font-weight: bold; }
    </style>
</head>
<body>
    <h1>تقرير الاستهلاك الشهري</h1>
    <p class="muted">المتجر: {{ $store->name ?? '---' }}</p>
    <p class="muted">الفترة: {{ $year }}-{{ str_pad((string)$month, 2, '0', STR_PAD_LEFT) }}</p>
    <p class="muted">تاريخ التوليد: {{ $generatedAt }}</p>

    <table class="stats">
        <tr>
            <td>إجمالي الاستهلاك</td>
            <td><strong>{{ number_format($total, 2) }} ر.س</strong></td>
            <td>تشغيلي</td>
            <td><strong>{{ number_format($operationalTotal, 2) }} ر.س</strong></td>
            <td>مشتريات مالك مباشرة</td>
            <td><strong>{{ number_format($ownerPurchasesTotal, 2) }} ر.س</strong></td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>النوع</th>
                <th>المصدر</th>
                <th>الوصف</th>
                <th>المبلغ</th>
                <th>التاريخ</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $index => $expense)
                @php $ownerPurchase = $expense->actor_type === 'owner_purchase'; @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $expense->type }}</td>
                    <td class="{{ $ownerPurchase ? 'badge-owner' : 'badge-op' }}">{{ $ownerPurchase ? 'مشتريات مالك' : 'تشغيلي' }}</td>
                    <td>{{ $expense->description ?: '-' }}</td>
                    <td>{{ number_format($expense->amount, 2) }} ر.س</td>
                    <td>{{ $expense->created_at->format('Y-m-d') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center; color:#666;">لا توجد بيانات لهذه الفترة</td>
                </tr>
            @endforelse
        </tbody>
    </table>


    @if(($ownerPurchaseGroups ?? collect())->count() > 0)
    <h2 style="margin-top:14px;">تجميع المشتريات المتكررة (المالك)</h2>
    <table>
        <thead>
            <tr>
                <th>البند</th>
                <th>عدد العمليات</th>
                <th>الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ownerPurchaseGroups as $group)
                <tr>
                    <td>{{ $group['name'] }}</td>
                    <td>{{ $group['count'] }}</td>
                    <td>{{ number_format($group['total'], 2) }} ر.س</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif

</body>
</html>
