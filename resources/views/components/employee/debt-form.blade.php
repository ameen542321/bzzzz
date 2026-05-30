@props(['employee', 'modalId' => 'debtModal'])
@php
    // تحديد الموديل (المحاسب أو الموظف)
    $model = $person ?? $employee;
    $finalModalId = $modalId ?? "debtOperationsModal-{$model->id}";

    // التصحيح: جلب مديونيات هذا الشخص فقط!
    // نستخدم $model->debts() لضمان أننا نبحث فقط في العمليات المرتبطة به
    $operations = $model ? $model->debts()
        ->where('amount', '>', 0)
        ->orderByDesc('date')
        ->get() : collect();
@endphp

<div id="{{ $finalModalId }}"
     x-data="{ activeOpId: null, partialAmount: null }"
     class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm overflow-y-auto py-10">

    <div class="w-full max-w-2xl px-4">
        <div class="bg-gray-900 border border-gray-800 shadow-2xl rounded-2xl overflow-hidden">

            {{-- 1. الهيدر --}}
            <div class="bg-gray-800/50 p-6 border-b border-gray-700 flex justify-between items-center">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-file-invoice-dollar text-red-500"></i>
                    مديونيات: {{ $model->name }}
                </h2>
                <button type="button" onclick="document.getElementById('{{ $finalModalId }}').classList.add('hidden')" class="text-gray-400 hover:text-white text-2xl">&times;</button>
            </div>

            <div class="p-6 space-y-8">

                {{-- 2. قسم الإضافة (التسجيل الجديد) --}}
                <section>
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                        تسجيل مديونية جديدة
                    </h3>
                    <form method="POST" action="{{ route('user.employees.debt.store', $model->id) }}" class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-800/30 p-4 rounded-xl border border-gray-800">
                        @csrf
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">المبلغ</label>
                            <input type="number" name="amount" step="0.01" required class="w-full bg-gray-800/80 border-gray-700/80 text-white rounded-xl px-3 py-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">التاريخ</label>
                            <input type="date" name="date" value="{{ now()->toDateString() }}" class="w-full bg-gray-800/80 border-gray-700/80 text-white rounded-xl px-3 py-2 focus:ring-red-500">
                        </div>
                        <div class="md:col-span-2 flex gap-2">
                            <input type="text" name="description" placeholder="الوصف (مثلاً: سلفة، عجز عهده...)" class="flex-1 bg-gray-800/80 border-gray-700/80 text-white rounded-xl px-3 py-2 text-sm focus:ring-red-500">
                            <button class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-xl font-bold transition">حفظ</button>
                        </div>
                    </form>
                </section>

                <div class="border-t border-gray-800"></div>

                {{-- 3. قسم التحصيل (السداد) --}}
                <section>
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                        المديونيات القائمة (للتحصيل)
                    </h3>

                    <div class="space-y-3 max-h-64 overflow-y-auto pr-2 custom-scrollbar">
                        @forelse($operations as $op)
                            <div class="bg-gray-800/40 border border-gray-700 rounded-xl p-4">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <div class="text-white font-bold">{{ number_format($op->amount, 2) }} ر.س</div>
                                        <div class="text-[10px] text-gray-500">{{ $op->date }} | {{ $op->description ?? 'بدون وصف' }}</div>
                                    </div>
                                    <button @click="activeOpId = (activeOpId === {{ $op->id }} ? null : {{ $op->id }})"
                                            class="bg-emerald-600/20 text-emerald-500 border border-emerald-600/30 px-4 py-1.5 rounded-xl text-xs font-bold hover:bg-emerald-600 hover:text-white transition">
                                        تحصيل
                                    </button>
                                </div>

                                {{-- خيارات التحصيل الجزئي والكامل (Alpine) --}}
                                <div x-show="activeOpId === {{ $op->id }}" x-transition class="mt-4 pt-4 border-t border-gray-700 space-y-3" x-cloak>
                                    <a href="{{ route('user.employees.debt.collect.full', $op->id) }}" class="block bg-emerald-600 text-white text-center py-2 rounded-xl text-xs font-bold">سداد كامل</a>

                                    <div class="flex gap-2">
                                        <input type="number" x-model="partialAmount" placeholder="مبلغ جزئي" class="flex-1 bg-gray-900 border-gray-700 text-white text-xs rounded-xl px-3 py-2">
                                        <button @click="
                                            if(!partialAmount || partialAmount <= 0) return alert('أدخل مبلغاً صحيحاً');
                                            let url = '{{ route('user.employees.debt.collect.partial', ['debt' => ':id', 'amount' => ':amount']) }}';
                                            window.location.href = url.replace(':id', {{ $op->id }}).replace(':amount', partialAmount);
                                        " class="bg-yellow-600 text-white px-4 py-2 rounded-xl text-xs font-bold">تأكيد</button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-6 text-gray-600 italic text-sm">لا توجد مديونيات حالية</div>
                        @endforelse
                    </div>
                </section>

            </div>
        </div>
    </div>
</div>
