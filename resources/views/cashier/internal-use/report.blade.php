@extends('dashboard.app')
@section('title', 'تقرير الاستهلاك')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6 text-right" dir="rtl">
    @if(session('success'))
        <div class="mb-3 p-3 bg-green-500/10 border border-green-500/50 rounded-lg text-green-400 text-sm">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-3 p-3 bg-red-500/10 border border-red-500/50 rounded-lg text-red-400 text-sm">⚠️ {{ session('error') }}</div>
    @endif

    <div class="mb-5 bg-gray-800/50 p-4 rounded-2xl border border-gray-700">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-3">
            <div>
                <h1 class="text-2xl font-bold text-white">تقرير الاستهلاك</h1>
                <p class="text-gray-400 text-sm mt-1">{{ $store->name ?? 'المتجر' }} - الاستهلاك منفصل عن المصاريف المالية</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('user.stores.show', $storeId) }}" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-xl text-sm">رجوع للمتجر</a>
                <a href="{{ route('user.stores.internal-use.export-pdf', ['store' => $storeId, 'month' => $month, 'year' => $year]) }}" class="bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded-xl text-sm">تصدير PDF</a>
            </div>
        </div>
    </div>

    <div class="mb-4 bg-gray-800/50 p-4 rounded-2xl border border-gray-700">
        <h3 class="text-white font-bold mb-3">تسجيل مشتريات المالك للاستهلاك (بدون خصم مخزون)</h3>
        <form method="POST" action="{{ route('user.stores.internal-use.add-consumption.store', $storeId) }}" class="grid grid-cols-1 md:grid-cols-6 gap-2 items-end">
            @csrf
            <div class="md:col-span-2">
                <label class="text-xs text-gray-400 block mb-1">نوع المشتريات</label>
                <input type="text" name="type" required list="ownerPurchaseTypes" class="w-full bg-gray-900 border border-gray-700 rounded-lg py-2 px-3 text-sm text-white" placeholder="مثال: امواس / ربل / تضليل / تجليد / فيوز">
                <datalist id="ownerPurchaseTypes">
                    @foreach(($ownerPurchaseTypeOptions ?? []) as $option)
                        <option value="{{ $option }}"></option>
                    @endforeach
                </datalist>
            </div>
            <div>
                <label class="text-xs text-gray-400 block mb-1">المبلغ</label>
                <input type="number" step="0.01" min="0.01" name="amount" required class="w-full bg-gray-900 border border-gray-700 rounded-lg py-2 px-3 text-sm text-white" placeholder="0.00">
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="w-full bg-green-600 hover:bg-green-500 text-white px-4 py-2 rounded-lg text-sm">حفظ</button>
            </div>
            <div class="md:col-span-6">
                <label class="text-xs text-gray-400 block mb-1">ملاحظات</label>
                <textarea name="description" rows="2" class="w-full bg-gray-900 border border-gray-700 rounded-lg py-2 px-3 text-sm text-white" placeholder="تفاصيل إضافية عن المشتريات..."></textarea>
            </div>
        </form>
    </div>

    @if(($ownerPurchaseGroups ?? collect())->count() > 0)
    <div class="mb-4 bg-gray-800/50 p-4 rounded-2xl border border-gray-700">
        <h3 class="text-white font-bold text-sm mb-3">تجميع مشتريات المالك المتكررة</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 text-xs">
            @foreach($ownerPurchaseGroups as $group)
                <div class="bg-gray-900/60 border border-gray-700 rounded-lg p-2 flex items-center justify-between">
                    <div>
                        <p class="text-emerald-300 font-bold">{{ $group['name'] }}</p>
                        <p class="text-gray-500">عدد العمليات: {{ $group['count'] }}</p>
                    </div>
                    <p class="text-yellow-300 font-bold">{{ number_format($group['total'], 2) }} ر.س</p>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="mb-4 bg-gray-800/50 p-4 rounded-2xl border border-gray-700">
        <form method="GET" action="{{ route('user.stores.internal-use.report.view', $storeId) }}" class="flex flex-wrap items-end gap-2">
            <div>
                <label class="text-xs text-gray-400 block mb-1">الشهر</label>
                <select name="month" class="bg-gray-900 border border-gray-700 rounded-lg py-2 px-3 text-sm text-white">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" @selected((int)$month === $m)>{{ $m }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-400 block mb-1">السنة</label>
                <input type="number" name="year" value="{{ $year }}" min="2020" max="2100" class="bg-gray-900 border border-gray-700 rounded-lg py-2 px-3 text-sm text-white w-28">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg text-sm">تحديث</button>
        </form>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 mb-4 text-sm">
        <div class="bg-gray-800/70 p-3 rounded-xl border border-gray-700">
            <p class="text-gray-400 text-xs">فترة التقرير</p>
            <p class="text-white font-bold">{{ $reportData['startDate'] }} → {{ $reportData['endDate'] }}</p>
        </div>
        <div class="bg-gray-800/70 p-3 rounded-xl border border-gray-700">
            <p class="text-gray-400 text-xs">استهلاك المحاسب</p>
            <p class="text-blue-400 font-bold">{{ number_format($reportData['summary']['accountant_total'], 2) }} ر.س</p>
        </div>
        <div class="bg-gray-800/70 p-3 rounded-xl border border-gray-700">
            <p class="text-gray-400 text-xs">مشتريات المالك للاستهلاك</p>
            <p class="text-emerald-400 font-bold">{{ number_format($reportData['summary']['owner_total'], 2) }} ر.س</p>
        </div>
        <div class="bg-gray-800/70 p-3 rounded-xl border border-gray-700">
            <p class="text-gray-400 text-xs">إجمالي الاستهلاك / عدد العمليات</p>
            <p class="text-yellow-400 font-bold">{{ number_format($reportData['summary']['grand_total'], 2) }} ر.س</p>
            <p class="text-gray-500 text-xs">{{ number_format($reportData['summary']['count']) }} عملية</p>
        </div>
    </div>

    <div class="bg-gray-800/40 border border-gray-700 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right">
                <thead class="bg-gray-900/50 text-gray-400 text-xs">
                    <tr>
                        <th class="py-3 px-3">#</th>
                        <th class="py-3 px-3">المصدر</th>
                        <th class="py-3 px-3">السبب</th>
                        <th class="py-3 px-3">الملاحظات</th>
                        <th class="py-3 px-3 text-center">القيمة</th>
                        <th class="py-3 px-3">التاريخ</th>
                        <th class="py-3 px-3 text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700/40 text-sm">
                    @forelse($reportData['records'] as $i => $row)
                        <tr class="hover:bg-gray-700/20">
                            <td class="py-3 px-3 text-gray-500">{{ $i + 1 }}</td>
                            <td class="py-3 px-3 text-white">
                                @if(str_contains($row['source'], 'المحاسب'))
                                    <span class="inline-flex items-center gap-1">
                                        <span>🧾</span>
                                        <span>{{ $row['source'] }}</span>
                                    </span>
                                @elseif(str_contains($row['source'], 'المالك'))
                                    <span class="inline-flex items-center gap-1">
                                        <span>🛒</span>
                                        <span>{{ $row['source'] }}</span>
                                    </span>
                                @else
                                    {{ $row['source'] }}
                                @endif
                            </td>
                            <td class="py-3 px-3 text-gray-300">{{ $row['type'] }}</td>
                            <td class="py-3 px-3 text-gray-400">{{ $row['description'] }}</td>
                            <td class="py-3 px-3 text-center text-yellow-400 font-bold">{{ number_format($row['amount'], 2) }} ر.س</td>
                            <td class="py-3 px-3 text-gray-400">{{ \Carbon\Carbon::parse($row['created_at'])->format('Y-m-d h:i A') }}</td>
                            <td class="py-3 px-3 text-center">
                                @if(($row['entry_type'] ?? null) === 'owner_purchase')
                                    <details class="inline-block text-right">
                                        <summary class="cursor-pointer text-blue-300 text-xs">تعديل</summary>
                                        <form method="POST" action="{{ route('user.stores.internal-use.add-consumption.update', ['store' => $storeId, 'purchase' => $row['entry_id']]) }}" class="mt-2 bg-gray-900/80 border border-gray-700 rounded-lg p-2 w-60 space-y-2">
                                            @csrf
                                            @method('PUT')
                                            <input type="text" name="type" value="{{ $row['type'] }}" class="w-full bg-gray-950 border border-gray-700 rounded px-2 py-1 text-xs text-white" required>
                                            <input type="number" step="0.01" min="0.01" name="amount" value="{{ $row['amount'] }}" class="w-full bg-gray-950 border border-gray-700 rounded px-2 py-1 text-xs text-white" required>
                                            <textarea name="description" rows="2" class="w-full bg-gray-950 border border-gray-700 rounded px-2 py-1 text-xs text-white">{{ $row['description'] !== '-' ? $row['description'] : '' }}</textarea>
                                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white text-xs rounded py-1">حفظ التعديل</button>
                                        </form>
                                    </details>

                                    <form method="POST" action="{{ route('user.stores.internal-use.add-consumption.destroy', ['store' => $storeId, 'purchase' => $row['entry_id']]) }}" class="mt-2" onsubmit="return confirm('هل أنت متأكد من حذف العملية؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-300 text-xs hover:text-red-200">حذف</button>
                                    </form>
                                @elseif(($row['entry_type'] ?? null) === 'accountant_internal_use')
                                    <details class="inline-block text-right">
                                        <summary class="cursor-pointer text-indigo-300 text-xs">تعديل</summary>
                                        <form method="POST" action="{{ route('user.stores.internal-use.accountant-consumption.update', ['store' => $storeId, 'sale' => $row['entry_id']]) }}" class="mt-2 bg-gray-900/80 border border-gray-700 rounded-lg p-2 w-60 space-y-2">
                                            @csrf
                                            @method('PUT')
                                            <input type="number" step="0.01" min="0.01" name="quantity" value="{{ $row['raw_quantity'] ?? 1 }}" class="w-full bg-gray-950 border border-gray-700 rounded px-2 py-1 text-xs text-white" required>
                                            <select name="unit_type" class="w-full bg-gray-950 border border-gray-700 rounded px-2 py-1 text-xs text-white">
                                                @php $unitType = $row['raw_unit_type'] ?? 'default'; @endphp
                                                <option value="default" @selected($unitType === 'default')>افتراضي</option>
                                                <option value="meters" @selected($unitType === 'meters')>متر</option>
                                                <option value="roll" @selected($unitType === 'roll')>رول</option>
                                                <option value="piece" @selected($unitType === 'piece')>حبة</option>
                                                <option value="kit" @selected($unitType === 'kit')>طقم</option>
                                            </select>
                                            <textarea name="internal_notes" rows="2" class="w-full bg-gray-950 border border-gray-700 rounded px-2 py-1 text-xs text-white" placeholder="ملاحظات">{{ $row['description'] !== '-' ? $row['description'] : '' }}</textarea>
                                            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white text-xs rounded py-1">حفظ تعديل استهلاك المحاسب</button>
                                        </form>
                                    </details>

                                    <form method="POST" action="{{ route('user.stores.internal-use.accountant-consumption.destroy', ['store' => $storeId, 'sale' => $row['entry_id']]) }}" class="mt-2" onsubmit="return confirm('هل تريد حذف العملية واسترجاع المخزون؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-300 text-xs hover:text-rose-200">حذف مع استرجاع المخزون</button>
                                    </form>
                                @else
                                    <span class="text-gray-500 text-xs">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-gray-400">لا توجد عمليات استهلاك في هذا الشهر.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
