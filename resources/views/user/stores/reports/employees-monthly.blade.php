@extends('dashboard.app')

@section('title', 'تقرير الموظفين الشهري - ' . $store->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6 text-right" dir="rtl">
    <div class="mb-6 flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-white">تقرير الموظفين الشهري</h1>
            <p class="text-gray-400 text-sm mt-1">{{ $store->name }}</p>
        </div>
        <a href="{{ route('user.stores.reports.index', $store->id) }}" class="text-sm bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
            العودة لمركز التقارير
        </a>
    </div>

    {{-- فلترة التقرير حسب شهر محدد --}}
    <form method="GET" class="mb-4 flex items-end gap-3 flex-wrap">
        <div>
            <label class="block text-xs text-gray-400 mb-1">الشهر</label>
            <input type="month" name="month" value="{{ $month }}" class="bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm">
        </div>
        <button class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg text-sm">عرض</button>
    </form>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 mb-4 text-sm">
        <div class="bg-gray-900/40 border border-gray-700 rounded-xl p-3"><p class="text-gray-400">إجمالي الرواتب</p><p class="text-indigo-300 font-bold">{{ number_format($totals['salary'], 2) }} ر.س</p></div>
        <div class="bg-gray-900/40 border border-gray-700 rounded-xl p-3"><p class="text-gray-400">إجمالي السحوبات</p><p class="text-yellow-300 font-bold">{{ number_format($totals['withdrawals'], 2) }} ر.س</p></div>
        <div class="bg-gray-900/40 border border-gray-700 rounded-xl p-3"><p class="text-gray-400">إجمالي المديونية</p><p class="text-rose-300 font-bold">{{ number_format($totals['debts'], 2) }} ر.س</p></div>
        <div class="bg-gray-900/40 border border-gray-700 rounded-xl p-3"><p class="text-gray-400">أيام الغياب</p><p class="text-orange-300 font-bold">{{ number_format($totals['absences_count']) }}</p></div>
        <div class="bg-gray-900/40 border border-gray-700 rounded-xl p-3"><p class="text-gray-400">صافي الرواتب</p><p class="text-emerald-300 font-bold">{{ number_format($totals['net_salary'], 2) }} ر.س</p></div>
    </div>

    <div class="bg-gray-900/40 border border-gray-700 rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-800/80 text-gray-300">
                <tr>
                    <th class="p-3 text-right">#</th>
                    <th class="p-3 text-right">الموظف</th>
                    <th class="p-3 text-right">الحالة</th>
                    <th class="p-3 text-right">الراتب</th>
                    <th class="p-3 text-right">السحوبات</th>
                    <th class="p-3 text-right">المديونية</th>
                    <th class="p-3 text-right">الغياب</th>
                    <th class="p-3 text-right">صافي الراتب</th>
                    <th class="p-3 text-right">التصدير</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr class="border-t border-gray-700/70 text-gray-200">
                        <td class="p-3">{{ $row['id'] }}</td>
                        <td class="p-3">{{ $row['name'] }}</td>
                        <td class="p-3">{{ $row['status'] ?? '—' }}</td>
                        <td class="p-3">{{ number_format($row['salary'], 2) }} ر.س</td>
                        <td class="p-3">{{ number_format($row['withdrawals'], 2) }} ر.س</td>
                        <td class="p-3">{{ number_format($row['debts'], 2) }} ر.س</td>
                        <td class="p-3">{{ number_format($row['absences_count']) }}</td>
                        <td class="p-3 font-bold text-emerald-300">{{ number_format($row['net_salary'], 2) }} ر.س</td>
                        <td class="p-3">
                            {{-- تصدير تقرير PDF للموظف المحدد مع نفس الشهر المختار في الفلتر --}}
                            <a href="{{ route('user.stores.reports.employees.pdf', ['store' => $store->id, 'employee' => $row['id'], 'month' => $month]) }}"
                               class="bg-red-600 hover:bg-red-500 text-white px-3 py-1 rounded-lg text-xs">
                                PDF
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="p-6 text-center text-gray-400">لا يوجد موظفون أو بيانات لهذا الشهر.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
