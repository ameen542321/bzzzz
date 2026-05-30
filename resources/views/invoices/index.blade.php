@extends('dashboard.app')

@section('title', 'إدارة الفواتير')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

  <div class="bg-gradient-to-l from-gray-900/80 via-gray-900/60 to-blue-950/40 border border-gray-800 rounded-3xl p-5 md:p-7 shadow-2xl shadow-black/20">
    <div class="flex flex-col gap-6">
    {{-- الحاوية العلوية: تجمع الأزرار في سطر واحد على الجوال --}}
    <div class="flex items-center justify-between w-full md:order-1">

        {{-- 1. زر الرجوع (يمين) --}}
        <div class="flex-shrink-0">
            <a href="{{ url()->previous() }}"
               class="inline-flex items-center gap-2 bg-gray-800 hover:bg-gray-700 text-gray-200 px-3 py-2 md:px-4 md:py-2.5 rounded-xl transition border border-gray-700 active:scale-95 text-xs md:text-sm">
                <svg class="w-5 h-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span class="hidden xs:inline"> الرجوع للرئيسية</span>
                <span class="inline xs:hidden"> الرجوع للرئيسية</span>
            </a>
        </div>

        {{-- العنوان يظهر هنا فقط في الشاشات الكبيرة (اختياري حسب الرغبة) --}}
        <div class="hidden md:flex items-center gap-4 text-center justify-center">
            <h1 class="text-2xl font-black text-white tracking-tight">إدارة الفواتير</h1>
        </div>

        {{-- 3. زر إنشاء فاتورة (يسار) --}}
        <div class="flex-shrink-0">
            <a href="{{ isset($store) ? route('user.stores.invoices.create', $store->id) : route('accountant.invoices.invoice.create') }}"
               class="inline-flex items-center gap-2 bg-gradient-to-r from-green-600 to-emerald-500 hover:from-green-700 hover:to-emerald-600 text-white px-3 py-2 md:px-6 md:py-3 rounded-xl transition-all shadow-lg shadow-green-500/20 active:scale-95 font-bold text-xs md:text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                إنشاء فاتورة
            </a>
        </div>
    </div>

    {{-- 2. عنوان الصفحة: يظهر في المنتصف في سطر منفصل على الجوال --}}
    <div class="md:hidden flex flex-col items-center gap-2 text-center">
        <h1 class="text-xl font-black text-white tracking-tight">إدارة الفواتير</h1>
        <p class="text-gray-400 text-xs">لديك <span class="text-blue-400 font-bold">{{ $invoices->total() }}</span> فاتورة مسجلة</p>
    </div>
    <div class="hidden md:flex justify-center">
        <p class="text-gray-300/90 text-sm bg-white/[0.03] border border-gray-700 rounded-xl px-4 py-2">
            لديك الآن <span class="text-blue-400 font-extrabold">{{ $invoices->total() }}</span> فاتورة في هذا العرض.
        </p>
    </div>
    </div>
  </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gray-900/50 border border-gray-800 p-5 rounded-2xl hover:border-gray-700 transition-all">
            <span class="text-gray-500 text-xs font-bold uppercase tracking-wider block mb-2">إجمالي الفواتير</span>
            <div class="text-2xl font-black text-white">{{ $totalInvoices }}</div>
        </div>
        <div class="bg-gray-900/50 border border-gray-800 p-5 rounded-2xl hover:border-green-500/30 transition-all">
            <span class="text-green-500/80 text-xs font-bold uppercase tracking-wider block mb-2">المدفوعة</span>
            <div class="text-2xl font-black text-green-400">{{ $paidInvoices }}</div>
        </div>
        <div class="bg-gray-900/50 border border-gray-800 p-5 rounded-2xl hover:border-yellow-500/30 transition-all">
            <span class="text-yellow-500/80 text-xs font-bold uppercase tracking-wider block mb-2">المعلقة</span>
            <div class="text-2xl font-black text-yellow-400">{{ $pendingInvoices }}</div>
        </div>
        <div class="bg-gray-900/50 border border-gray-800 p-5 rounded-2xl hover:border-blue-500/30 transition-all">
            <span class="text-blue-500/80 text-xs font-bold uppercase tracking-wider block mb-2">إجمالي المبيعات</span>
            <div class="text-2xl font-black text-blue-400 truncate">{{ number_format($totalAmount, 0) }} <small class="text-xs">ر.س</small></div>
        </div>
    </div>

    <div class="bg-gray-900/60 border border-gray-800 rounded-2xl p-6">
        <form method="GET" action="{{ isset($store) ? route('user.stores.invoices.index', $store->id) : route('accountant.invoices.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-7 relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="بحث برقم الفاتورة أو اسم العميل..."
                       class="w-full bg-gray-800/50 border border-gray-700 text-white rounded-xl pr-12 py-3.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all outline-none">
                <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <div class="md:col-span-3">
               <input type="date" name="date" value="{{ request('date') }}"
                class="w-full bg-gray-800/50 border border-gray-700 text-white rounded-xl px-4 py-3.5 focus:ring-2 focus:ring-blue-500 outline-none"
                style="color-scheme: dark;">
            </div>
            <div class="md:col-span-2 flex gap-2">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold transition-all shadow-lg shadow-blue-600/20">تطبيق</button>
                <a href="{{ isset($store) ? route('user.stores.invoices.index', $store->id) : route('accountant.invoices.index') }}" class="w-12 h-12 flex items-center justify-center bg-gray-800 text-gray-400 rounded-xl hover:text-white transition-all border border-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </a>
            </div>
        </form>
    </div>

    @php
        $statusMeta = [
            'paid' => ['label' => 'مدفوعة', 'class' => 'bg-green-500/10 text-green-400 border-green-500/20'],
            'pending' => ['label' => 'معلقة', 'class' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20'],
            'printed' => ['label' => 'مطبوعة', 'class' => 'bg-blue-500/10 text-blue-400 border-blue-500/20'],
            'canceled' => ['label' => 'ملغاة', 'class' => 'bg-red-500/10 text-red-400 border-red-500/20'],
        ];
    @endphp

    <div class="bg-gray-900/40 border border-gray-800 rounded-2xl overflow-hidden shadow-xl shadow-black/10">
        @if($invoices->total() > 0)

            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full text-right border-collapse">
                    <thead>
                        <tr class="bg-gray-800/40 text-gray-300 text-xs uppercase font-bold">
                            <th class="p-5">رقم الفاتورة</th>
                            <th class="p-5">العميل</th>
                            <th class="p-5">التاريخ</th>
                            <th class="p-5">المبلغ</th>
                            <th class="p-5 text-center">الحالة</th>
                            <th class="p-5 text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        @foreach($invoices as $invoice)
                        <tr class="hover:bg-white/[0.03] transition-colors group">
                            <td class="p-5 font-mono font-bold text-white uppercase text-sm">#{{ $invoice->invoice_number }}</td>
                            <td class="p-5">
                                <div class="text-white font-bold text-sm">{{ $invoice->customer_name }}</div>
                                <div class="text-gray-500 text-xs mt-1">{{ $invoice->customer_phone ?? 'بدون هاتف' }}</div>
                            </td>
                            <td class="p-5 text-gray-400 text-sm italic">{{ $invoice->created_at->format('Y/m/d') }}</td>
                            <td class="p-5">
                                <span class="text-white font-black text-base">{{ number_format($invoice->total_amount, 2) }}</span>
                                <span class="text-gray-500 text-xs">ر.س</span>
                            </td>
                            <td class="p-5 text-center">
                                @php($status = $statusMeta[$invoice->status] ?? ['label' => $invoice->status, 'class' => 'bg-gray-500/10 text-gray-400 border-gray-500/20'])
                                <span class="px-3 py-1.5 rounded-lg border text-[10px] font-black uppercase {{ $status['class'] }}">
                                    {{ $status['label'] }}
                                </span>
                            </td>
                            <td class="p-5">
                                <div class="flex items-center justify-center gap-2">
                                    {{-- رابط التفاصيل --}}
                                    <a href="{{ isset($store) ? route('user.stores.invoices.show', [$store->id, $invoice->id]) : route('accountant.invoices.show', $invoice->id) }}" class="p-2.5 bg-gray-800 text-blue-400 rounded-xl hover:bg-blue-600 hover:text-white transition-all shadow-sm border border-gray-700/50 hover:border-blue-500/40" title="عرض التفاصيل">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    {{-- زر الحذف --}}
                                    <button onclick="confirmDelete('{{ isset($store) ? route('user.stores.invoices.destroy', [$store->id, $invoice->id]) : route('accountant.invoices.destroy', $invoice->id) }}')" class="p-2.5 bg-gray-800 text-red-400 rounded-xl hover:bg-red-600 hover:text-white transition-all shadow-sm border border-gray-700/50 hover:border-red-500/40" title="حذف">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="lg:hidden grid gap-3 p-3">
                @foreach($invoices as $invoice)
                <div class="p-4 space-y-4 rounded-2xl border border-gray-800 bg-gray-900/35">
                    <div class="flex justify-between items-start">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gray-800 flex items-center justify-center font-bold text-blue-400 text-[10px]">#</div>
                            <div>
                                <h4 class="text-white font-bold font-mono text-sm">#{{ $invoice->invoice_number }}</h4>
                                <span class="text-gray-500 text-[10px]">{{ $invoice->created_at->format('d M Y | h:i A') }}</span>
                            </div>
                        </div>
                        @php($status = $statusMeta[$invoice->status] ?? ['label' => $invoice->status, 'class' => 'bg-gray-500/10 text-gray-400 border-gray-500/20'])
                        <span class="px-2 py-1 rounded-lg border text-[10px] font-black {{ $status['class'] }}">
                            {{ $status['label'] }}
                        </span>
                    </div>

                    <div class="bg-white/[0.03] p-3 rounded-xl border border-gray-800 space-y-2.5">
                        <div class="flex justify-between">
                            <span class="text-gray-500 text-xs">العميل:</span>
                            <span class="text-white text-xs font-bold">{{ $invoice->customer_name }}</span>
                        </div>
                        <div class="flex justify-between items-baseline">
                            <span class="text-gray-500 text-xs">المبلغ الإجمالي:</span>
                            <div class="text-left">
                                <span class="text-lg font-black text-white">{{ number_format($invoice->total_amount, 2) }}</span>
                                <span class="text-[10px] text-gray-500 mr-1">ر.س</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        {{-- رابط التفاصيل للجوال --}}
                        <a href="{{ isset($store) ? route('user.stores.invoices.show', [$store->id, $invoice->id]) : route('accountant.invoices.show', $invoice->id) }}" class="flex-1 flex items-center justify-center gap-2 py-2.5 bg-blue-600/10 text-blue-400 rounded-xl font-bold text-xs border border-blue-600/20 active:bg-blue-600 active:text-white transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            تفاصيل
                        </a>
                        {{-- زر الحذف للجوال --}}
                        <button onclick="confirmDelete('{{ isset($store) ? route('user.stores.invoices.destroy', [$store->id, $invoice->id]) : route('accountant.invoices.destroy', $invoice->id) }}')" class="w-11 h-11 flex items-center justify-center bg-red-600/10 text-red-500 rounded-xl border border-red-600/20 active:bg-red-600 active:text-white transition-all text-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>

        @else
            <div class="py-24 text-center px-4">
                <div class="inline-flex w-20 h-20 bg-gray-800 text-gray-500 rounded-full items-center justify-center mb-4">
                    <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17v-2a4 4 0 014-4h6m0 0l-3-3m3 3l-3 3M7 7h10M7 11h4m-4 4h3"/>
                    </svg>
                </div>
                <h3 class="text-white font-extrabold text-lg">لا توجد فواتير مطابقة لبحثك</h3>
                <p class="text-gray-500 text-sm mt-2">جرّب تغيير معايير البحث أو إعادة تعيين الفلاتر.</p>
            </div>
        @endif

        <div class="mt-6 px-2 pb-2">
            {{ $invoices->links() }}
        </div>
    </div>

    {{-- فورم الحذف المخفي --}}
    <form id="deleteForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
</div>

<script>
    function confirmDelete(url) {
        if (confirm('تنبيه: هل أنت متأكد تماماً من رغبتك في حذف هذه الفاتورة؟')) {
            const form = document.getElementById('deleteForm');
            form.action = url;
            form.submit();
        }
    }
</script>
@endsection
