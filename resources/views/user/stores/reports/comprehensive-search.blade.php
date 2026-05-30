@extends('dashboard.app')

@section('title', 'بحث عمليات المتجر - ' . $store->name)

@section('content')
@php
    $money = fn ($value) => number_format((float) $value, 2);
    $searchLabel = $search !== '' ? $search : 'كل العمليات في الفترة';
@endphp

<div class="max-w-6xl mx-auto px-3 py-4 sm:px-4 sm:py-6 text-right" dir="rtl" x-data="{ open: false, modal: {}, show(data) { this.modal = data; this.open = true }, close() { this.open = false } }">
    <div class="mb-5 bg-gray-800 border border-gray-700 rounded-2xl p-4 sm:p-5">
        <div class="flex items-start gap-3">
            <a href="{{ route('user.stores.reports.index', $store->id) }}"
               class="p-3 rounded-xl bg-gray-900 border border-gray-700 text-gray-300 hover:text-white hover:border-blue-500 transition">
                <i class="fa-solid fa-arrow-right"></i>
            </a>
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-white leading-8">بحث عمليات المتجر</h1>
                <p class="text-gray-400 text-sm mt-1">ابحث باسم منتج أو وصف عملية، وستظهر كل العمليات المطابقة وإجماليها.</p>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('user.stores.reports.search', $store->id) }}" class="mb-5 bg-gray-800 border border-gray-700 rounded-2xl p-4 sm:p-5">
        <input type="hidden" name="scope" value="all">

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div class="md:col-span-2">
                <label for="q" class="block text-sm font-bold text-gray-300 mb-2">المنتج أو الوصف</label>
                <input id="q" name="q" value="{{ $search }}" type="text" autofocus
                       placeholder="مثال: تظليل، لمبة، شغل يد..."
                       class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition">
            </div>

            <div>
                <label for="from" class="block text-sm font-bold text-gray-300 mb-2">من</label>
                <input id="from" name="from" value="{{ $from }}" type="date"
                       class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition">
            </div>

            <div>
                <label for="to" class="block text-sm font-bold text-gray-300 mb-2">إلى</label>
                <input id="to" name="to" value="{{ $to }}" type="date"
                       class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition">
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-2 mt-4">
            <button type="submit" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-500 text-white px-6 py-2.5 rounded-xl font-bold transition inline-flex items-center justify-center gap-2">
                <i class="fa-solid fa-search"></i>
                بحث
            </button>
            <a href="{{ route('user.stores.reports.search', $store->id) }}" class="w-full sm:w-auto bg-gray-700 hover:bg-gray-600 text-white px-6 py-2.5 rounded-xl font-bold transition inline-flex items-center justify-center gap-2">
                <i class="fa-solid fa-rotate-right"></i>
                مسح
            </a>
        </div>
    </form>

    <section class="mb-5 bg-gradient-to-br from-slate-900 to-gray-900 border border-cyan-700/50 rounded-2xl p-4 sm:p-5">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h2 class="text-lg sm:text-xl font-black text-white">خلاصة البحث</h2>
                <p class="text-gray-400 text-sm mt-1">
                    البحث: <span class="text-cyan-300 font-bold">{{ $searchLabel }}</span>
                    <span class="text-gray-600 mx-1">|</span>
                    {{ $from }} إلى {{ $to }}
                </p>
            </div>
            <div class="bg-cyan-500/10 border border-cyan-500/30 rounded-2xl p-4 min-w-full lg:min-w-[260px]">
                <p class="text-cyan-200 text-sm">إجمالي العمليات المطابقة</p>
                <p class="text-3xl font-black text-cyan-300 mt-2">{{ $money($summary['all_operations_total']) }} ر.س</p>
                <p class="text-xs text-gray-400 mt-1">{{ number_format($summary['all_operations_count']) }} عملية</p>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
            <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl p-3">
                <span class="text-emerald-200">بيع:</span>
                <span class="text-white font-black">{{ $money($summary['sales_total']) }} ر.س</span>
                <span class="text-gray-500 text-xs">({{ number_format($summary['sales_count']) }})</span>
            </div>
            <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-xl p-3">
                <span class="text-yellow-200">استهلاك:</span>
                <span class="text-white font-black">{{ $money($summary['internal_total']) }} ر.س</span>
                <span class="text-gray-500 text-xs">({{ number_format($summary['internal_count']) }})</span>
            </div>
            <div class="bg-orange-500/10 border border-orange-500/30 rounded-xl p-3">
                <span class="text-orange-200">مشتريات مالك:</span>
                <span class="text-white font-black">{{ $money($summary['owner_purchases_total']) }} ر.س</span>
                <span class="text-gray-500 text-xs">({{ number_format($summary['owner_purchases_count']) }})</span>
            </div>
        </div>
    </section>

    <section class="bg-gray-800 border border-gray-700 rounded-2xl overflow-hidden">
        <div class="p-4 border-b border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h2 class="text-lg font-black text-white">جدول العمليات</h2>
            <span class="text-sm text-gray-400">اضغط أيقونة العين لعرض التفاصيل</span>
        </div>

        <div class="md:hidden divide-y divide-gray-700">
            @forelse($unifiedOperations as $operation)
                @php
                    $operationDate = optional($operation['date'])->format('Y-m-d H:i');
                    $operationAmount = $money($operation['amount']) . ' ر.س';
                @endphp
                <article class="p-4 space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <span class="border rounded-full px-3 py-1 text-xs font-bold whitespace-nowrap {{ $operation['badge_class'] }}">{{ $operation['type_label'] }}</span>
                            <h3 class="text-white font-bold break-words mt-2">{{ $operation['title'] }}</h3>
                            <p class="text-gray-400 text-xs mt-1">#{{ $operation['id'] }}</p>
                        </div>
                        <button type="button"
                                @click="show({type: @js($operation['type_label']), id: @js($operation['id']), title: @js($operation['title']), details: @js($operation['details'] ?: 'لا يوجد تفاصيل'), meta: @js($operation['meta']), amount: @js($operationAmount), date: @js($operationDate)})"
                                class="shrink-0 w-10 h-10 rounded-xl bg-blue-500/10 border border-blue-500/30 text-blue-300 hover:bg-blue-500/20 transition inline-flex items-center justify-center"
                                title="التفاصيل">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    <div class="flex items-center justify-between gap-3 bg-gray-900/60 rounded-xl px-3 py-2">
                        <span class="text-gray-500 text-xs">القيمة</span>
                        <span class="text-white font-black">{{ $operationAmount }}</span>
                    </div>
                </article>
            @empty
                <div class="p-10 text-center text-gray-400">
                    <i class="fa-solid fa-magnifying-glass text-3xl mb-3 text-gray-500"></i>
                    <p class="font-bold text-white">لا توجد عمليات مطابقة</p>
                    <p class="text-sm mt-1">جرّب اسم منتج أو وصف آخر.</p>
                </div>
            @endforelse
        </div>

        <div class="hidden md:block overflow-x-auto">
            <table class="w-full min-w-[760px] text-sm">
                <thead class="bg-gray-900/80 text-gray-300">
                    <tr>
                        <th class="px-4 py-3 text-right">النوع</th>
                        <th class="px-4 py-3 text-right">التاريخ والوقت</th>
                        <th class="px-4 py-3 text-right">العملية / الوصف</th>
                        <th class="px-4 py-3 text-right">القيمة</th>
                        <th class="px-4 py-3 text-center">التفاصيل</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($unifiedOperations as $operation)
                        @php
                            $operationDate = optional($operation['date'])->format('Y-m-d H:i');
                            $operationAmount = $money($operation['amount']) . ' ر.س';
                        @endphp
                        <tr class="hover:bg-gray-700/30 transition align-top">
                            <td class="px-4 py-3">
                                <span class="border rounded-full px-3 py-1 text-xs font-bold whitespace-nowrap {{ $operation['badge_class'] }}">{{ $operation['type_label'] }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-300 whitespace-nowrap">{{ $operationDate }}</td>
                            <td class="px-4 py-3">
                                <p class="text-white font-bold break-words">{{ $operation['title'] }}</p>
                                <p class="text-gray-500 text-xs mt-1">#{{ $operation['id'] }}</p>
                            </td>
                            <td class="px-4 py-3 text-white font-black whitespace-nowrap">{{ $operationAmount }}</td>
                            <td class="px-4 py-3 text-center">
                                <button type="button"
                                        @click="show({type: @js($operation['type_label']), id: @js($operation['id']), title: @js($operation['title']), details: @js($operation['details'] ?: 'لا يوجد تفاصيل'), meta: @js($operation['meta']), amount: @js($operationAmount), date: @js($operationDate)})"
                                        class="w-10 h-10 rounded-xl bg-blue-500/10 border border-blue-500/30 text-blue-300 hover:bg-blue-500/20 transition inline-flex items-center justify-center"
                                        title="التفاصيل">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-10 text-center text-gray-400">
                                <i class="fa-solid fa-magnifying-glass text-3xl mb-3 text-gray-500"></i>
                                <p class="font-bold text-white">لا توجد عمليات مطابقة</p>
                                <p class="text-sm mt-1">جرّب اسم منتج أو وصف آخر.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div x-show="open"
         x-cloak
         @keydown.escape.window="close()"
         class="fixed inset-0 z-[99999] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="close()"></div>
        <div x-show="open"
             x-transition
             class="relative w-full max-w-lg bg-gray-900 border border-gray-700 rounded-2xl shadow-2xl overflow-hidden">
            <div class="p-4 border-b border-gray-700 flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs text-gray-500" x-text="modal.type + ' #' + modal.id"></p>
                    <h3 class="text-lg font-black text-white mt-1" x-text="modal.title"></h3>
                </div>
                <button type="button" @click="close()" class="w-10 h-10 rounded-xl bg-gray-800 text-gray-300 hover:text-white hover:bg-gray-700 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="p-4 space-y-3 text-sm">
                <div class="bg-gray-800/70 rounded-xl p-3">
                    <p class="text-gray-500 text-xs">القيمة</p>
                    <p class="text-cyan-300 font-black mt-1" x-text="modal.amount"></p>
                </div>

                <div class="bg-gray-800/70 rounded-xl p-3">
                    <p class="text-gray-500 text-xs mb-1">التفاصيل</p>
                    <p class="text-gray-200 leading-7 break-words whitespace-pre-line" x-text="modal.details || 'لا يوجد تفاصيل'"></p>
                </div>

                <div class="bg-gray-800/70 rounded-xl p-3">
                    <p class="text-gray-500 text-xs mb-1">معلومات إضافية</p>
                    <p class="text-gray-200 leading-7 break-words" x-text="modal.meta || 'لا يوجد تفاصيل'"></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
