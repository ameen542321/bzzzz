@extends('dashboard.app')
@section('title', 'المبيعات - ' . $store->name)
@section('content')
<div class="max-w-7xl mx-auto px-4 py-6 text-right" dir="rtl">

    {{-- ===== شريط العنوان والبحث المتقدم ===== --}}
    <div class="mb-6 bg-gray-800/50 p-4 rounded-2xl border border-gray-700">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                    <i class="fas fa-chart-line text-green-500"></i>
                    @if(request('date') || request('search'))
                        نتائج البحث
                    @else
                        مبيعات الشفت اليومية
                    @endif
                </h1>
                <p class="text-gray-400 text-sm mt-1">{{ $store->name }}</p>
            </div>

            <form method="GET" action="{{ route('user.stores.daily', $store->id) }}" class="flex flex-col sm:flex-row gap-2 w-full lg:w-auto">
                <div class="relative">
                    <input type="date" name="date" value="{{ request('date', \Carbon\Carbon::today()->format('Y-m-d')) }}"
                           class="bg-gray-900 border border-gray-700 rounded-xl py-2.5 px-4 text-sm text-white w-full sm:w-auto"
                           max="{{ \Carbon\Carbon::today()->format('Y-m-d') }}">
                </div>

                <div class="relative flex-grow">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="🔍 بحث برقم العملية أو اسم المنتج..."
                           class="bg-gray-900 border border-gray-700 rounded-xl py-2.5 px-4 pr-10 text-sm text-white w-full min-w-[250px]">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500"></i>
                </div>

                <button type="submit" class="bg-green-600 hover:bg-green-500 text-white px-6 py-2.5 rounded-xl transition flex items-center gap-2 justify-center">
                    <i class="fas fa-search"></i>
                    <span>بحث</span>
                </button>

                @if(request('search') || request('date'))
                    <a href="{{ route('user.stores.daily', $store->id) }}"
                       class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2.5 rounded-xl transition flex items-center gap-2 justify-center">
                        <i class="fas fa-times"></i>
                        <span>إلغاء</span>
                    </a>
                @endif
            </form>
        </div>

        <div class="mt-3 text-sm text-gray-400 bg-gray-900/50 p-2 rounded-lg flex flex-col gap-1">
            <div class="text-[11px] text-green-300 bg-green-500/10 border border-green-500/20 rounded-md px-2 py-1">
                ✅ عرض التقرير يعتمد على الفترة المحددة (اليوم أو التاريخ المختار).
            </div>
            <div>
                <i class="fas fa-clock ml-1 text-blue-400"></i>
                فترة التقرير:
                <span class="text-gray-200">{{ $startTime->format('Y-m-d h:i A') }}</span>
                <span class="mx-1">→</span>
                <span class="text-gray-200">{{ $endTime->format('Y-m-d h:i A') }}</span>
                @if($selectedShift)
                    <span class="mr-2 text-[11px] text-green-400">(حسب الفترة المعتمدة)</span>
                    <span class="mr-2 text-[11px] text-cyan-300">عدد الفترات المعروضة: {{ $stats['shift_count'] }}</span>
                @else
                    <span class="mr-2 text-[11px] text-yellow-400">(تم اعتماد الفترة اليومية المحددة)</span>
                @endif
            </div>
            @if(request('search') || request('date'))
            <div>
                <i class="fas fa-filter ml-1 text-green-400"></i>
                @if(request('date')) <span class="ml-3">📅 التاريخ: {{ request('date') }}</span> @endif
                @if(request('search')) <span>🔍 البحث: "{{ request('search') }}"</span> @endif
            </div>
            @endif
        </div>
    </div>

    {{-- ===== كروت الإحصائيات السريعة (نسخة مصغرة) ===== --}}
    <div class="grid grid-cols-2 lg:grid-cols-7 gap-2 mb-4 text-[12px]">
        <div class="bg-gray-800/70 p-2 rounded-lg border border-gray-700">
            <p class="text-gray-400">إجمالي التحصيل (الفترة)</p>
            <p class="text-green-400 font-bold">{{ number_format($stats['collected_total'] ?? $stats['total'], 2) }} ر.س</p>
            <p class="text-[11px] text-gray-500">مجموع المبالغ المحصلة</p>
        </div>
        <div class="bg-gray-800/70 p-2 rounded-lg border border-gray-700">
            <p class="text-gray-400">تكلفة / ربح محتسب</p>
            <p class="text-yellow-400 font-bold">{{ number_format($stats['total_cost'], 2) }}</p>
            <p class="text-blue-400 font-bold">{{ number_format($stats['total_profit'], 2) }}</p>
        </div>
        <div class="bg-gray-800/70 p-2 rounded-lg border border-gray-700">
            <p class="text-gray-400">المحصل كاش / شبكة</p>
            <p class="text-emerald-400 font-bold">{{ number_format($stats['cash_sales'], 2) }}</p>
            <p class="text-cyan-400 font-bold">{{ number_format($stats['card_sales'], 2) }}</p>
        </div>
        <div class="bg-gray-800/70 p-2 rounded-lg border border-gray-700">
            <p class="text-gray-400">المصروفات + السحوبات</p>
            <p class="text-rose-400 font-bold">{{ number_format($stats['expenses'], 2) }} + {{ number_format($stats['withdrawals'], 2) }}</p>
            <p class="text-red-400 font-bold">= {{ number_format($stats['outgoing_total'], 2) }} ر.س</p>
        </div>
        <div class="bg-gray-800/70 p-2 rounded-lg border border-gray-700">
            <p class="text-gray-400">عدد العمليات / شغل يد</p>
            <p class="text-purple-400 font-bold">{{ number_format($stats['count']) }}</p>
            <p class="text-orange-400 font-bold">{{ number_format($stats['labor_count']) }}</p>
        </div>
        <div class="bg-gray-800/70 p-2 rounded-lg border border-gray-700">
            <p class="text-gray-400">سجل التضليل (خصم المنتجات)</p>
            <p class="text-cyan-300 font-bold">{{ number_format($stats['tadlil_count'] ?? 0) }} عملية</p>
            <p class="text-emerald-300 font-bold">{{ number_format($stats['tadlil_total'] ?? 0, 2) }} ر.س</p>
            <p class="text-[11px] text-gray-500">
                @if($selectedShift)
                    شفتات معتمدة
                @else
                    يومي
                @endif
                — {{ $startTime->format('Y-m-d h:i A') }} → {{ $endTime->format('Y-m-d h:i A') }}
            </p>
        </div>
        <div class="bg-gray-800/70 p-2 rounded-lg border border-gray-700">
            <p class="text-gray-400">عدد الفترات المعروضة</p>
            <p class="text-cyan-300 font-bold">{{ number_format($stats['shift_count']) }}</p>
        </div>
    </div>

    @if(($stats['deferred_profit'] ?? 0) > 0)
    <div class="mb-3 text-[11px] text-yellow-300 bg-yellow-500/10 border border-yellow-500/20 rounded-lg p-2">
        ⚠️ يوجد ربح مؤجل غير محتسب داخل هذه الصفحة بقيمة {{ number_format($stats['deferred_profit'], 2) }} ر.س حتى يكتمل تحصيل العمليات الآجلة.
    </div>
    @endif

    @if(($shiftSummaries ?? collect())->count() > 0)
    <div class="mb-5 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-2">
        @foreach($shiftSummaries as $shift)
        <div class="bg-gray-900/40 border border-gray-700 rounded-lg p-3 text-[12px]">
            @php
                $isClosedShift = \Illuminate\Support\Str::startsWith((string) ($shift['key'] ?? ''), 'shift_');
            @endphp
            <div class="flex justify-between items-center mb-2">
                <div class="flex flex-col">
                    <span class="text-white font-bold">{{ $shift['label'] }}</span>
                    @if($isClosedShift)
                        <span class="text-[9px] text-gray-500/70 tracking-wider uppercase">ref: shf-{{ str_replace('shift_', '', (string) $shift['key']) }}</span>
                    @endif
                </div>
                <span class="text-gray-400">{{ $shift['start']->format('h:i A') }} → {{ $shift['end']->format('h:i A') }}</span>
            </div>
            @if($isClosedShift)
            {{-- نستخدم URL مباشر بدل route() لتفادي تعطل الصفحة إذا كانت أسماء المسارات غير محدثة في بيئة التشغيل. --}}
            <form method="POST" action="{{ url('/user/stores/' . $store->id . '/daily-sales/shift-date') }}" class="mb-2 flex items-center gap-2 flex-wrap">
                @csrf
                @method('PUT')
                <input type="hidden" name="shift_key" value="{{ $shift['key'] }}">
                <input type="date" name="shift_date" value="{{ $shift['start']->format('Y-m-d') }}"
                       class="bg-gray-800 border border-gray-600 rounded px-2 py-1 text-[11px] text-white">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-2 py-1 rounded text-[11px]">
                    تعديل تاريخ الشفت
                </button>
                <span class="text-[10px] text-gray-400">نقل بدون دمج (لأغراض المراجعة)</span>
            </form>
            @else
            <div class="mb-2 text-[11px] text-gray-400">
                تعديل التاريخ متاح فقط للشفتات المغلقة.
            </div>
            @endif
            {{-- ملاحظة توضيحية: نعرض ملاحظة الإغلاق فقط إذا كانت موجودة فعليًا في DailyBalance. --}}
            @if(!empty($shift['notes']))
            <div class="mb-2 text-[11px] text-amber-200 bg-amber-500/10 border border-amber-500/20 rounded-md px-2 py-1">
                <span class="text-amber-300 font-semibold">ملاحظة الإغلاق:</span>
                <span>{{ $shift['notes'] }}</span>
            </div>
            @endif
            <div class="grid grid-cols-2 gap-1">
                <span class="text-gray-400">قيمة المبيعات:</span><span class="text-green-400 font-bold">{{ number_format($shift['stats']['total'], 2) }}</span>
                <span class="text-gray-400">تكلفة:</span><span class="text-yellow-400 font-bold">{{ number_format($shift['stats']['total_cost'], 2) }}</span>
                <span class="text-gray-400">ربح محتسب:</span><span class="text-blue-400 font-bold">{{ number_format($shift['stats']['total_profit'], 2) }}</span>
                <span class="text-gray-400">كاش المبيعات:</span><span class="text-emerald-400 font-bold">{{ number_format($shift['stats']['cash_sales'], 2) }}</span>
                <span class="text-gray-400">شبكة المبيعات:</span><span class="text-cyan-400 font-bold">{{ number_format($shift['stats']['card_sales'], 2) }}</span>
                @if(($shift['stats']['credit_collections'] ?? 0) > 0)
                <span class="text-gray-400">تحصيلات الآجل:</span><span class="text-amber-300 font-bold">{{ number_format($shift['stats']['credit_collections'] ?? 0, 2) }}</span>
                @endif
                <span class="text-gray-400">سجل التضليل (خصم المنتجات):</span><span class="text-cyan-300 font-bold">{{ number_format($shift['stats']['tadlil_count'] ?? 0) }} عملية</span>
                <span class="text-gray-400">إجمالي التضليل (خصم المنتجات):</span><span class="text-emerald-300 font-bold">{{ number_format($shift['stats']['tadlil_total'] ?? 0, 2) }} ر.س</span>
                <span class="text-gray-400">منصرفات:</span><span class="text-red-400 font-bold">{{ number_format($shift['stats']['outgoing_total'], 2) }}</span>
                <span class="text-gray-400">عمليات:</span><span class="text-purple-400 font-bold">{{ number_format($shift['stats']['count']) }}</span>
                @if(($shift['stats']['deferred_profit'] ?? 0) > 0)
                <span class="text-gray-400">ربح مؤجل:</span><span class="text-yellow-300 font-bold">{{ number_format($shift['stats']['deferred_profit'], 2) }}</span>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ===== بطاقات العمليات (مقسمة حسب الشفت) ===== --}}
    @php
        $groupedSales = $sales->groupBy('shift_key');
    @endphp

    <div class="space-y-6">
        @if($sales->count() > 0)
            @foreach(($shiftSummaries ?? collect()) as $shift)
                @php
                    $shiftSales = $groupedSales->get($shift['key'], collect());
                @endphp

                @if($shiftSales->count() > 0)
                <div class="bg-gray-900/20 border border-gray-700 rounded-xl p-3">
                    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                        @php
                            preg_match('/\d+/', (string) ($shift['label'] ?? ''), $shiftLabelNumberMatch);
                            $shiftNumber = $shiftLabelNumberMatch[0] ?? null;
                        @endphp
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-[10px] font-semibold text-gray-200 border border-gray-500/50 px-2.5 py-1 rounded-full">
                                {{ $shiftNumber ? 'الشفت رقم ' . $shiftNumber : 'الشفت' }}
                            </span>
                            <div class="flex flex-col">
                                <h3 class="text-sm font-bold text-white tracking-wide">{{ $shift['label'] }}</h3>
                                @if(\Illuminate\Support\Str::startsWith((string) ($shift['key'] ?? ''), 'shift_'))
                                    <span class="text-[9px] text-gray-500/70 tracking-wider uppercase">ref: shf-{{ str_replace('shift_', '', (string) $shift['key']) }}</span>
                                @endif
                            </div>
                        </div>
                        <span class="text-xs text-gray-300 bg-gray-800/80 border border-gray-600 px-2 py-1 rounded-md">{{ $shift['start']->format('h:i A') }} → {{ $shift['end']->format('h:i A') }}</span>
                    </div>
                    <div class="mb-3 px-2 py-1.5 border border-gray-300 rounded-lg text-[11px] text-gray-700">
                        <span class="font-semibold">قائمة عمليات {{ $shiftNumber ? 'الشفت رقم ' . $shiftNumber : $shift['label'] }}</span>
                        <span class="text-gray-600">({{ number_format($shiftSales->count()) }} عملية)</span>
                    </div>

                    <div class="space-y-3">
                        @foreach($shiftSales as $sale)
                        @php
                            $netProfit = $sale->recognized_profit ?? ($sale->paid_amount - $sale->total_cost);
                            $bgColor = $loop->iteration % 2 == 0 ? 'bg-gray-800/30' : 'bg-gray-800/60';
                            $isCollectionOperation = ($sale->operation_kind ?? null) === 'collection';
                            $productsCost = $sale->items->sum('calculated_cost');
                            $visibleProducts = $isCollectionOperation
                                ? ($sale->employee_name ?? 'غير معروف')
                                : $sale->items->take(2)->pluck('display_name')->filter()->implode(' - ');
                            $operationAmount = max((float) ($sale->final_total ?? 0), (float) (($sale->paid_amount ?? 0) + ($sale->remaining_amount ?? 0)));
                            $hasOutstandingCredit = (float) ($sale->remaining_amount ?? 0) > 0;
                            $hasCreditComponent = $sale->sale_type === 'credit' || (int) ($sale->has_partial_credit ?? 0) === 1;
                            $isFullCredit = $sale->sale_type === 'credit' && $hasOutstandingCredit;
                            $isMixedWithCredit = $sale->sale_type === 'mixed' && $hasOutstandingCredit;
                            $effectiveTimestamp = ($sale->updated_at && $sale->updated_at->ne($sale->created_at)) ? $sale->updated_at : $sale->created_at;
                            $profitDisplay = $hasCreditComponent && $hasOutstandingCredit ? 'مؤجل' : number_format($netProfit, 2);
                            $paymentBadgeColor = match($sale->payment_label) {
                                'نقداً', 'نقداً + آجل' => 'green',
                                'بطاقة', 'بطاقة + آجل' => 'blue',
                                'ميكس', 'ميكس + آجل' => 'purple',
                                'تم التحصيل', 'تحصيل' => 'emerald',
                                default => 'yellow',
                            };
                            $wasEdited = $sale->updated_at && $sale->updated_at->ne($sale->created_at);
                            $shouldShowFinancialSummary = $wasEdited || $hasCreditComponent || $sale->sale_type === 'mixed';
                            $collectedProfit = (float) ($sale->paid_amount ?? 0) - (float) ($sale->total_cost ?? 0);
                            $fullOperationProfit = $operationAmount - (float) ($sale->total_cost ?? 0);
                        @endphp
                        <div class="{{ $bgColor }} rounded-xl border border-gray-700 hover:border-green-500/30 transition-all hover:shadow-lg hover:shadow-green-500/5">
                            <div class="px-4 py-3 flex flex-wrap items-center justify-between gap-3 cursor-pointer" onclick="toggleDetails({{ $sale->id }})">
                                <div class="flex items-center gap-3 flex-wrap">
                                    <span class="text-white font-bold bg-gray-900 w-8 h-8 rounded-lg flex items-center justify-center text-sm">#{{ $loop->iteration }}</span>
                                    <span class="px-2 py-1 rounded-full text-[10px] {{ $isCollectionOperation ? 'bg-emerald-500/20 text-emerald-300' : ($sale->items->isNotEmpty() ? 'bg-purple-500/20 text-purple-400' : 'bg-yellow-500/20 text-yellow-400') }}">
                                        {{ $isCollectionOperation ? 'تحصيل آجل' : ($sale->items->isNotEmpty() ? 'منتجات' : 'شغل يد') }}
                                    </span>
                                    @if($visibleProducts)
                                        <div class="flex flex-col gap-0.5">
                                            <span class="text-xs text-blue-300 bg-blue-500/10 border border-blue-500/20 px-2 py-1 rounded">{{ $visibleProducts }}</span>
                                            <span class="text-[10px] text-gray-500">{{ $effectiveTimestamp->format('Y-m-d h:i A') }}</span>
                                            @if($sale->updated_at && $sale->updated_at->ne($sale->created_at))
                                                <span class="text-[10px] text-amber-300">آخر تعديل: {{ $sale->updated_at->format('h:i A') }}</span>
                                            @endif
                                            @if(!empty($sale->description))
                                                <span class="text-[10px] text-gray-300 bg-gray-900/70 border border-gray-700 rounded px-2 py-0.5 max-w-[260px] truncate" title="{{ $sale->description }}">{{ $sale->description }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <div class="flex flex-col gap-0.5">
                                            <span class="text-[10px] text-gray-500">{{ $effectiveTimestamp->format('Y-m-d h:i A') }}</span>
                                            @if(!empty($sale->description))
                                                <span class="text-[10px] text-gray-300 bg-gray-900/70 border border-gray-700 rounded px-2 py-0.5 max-w-[260px] truncate" title="{{ $sale->description }}">{{ $sale->description }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <div class="flex items-center gap-4 flex-wrap">
                                    <span class="text-gray-400 text-sm">
                                        {{ $hasCreditComponent && $hasOutstandingCredit ? 'القيمة الأساسية:' : 'المستلم:' }}
                                        <span class="{{ $hasCreditComponent && $hasOutstandingCredit ? 'text-yellow-400' : 'text-green-400' }} font-bold">{{ number_format($hasCreditComponent && $hasOutstandingCredit ? $operationAmount : $sale->paid_amount, 2) }}</span>
                                    </span>
                                    <span class="text-gray-400 text-sm">التكلفة: <span class="text-yellow-400 font-bold">{{ number_format($sale->total_cost, 2) }}</span></span>
                                    <span class="text-gray-400 text-sm">{{ $isCollectionOperation ? 'الموظف:' : 'الربح:' }} <span class="{{ $isCollectionOperation ? 'text-emerald-300' : ($hasCreditComponent && $hasOutstandingCredit ? 'text-yellow-400' : 'text-blue-400') }} font-bold">{{ $isCollectionOperation ? ($sale->employee_name ?? 'غير معروف') : $profitDisplay }}</span></span>
                                    <span class="text-{{ $paymentBadgeColor }}-400 text-xs border border-{{ $paymentBadgeColor }}-500/30 px-2 py-1 rounded-lg">
                                        {{ $sale->payment_label }}
                                    </span>
                                    <i class="fas fa-chevron-down text-gray-500 text-xs transition-transform" id="arrow-{{ $sale->id }}"></i>
                                </div>
                            </div>

                            <div id="details-{{ $sale->id }}" class="hidden border-t border-gray-700 p-4 bg-gray-900/30">

                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                                    <div class="lg:col-span-2">
                                        @if($shouldShowFinancialSummary)
                                        <div class="mb-4 rounded-xl border border-cyan-500/20 bg-cyan-500/5 p-3">
                                            <div class="mb-3 flex items-center justify-between gap-2">
                                                <h4 class="text-sm font-bold text-cyan-300">ملخص مالي توضيحي للعملية</h4>
                                                <span class="text-[11px] text-gray-400">يظهر فقط للعمليات المعدلة أو التي فيها آجل/ميكس</span>
                                            </div>

                                            <div class="grid grid-cols-2 xl:grid-cols-4 gap-2 text-xs">
                                                <div class="bg-gray-900/60 p-3 rounded-lg text-center border border-gray-700">
                                                    <span class="text-gray-400 block mb-1">إجمالي العملية الكامل</span>
                                                    <span class="text-white font-bold">{{ number_format($operationAmount, 2) }} ر.س</span>
                                                </div>
                                                <div class="bg-gray-900/60 p-3 rounded-lg text-center border border-gray-700">
                                                    <span class="text-gray-400 block mb-1">المبلغ المحصل الآن</span>
                                                    <span class="text-green-400 font-bold">{{ number_format($sale->paid_amount, 2) }} ر.س</span>
                                                </div>
                                                <div class="bg-gray-900/60 p-3 rounded-lg text-center border border-gray-700">
                                                    <span class="text-gray-400 block mb-1">المتبقي / الآجل</span>
                                                    <span class="{{ $sale->remaining_amount > 0 ? 'text-yellow-400' : 'text-emerald-400' }} font-bold">{{ number_format($sale->remaining_amount ?? 0, 2) }} ر.س</span>
                                                </div>
                                                <div class="bg-gray-900/60 p-3 rounded-lg text-center border border-gray-700">
                                                    <span class="text-gray-400 block mb-1">حالة الربح</span>
                                                    <span class="{{ $hasOutstandingCredit ? 'text-yellow-400' : ($collectedProfit >= 0 ? 'text-blue-400' : 'text-red-400') }} font-bold">{{ $hasOutstandingCredit ? 'مؤجل حتى التحصيل' : number_format($collectedProfit, 2) . ' ر.س' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        @if(!$isCollectionOperation && $sale->items->isNotEmpty())
                                            <div class="space-y-4">
                                                @foreach($sale->items as $item)
                                                @php
                                                    $itemTotal = $item->total ?? ($item->price * $item->quantity);
                                                    $quantity = $item->display_quantity ?? ($item->custom_consumption ?? $item->quantity);
                                                    $productName = $item->display_name ?? $item->product_name ?? 'منتج';
                                                    $unitText = $item->display_unit ?? 'وحدة';
                                                    $quantityDisplay = is_numeric($quantity)
                                                        ? rtrim(rtrim(number_format((float) $quantity, 2, '.', ''), '0'), '.')
                                                        : $quantity;
                                                @endphp
                                                <div class="border-b border-gray-700/50 pb-3">
                                                    <div class="flex items-center gap-2 mb-2">
                                                        <span class="text-blue-400 font-bold">{{ $productName }}</span>
                                                        <span class="text-gray-500 text-xs">({{ $quantityDisplay }} {{ $unitText }})</span>
                                                    </div>

                                                    <div class="grid grid-cols-2 gap-2 text-xs">
                                                        <div class="bg-gray-900/50 p-2 rounded text-center">
                                                            <span class="text-gray-500 block">سعر بيع المنتج</span>
                                                            <span class="text-green-400 font-bold">{{ number_format($item->price, 2) }}</span>
                                                        </div>
                                                        <div class="bg-gray-900/50 p-2 rounded text-center">
                                                            <span class="text-gray-500 block">تكلفة الوحدة الفعلية</span>
                                                            @php
                                                                $effectiveUnitCost = ((float) $quantity > 0) ? ($item->calculated_cost / (float) $quantity) : 0;
                                                            @endphp
                                                            <span class="{{ $effectiveUnitCost > 0 ? 'text-yellow-400' : 'text-red-400' }} font-bold">{{ number_format($effectiveUnitCost, 2) }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-gray-500 text-sm">{{ $isCollectionOperation ? 'هذه العملية تمثل تحصيلًا من مديونية موظف.' : 'لا توجد منتجات في هذه العملية' }}</p>
                                        @endif

                                        @if($sale->labor_total > 0)
                                        <div class="mt-3 p-3 bg-yellow-500/10 rounded-lg flex justify-between items-center">
                                            <span class="text-yellow-400"><i class="fas fa-hand ml-2"></i>شغل يد</span>
                                            <span class="text-yellow-400 font-bold">{{ number_format($sale->labor_total, 2) }} ر.س</span>
                                        </div>
                                        @endif
                                    </div>

                                    <div class="space-y-3">
                                        <div class="bg-gray-800 p-4 rounded-lg">
                                            <h3 class="text-white font-bold mb-3 text-sm border-b border-gray-700 pb-2">{{ $isCollectionOperation ? 'ملخص التحصيل' : 'ملخص العملية' }}</h3>

                                            <div class="space-y-2">
                                                <div class="flex justify-between">
                                                    <span class="text-gray-400 text-sm">{{ $isCollectionOperation ? 'المبلغ المحصل:' : ($hasCreditComponent && $hasOutstandingCredit ? 'القيمة الأساسية للعملية:' : 'المبلغ المستلم:') }}</span>
                                                    <span class="text-white font-bold">{{ number_format($hasCreditComponent && $hasOutstandingCredit ? $operationAmount : $sale->paid_amount, 2) }} ر.س</span>
                                                </div>
                                                @if(!$isCollectionOperation && ($sale->sale_type === 'mixed' || (float) ($sale->remaining_amount ?? 0) > 0))
                                                <div class="flex justify-between">
                                                    <span class="text-gray-400 text-sm">كاش{{ $sale->sale_type === 'mixed' ? ' (ضمن الميكس)' : '' }}:</span>
                                                    <span class="text-emerald-400 font-bold">{{ number_format($sale->cash_paid, 2) }} ر.س</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-gray-400 text-sm">شبكة{{ $sale->sale_type === 'mixed' ? ' (ضمن الميكس)' : '' }}:</span>
                                                    <span class="text-cyan-400 font-bold">{{ number_format($sale->card_paid, 2) }} ر.س</span>
                                                </div>
                                                @if($sale->remaining_amount > 0)
                                                <div class="flex justify-between">
                                                    <span class="text-gray-400 text-sm">الجزء الآجل{{ $sale->sale_type === 'mixed' ? ' (ضمن الميكس)' : '' }}:</span>
                                                    <span class="text-yellow-400 font-bold">{{ number_format($sale->remaining_amount, 2) }} ر.س</span>
                                                </div>
                                                @endif
                                                @endif
                                                <div class="flex justify-between">
                                                    <span class="text-gray-400 text-sm">{{ $isCollectionOperation ? 'اسم الموظف:' : 'إجمالي تكلفة المنتجات:' }}</span>
                                                    <span class="{{ $isCollectionOperation ? 'text-emerald-300' : 'text-yellow-400' }}">{{ $isCollectionOperation ? ($sale->employee_name ?? 'غير معروف') : number_format($productsCost, 2) . ' ر.س' }}</span>
                                                </div>
                                                @if(!$isCollectionOperation)
                                                <div class="flex justify-between">
                                                    <span class="text-gray-400 text-sm">حالة الربح الحالية:</span>
                                                    <span class="{{ $hasOutstandingCredit ? 'text-yellow-400' : ($collectedProfit >= 0 ? 'text-blue-400' : 'text-red-400') }} font-bold">{{ $hasOutstandingCredit ? 'مؤجل حتى التحصيل' : number_format($collectedProfit, 2) . ' ر.س' }}</span>
                                                </div>
                                                <div class="flex justify-between pt-2 border-t border-gray-700">
                                                    <span class="text-gray-400 text-sm font-bold">الربح النهائي للعملية:</span>
                                                    <span class="{{ $hasOutstandingCredit ? 'text-yellow-400' : 'text-blue-400' }} font-bold text-lg">{{ $hasOutstandingCredit ? 'لا يُحتسب قبل اكتمال التحصيل' : number_format($fullOperationProfit, 2) . ' ر.س' }}</span>
                                                </div>
                                                @endif
                                            </div>

                                            @if(!$isCollectionOperation)
                                            <div class="mt-3 pt-3 border-t border-gray-700 flex justify-end">
                                                <button type="button"
                                                        onclick="event.stopPropagation(); openEditSaleModal({{ $sale->id }});"
                                                        class="text-xs bg-indigo-600/20 text-indigo-300 border border-indigo-500/40 px-3 py-1.5 rounded-lg hover:bg-indigo-600/30 transition">
                                                    <i class="fas fa-pen ml-1"></i> تعديل العملية
                                                </button>

                                                <form method="POST"
                                                      action="{{ route('user.stores.daily.destroy', [$store->id, $sale->id]) }}"
                                                      class="mr-2"
                                                      onsubmit="event.stopPropagation(); return confirm('هل أنت متأكد من حذف العملية رقم #{{ $sale->id }}؟ سيتم استرجاع المخزون المرتبط بها.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="text-xs bg-red-600/20 text-red-300 border border-red-500/40 px-3 py-1.5 rounded-lg hover:bg-red-600/30 transition">
                                                        <i class="fas fa-trash ml-1"></i> حذف العملية
                                                    </button>
                                                </form>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- نافذة تعديل العملية --}}
                        <div id="edit-sale-modal-{{ $sale->id }}" class="hidden fixed inset-0 z-50 bg-black/70 p-4" onclick="closeEditSaleModal({{ $sale->id }})">
                            <div class="max-w-lg mx-auto mt-16 max-h-[85vh] overflow-y-auto bg-gray-900 border border-gray-700 rounded-xl p-5" onclick="event.stopPropagation()">
                                <h3 class="text-white font-bold text-lg mb-4">تعديل العملية #{{ $sale->id }}</h3>

                                <form method="POST" action="{{ route('user.stores.daily.update', [$store->id, $sale->id]) }}" class="space-y-4">
                                    @csrf
                                    @method('PUT')

                                    @if(session('edit_sale_modal') == $sale->id && $errors->any())
                                    <div class="rounded-lg border border-red-500/30 bg-red-500/10 p-3 text-sm text-red-200">
                                        <ul class="space-y-1 list-disc pr-4">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif

                                    <div>
                                        <label class="text-sm text-gray-300 block mb-1">نوع البيع</label>
                                        <select id="sale-type-{{ $sale->id }}" name="sale_type" onchange="updateEditSaleFields({{ $sale->id }})" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white">
                                            <option value="cash" @selected($sale->sale_type === 'cash')>نقداً</option>
                                            <option value="card" @selected($sale->sale_type === 'card')>بطاقة</option>
                                            <option value="credit" @selected($sale->sale_type === 'credit')>آجل</option>
                                            <option value="mixed" @selected($sale->sale_type === 'mixed')>ميكس</option>
                                        </select>
                                    </div>

                                    <div id="paid-amount-wrapper-{{ $sale->id }}"
                                         data-original-sale-type="{{ $sale->sale_type }}"
                                         data-original-paid-amount="{{ (float) ($sale->paid_amount ?? 0) }}"
                                         data-original-remaining-amount="{{ (float) ($sale->remaining_amount ?? 0) }}"
                                         class="{{ $sale->sale_type === 'credit' ? 'hidden' : '' }}">
                                        <label id="paid-amount-label-{{ $sale->id }}" class="text-sm text-gray-300 block mb-1">المبلغ المدفوع</label>
                                        <input id="paid-amount-input-{{ $sale->id }}" type="number" step="0.01" min="0" name="paid_amount" value="{{ old('paid_amount', $sale->paid_amount) }}"
                                               class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white">
                                        <p id="paid-amount-help-{{ $sale->id }}" class="text-xs text-gray-500 mt-1">في حالة (نقد/بطاقة) سيتم ضبط المدفوع تلقائياً على إجمالي الفاتورة. في الميكس أدخل الكاش/الشبكة، وفي الآجل الكامل ستكون المديونية هي كامل العملية.</p>
                                        <div id="credit-conversion-warning-{{ $sale->id }}" class="hidden mt-2 rounded-lg border border-amber-500/30 bg-amber-500/10 p-2 text-xs text-amber-200">
                                            تنبيه: هذه العملية كانت آجلًا وتم تحصيل جزء منها مسبقًا؛ لذلك تم وضع <span class="font-bold">القيمة المتبقية</span> داخل خانة المبلغ المدفوع لإكمال التحويل.
                                        </div>
                                    </div>

                                    <div id="debt-wrapper-{{ $sale->id }}" class="{{ in_array($sale->sale_type, ['credit', 'mixed'], true) ? '' : 'hidden' }}">
                                        <label class="text-sm text-gray-300 block mb-1">قيمة المديونية</label>
                                        <input type="number" step="0.01" min="0" name="debt_amount" value="{{ old('debt_amount', $sale->remaining_amount ?? 0) }}"
                                               class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white">
                                        <p class="text-xs text-gray-500 mt-1">للآجل الكامل يجب أن تساوي كامل العملية. وللآجل الجزئي في الميكس يجب أن يكون (كاش + شبكة + مديونية) = قيمة العملية.</p>
                                    </div>

                                    <div id="employee-wrapper-{{ $sale->id }}" class="{{ in_array($sale->sale_type, ['credit', 'mixed'], true) ? '' : 'hidden' }}">
                                        <label class="text-sm text-gray-300 block mb-1">الموظف المرتبط بالآجل</label>
                                        <select name="employee_id" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white">
                                            <option value="">بدون موظف</option>
                                            @foreach(($employees ?? collect()) as $employee)
                                                <option value="{{ $employee->id }}" @selected(old('employee_id', $sale->employee_id) == $employee->id)>{{ $employee->name }}</option>
                                            @endforeach
                                        </select>
                                        <p class="text-xs text-gray-500 mt-1">اختياره إلزامي عند وجود مديونية كاملة أو جزئية.</p>
                                    </div>

                                    <div id="mixed-wrapper-{{ $sale->id }}" class="{{ $sale->sale_type === 'mixed' ? '' : 'hidden' }}">
                                    <div id="mixed-conversion-warning-{{ $sale->id }}"
                                         data-original-sale-type="{{ $sale->sale_type }}"
                                         data-original-paid-amount="{{ (float) ($sale->paid_amount ?? 0) }}"
                                         data-original-remaining-amount="{{ (float) ($sale->remaining_amount ?? 0) }}"
                                         class="hidden mb-3 rounded-lg border border-cyan-500/30 bg-cyan-500/10 p-2 text-xs text-cyan-200">
                                        عند التحويل إلى ميكس من آجل محصّل جزئيًا، أدخل القيم يدويًا بحيث يكون:
                                        <span class="font-bold">كاش + شبكة + مديونية = المتبقي من العملية</span>.
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                        <div>
                                            <label class="text-sm text-gray-300 block mb-1">كاش (لـ ميكس)</label>
                                            <input id="cash-amount-input-{{ $sale->id }}" type="number" step="0.01" min="0" name="cash_amount" value="{{ old('cash_amount', $sale->cash_amount) }}"
                                                   class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white">
                                        </div>
                                        <div>
                                            <label class="text-sm text-gray-300 block mb-1">شبكة (لـ ميكس)</label>
                                            <input id="card-amount-input-{{ $sale->id }}" type="number" step="0.01" min="0" name="card_amount" value="{{ old('card_amount', $sale->card_amount) }}"
                                                   class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white">
                                        </div>
                                    </div>
                                    </div>

                                    <div>
                                        <label class="text-sm text-gray-300 block mb-1">شغل اليد</label>
                                        <input type="number" step="0.01" min="0" name="labor_total" value="{{ old('labor_total', $sale->labor_total) }}"
                                               class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white">
                                    </div>

                                    <div>
                                        <label class="text-sm text-gray-300 block mb-1">الوصف</label>
                                        <textarea name="description" rows="3"
                                                  class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white">{{ old('description', $sale->description) }}</textarea>
                                    </div>

                                    <div class="flex gap-2 justify-end">
                                        <button type="button" onclick="closeEditSaleModal({{ $sale->id }})" class="px-4 py-2 bg-gray-700 text-white rounded-lg">إلغاء</button>
                                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg">حفظ التعديل</button>
                                    </div>
                                </form>

                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            @endforeach
        @else
        <div class="text-center py-16 bg-gray-800/30 rounded-2xl border border-gray-700">
            <i class="fas fa-chart-line text-5xl text-gray-600 mb-4"></i>
            <p class="text-gray-500 text-lg">لا توجد مبيعات</p>
            @if(request('date') || request('search'))
            <a href="{{ route('user.stores.daily', $store->id) }}" class="mt-4 inline-block bg-gray-700 hover:bg-gray-600 text-white px-6 py-2 rounded-xl">
                عرض مبيعات اليوم
            </a>
            @endif
        </div>
        @endif
    </div>

    {{-- ===== ملخص الصفحة ===== --}}
    @if($sales->count() > 0)
    <div class="mt-6 p-4 bg-gray-800/50 rounded-xl border border-gray-700">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <p class="text-gray-400 text-xs">عدد العمليات</p>
                <p class="text-white font-bold">{{ $sales->count() }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-xs">إجمالي المستلم</p>
                <p class="text-green-400 font-bold">{{ number_format($sales->sum('paid_amount'), 2) }} ر.س</p>
            </div>
            <div>
                <p class="text-gray-400 text-xs">إجمالي التكلفة</p>
                <p class="text-yellow-400 font-bold">{{ number_format($sales->sum('total_cost'), 2) }} ر.س</p>
            </div>
            <div>
                <p class="text-gray-400 text-xs">الربح المحتسب</p>
                <p class="text-blue-400 font-bold">{{ number_format($sales->sum('recognized_profit'), 2) }} ر.س</p>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- JavaScript للتحكم في إظهار/إخفاء التفاصيل --}}
<script>
function toggleDetails(saleId) {
    const details = document.getElementById(`details-${saleId}`);
    const arrow = document.getElementById(`arrow-${saleId}`);

    if (details) {
        if (details.classList.contains('hidden')) {
            details.classList.remove('hidden');
            arrow.classList.add('rotate-180');
        } else {
            details.classList.add('hidden');
            arrow.classList.remove('rotate-180');
        }
    }
}

function updateEditSaleFields(saleId) {
    const saleType = document.getElementById(`sale-type-${saleId}`)?.value;
    const paidWrapper = document.getElementById(`paid-amount-wrapper-${saleId}`);
    const debtWrapper = document.getElementById(`debt-wrapper-${saleId}`);
    const employeeWrapper = document.getElementById(`employee-wrapper-${saleId}`);
    const mixedWrapper = document.getElementById(`mixed-wrapper-${saleId}`);
    const paidInput = document.getElementById(`paid-amount-input-${saleId}`);
    const paidLabel = document.getElementById(`paid-amount-label-${saleId}`);
    const paidHelp = document.getElementById(`paid-amount-help-${saleId}`);
    const conversionWarning = document.getElementById(`credit-conversion-warning-${saleId}`);
    const mixedConversionWarning = document.getElementById(`mixed-conversion-warning-${saleId}`);
    const cashInput = document.getElementById(`cash-amount-input-${saleId}`);
    const cardInput = document.getElementById(`card-amount-input-${saleId}`);

    if (!saleType) return;

    const isCredit = saleType === 'credit';
    const isMixed = saleType === 'mixed';
    const hasDebt = isCredit || isMixed;
    const originalSaleType = paidWrapper?.dataset.originalSaleType || '';
    const originalPaidAmount = parseFloat(paidWrapper?.dataset.originalPaidAmount || '0');
    const originalRemainingAmount = parseFloat(paidWrapper?.dataset.originalRemainingAmount || '0');
    const isCollectedCreditConversion = originalSaleType === 'credit' && originalPaidAmount > 0 && originalRemainingAmount > 0 && !isCredit;
    const isCollectedCreditToMixedConversion = originalSaleType === 'credit' && originalPaidAmount > 0 && originalRemainingAmount > 0 && isMixed;

    paidWrapper?.classList.toggle('hidden', isCredit);
    debtWrapper?.classList.toggle('hidden', !hasDebt);
    employeeWrapper?.classList.toggle('hidden', !hasDebt);
    mixedWrapper?.classList.toggle('hidden', !isMixed);

    if (paidLabel) {
        paidLabel.textContent = isCollectedCreditConversion ? 'المبلغ المتبقي المطلوب تحصيله' : 'المبلغ المدفوع';
    }

    if (paidHelp) {
        paidHelp.textContent = isCollectedCreditConversion
            ? `تم تحصيل ${originalPaidAmount.toFixed(2)} سابقًا، والمتبقي الآن ${originalRemainingAmount.toFixed(2)}. عند التحويل من آجل إلى نوع آخر ابدأ من القيمة المتبقية.`
            : 'في حالة (نقد/بطاقة) سيتم ضبط المدفوع تلقائياً على إجمالي الفاتورة. في الميكس أدخل الكاش/الشبكة، وفي الآجل الكامل ستكون المديونية هي كامل العملية.';
    }

    conversionWarning?.classList.toggle('hidden', !isCollectedCreditConversion);
    mixedConversionWarning?.classList.toggle('hidden', !isCollectedCreditToMixedConversion);

    if (paidInput && isCollectedCreditConversion) {
        paidInput.value = originalRemainingAmount.toFixed(2);
    }

    if (mixedConversionWarning && isCollectedCreditToMixedConversion) {
        mixedConversionWarning.innerHTML = `عند التحويل إلى ميكس من آجل محصّل جزئيًا: تم تحصيل <span class="font-bold">${originalPaidAmount.toFixed(2)}</span> سابقًا، والمتبقي الآن <span class="font-bold">${originalRemainingAmount.toFixed(2)}</span>. أدخل القيم بحيث يكون <span class="font-bold">كاش + شبكة + مديونية = ${originalRemainingAmount.toFixed(2)}</span>.`;
    }

    if (isCollectedCreditToMixedConversion) {
        if (cashInput) cashInput.value = '';
        if (cardInput) cardInput.value = '';
    }
}

function openEditSaleModal(saleId) {
    const modal = document.getElementById(`edit-sale-modal-${saleId}`);
    if (modal) {
        modal.classList.remove('hidden');
        updateEditSaleFields(saleId);
    }
}

function closeEditSaleModal(saleId) {
    const modal = document.getElementById(`edit-sale-modal-${saleId}`);
    if (modal) modal.classList.add('hidden');
}

document.addEventListener('DOMContentLoaded', () => {
    const failedModalId = @json(session('edit_sale_modal'));
    if (failedModalId) {
        openEditSaleModal(failedModalId);
    }
});
</script>

<style>
.rotate-180 {
    transform: rotate(180deg);
}
</style>
@endsection
