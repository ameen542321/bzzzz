<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>تقرير الاستهلاك الشهري</title>
</head>
{{-- 2026-05-16: تم نقل تنسيقات هذا التقرير إلى inline styles حتى لا تظهر أكواد CSS كنص داخل PDF عند توليده عبر mPDF. --}}
<body style="direction: rtl; text-align: right; margin: 0; padding: 12mm; font-family: 'Amiri', 'Cairo', 'DejaVu Sans', sans-serif; font-size: 11px; color: #333; box-sizing: border-box;">
<div style="border-bottom: 2px solid #333; padding-bottom: 8px; margin-bottom: 16px;">
    <p style="font-size: 18px; font-weight: bold; margin: 0 0 3px;">تقرير الاستهلاك الشهري - {{ $store->name }}</p>
    <p style="font-size: 11px; color: #666; margin: 0;">الشهر: {{ str_pad($month, 2, '0', STR_PAD_LEFT) }}/{{ $year }} | الفترة: {{ $reportData['startDate'] }} إلى {{ $reportData['endDate'] }}</p>
</div>

<table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
    <tr>
        <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">
            <div style="font-size: 10px; color: #666;">استهلاك المحاسب</div>
            <div style="font-size: 14px; font-weight: bold;">{{ number_format($reportData['summary']['accountant_total'], 2) }} ر.س</div>
        </td>
        <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">
            <div style="font-size: 10px; color: #666;">مشتريات المالك للاستهلاك</div>
            <div style="font-size: 14px; font-weight: bold;">{{ number_format($reportData['summary']['owner_total'], 2) }} ر.س</div>
        </td>
        <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">
            <div style="font-size: 10px; color: #666;">الإجمالي</div>
            <div style="font-size: 14px; font-weight: bold;">{{ number_format($reportData['summary']['grand_total'], 2) }} ر.س</div>
        </td>
        <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">
            <div style="font-size: 10px; color: #666;">عدد العمليات</div>
            <div style="font-size: 14px; font-weight: bold;">{{ $reportData['summary']['count'] }}</div>
        </td>
    </tr>
</table>

<table style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th style="border: 1px solid #ddd; padding: 7px; font-size: 10px; background: #f4f4f4;">#</th>
            <th style="border: 1px solid #ddd; padding: 7px; font-size: 10px; background: #f4f4f4;">المصدر</th>
            <th style="border: 1px solid #ddd; padding: 7px; font-size: 10px; background: #f4f4f4;">النوع</th>
            <th style="border: 1px solid #ddd; padding: 7px; font-size: 10px; background: #f4f4f4;">الوصف</th>
            <th style="border: 1px solid #ddd; padding: 7px; font-size: 10px; background: #f4f4f4;">المبلغ</th>
            <th style="border: 1px solid #ddd; padding: 7px; font-size: 10px; background: #f4f4f4;">التاريخ</th>
        </tr>
    </thead>
    <tbody>
        @forelse($reportData['records'] as $index => $row)
            <tr>
                <td style="border: 1px solid #ddd; padding: 7px; font-size: 10px;">{{ $index + 1 }}</td>
                <td style="border: 1px solid #ddd; padding: 7px; font-size: 10px;">{{ $row['source'] }}</td>
                <td style="border: 1px solid #ddd; padding: 7px; font-size: 10px;">{{ $row['type'] }}</td>
                <td style="border: 1px solid #ddd; padding: 7px; font-size: 10px;">{{ $row['description'] }}</td>
                <td style="border: 1px solid #ddd; padding: 7px; font-size: 10px; font-weight: bold;">{{ number_format($row['amount'], 2) }} ر.س</td>
                <td style="border: 1px solid #ddd; padding: 7px; font-size: 10px;">{{ \Carbon\Carbon::parse($row['created_at'])->format('Y-m-d h:i A') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" style="text-align:center; padding: 16px; border: 1px solid #ddd; font-size: 10px;">لا توجد بيانات لهذا الشهر.</td>
            </tr>
        @endforelse
    </tbody>
</table>
</body>
</html>
