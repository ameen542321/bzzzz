@extends('dashboard.app')

@section('title', 'تقارير آخر 10 أيام - ' . $store->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6 text-right" dir="rtl">
    <div class="mb-6 flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-white">تقارير مبيعات آخر 10 أيام</h1>
            <p class="text-gray-400 text-sm mt-1">{{ $store->name }} — من {{ $cutoffDate->format('Y-m-d') }} إلى اليوم</p>
        </div>
        <a href="{{ route('user.stores.reports.index', $store->id) }}" class="text-sm bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
            العودة لمركز التقارير
        </a>
    </div>

        {{-- صفحة عرض فقط: سياسة حذف التقارير تُدار من نظام خارجي منفصل. --}}
    <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-3 mb-4 text-blue-200 text-sm">
        تنبيه: هذه الملفات قد تُحذف تلقائياً بعد مرور 90 يوماً حسب سياسة النظام.
    </div>

    <div class="bg-gray-900/40 border border-gray-700 rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-800/80 text-gray-300">
                <tr>
                    <th class="p-3 text-right">اسم التقرير</th>
                    <th class="p-3 text-right">تاريخ الإنشاء</th>
                    <th class="p-3 text-right">الحجم</th>
                    <th class="p-3 text-right">الإجراء</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                    <tr class="border-t border-gray-700/70 text-gray-200">
                        <td class="p-3">{{ $report['name'] }}</td>
                        <td class="p-3">{{ $report['created_at']->format('Y-m-d h:i A') }}</td>
                        <td class="p-3">{{ number_format($report['size_kb'], 2) }} KB</td>
                        <td class="p-3">
                            <a href="{{ $report['url'] }}" target="_blank" class="bg-green-600 hover:bg-green-500 text-white px-3 py-1.5 rounded-lg text-xs">
                                فتح التقرير
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-6 text-center text-gray-400">لا توجد تقارير خلال آخر 10 أيام.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
