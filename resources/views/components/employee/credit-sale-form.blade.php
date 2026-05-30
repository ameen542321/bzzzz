@props(['employee', 'modalId' => 'creditSaleModal'])

<div id="{{ $modalId }}"
     class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">

    <div class="w-full max-w-3xl px-4">
        <div class="bg-gray-900/95 border border-gray-800 shadow-2xl rounded-2xl p-8 md:p-9">

            {{-- العنوان + زر الإغلاق --}}
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-100">
                    إضافة بيع آجل — {{ $employee->name }}
                </h2>

                <button type="button"
                        onclick="document.getElementById('{{ $modalId }}').classList.add('hidden')"
                        class="w-9 h-9 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-xl text-sm transition flex items-center justify-center"><i class="fa-solid fa-xmark"></i></button>
            </div>

            {{-- الفورم --}}
            <form method="POST"
                  action="{{ route('user.employees.credit-sale.store', $employee->id) }}"
                  class="space-y-6">

                @csrf

                {{-- المبلغ --}}
                <div>
                    <label class="block text-gray-300 font-medium mb-1">المبلغ</label>
                    <div class="relative">
                        <input type="number" name="amount" step="0.01" required
                               class="w-full bg-gray-800/80 border border-gray-700/80 text-gray-200 rounded-xl px-10 py-2
                                      focus:ring-purple-500 focus:border-purple-500">
                        <i class="fa-solid fa-sack-dollar text-purple-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    </div>
                </div>

                {{-- الوصف --}}
                <div>
                    <label class="block text-gray-300 font-medium mb-1">الوصف (اختياري)</label>
                    <div class="relative">
                        <input type="text" name="description"
                               class="w-full bg-gray-800/80 border border-gray-700/80 text-gray-200 rounded-xl px-10 py-2
                                      focus:ring-purple-500 focus:border-purple-500">
                        <i class="fa-solid fa-align-right text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    </div>
                </div>

                {{-- التاريخ --}}
                <div>
    <label class="block text-gray-300 font-medium mb-1">تاريخ التسجيل</label>

    <div class="relative">
        <input type="text"
               name="date"
               id="dateInput-{{ $modalId }}"
               value="{{ now()->toDateString() }}"
               required
               class="w-full bg-gray-800/80 border border-gray-700/80 text-gray-200 rounded-xl px-10 py-2
                      focus:ring-yellow-500 focus:border-yellow-500 cursor-pointer"
               onclick="this.showPicker ? this.showPicker() : null">

        <input type="date"
               id="hiddenDate-{{ $modalId }}"
               class="absolute inset-0 opacity-0 cursor-pointer"
               value="{{ now()->toDateString() }}"
               onchange="document.getElementById('dateInput-{{ $modalId }}').value = this.value">

        <i class="fa-solid fa-calendar text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
    </div>
</div>

                {{-- زر الحفظ --}}
                <div class="pt-2">
                    <button
                        class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2.5 rounded-xl transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-check"></i>
                        حفظ عملية البيع الآجل
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
