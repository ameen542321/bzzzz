@props(['person' => null, 'employee' => null])

@php
    // اختيار المتغير الصحيح
    $model = $person ?? $employee;

    // حماية من الانهيار
    if (!$model) {
        return;
    }
@endphp

<div id="debtOperationsModal-{{ $model->id }}"
     class="hidden fixed inset-0 z-50 flex justify-center items-center bg-black/50 backdrop-blur-sm">

    <div class="w-full max-w-lg p-4">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl shadow-2xl overflow-hidden">

            <!-- Header -->
            <div class="p-5 border-b border-gray-800 text-center">
                <h3 class="text-xl font-semibold text-gray-100">
                    مديونية {{ $model->name }}
                </h3>
                <p class="text-gray-400 text-sm mt-1">عرض العمليات المتبقية</p>
            </div>

            @php
                $operations = $model->debts()
                    ->where('amount', '>', 0)
                    ->orderByDesc('date')
                    ->orderByDesc('id')
                    ->get();
            @endphp

            <!-- Content -->
            <div class="p-5 space-y-4 max-h-[65vh] overflow-y-auto">

                @forelse($operations as $op)

                    <div class="bg-gray-800/40 rounded-xl border border-gray-700 p-4">

                        <div class="flex justify-between items-center">
                            <div>
                                <div class="text-gray-200 font-medium">
                                    {{ number_format($op->amount, 2) }} ريال
                                </div>
                                <div class="text-gray-400 text-xs mt-1">
                                    {{ $op->description ?? 'بدون وصف' }}
                                </div>
                                <div class="text-gray-500 text-xs mt-1">
                                    {{ $op->date }}
                                </div>
                            </div>

                            <button onclick="toggleDebtActions({{ $op->id }})"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition">
                                تحصيل
                            </button>
                        </div>

                        <div id="actions-{{ $op->id }}" class="hidden mt-4 space-y-2">

                            <a href="{{ route('user.employees.debt.collect.full', $op->id) }}"
                               class="block bg-green-600 text-white text-center py-2 rounded-lg hover:bg-green-700 transition">
                                تحصيل كامل
                            </a>

                            <div class="bg-gray-800 p-3 rounded-lg border border-gray-700">
                                <input type="number"
                                       id="partialAmount-{{ $op->id }}"
                                       placeholder="مبلغ التحصيل"
                                       class="w-full bg-gray-700 text-white p-2 rounded mb-2 text-sm">

                                <button onclick="collectPartial({{ $op->id }})"
                                        class="w-full bg-yellow-600 text-white py-2 rounded-lg hover:bg-yellow-700 transition text-sm">
                                    تأكيد التحصيل الجزئي
                                </button>
                            </div>

                        </div>

                    </div>

                @empty

                    <div class="text-center text-gray-400 py-6">
                        لا توجد مديونيات متبقية
                    </div>

                @endforelse

            </div>

            <div class="p-4 border-t border-gray-800">
                <button type="button"
                        onclick="document.getElementById('debtOperationsModal-{{ $model->id }}').classList.add('hidden')"
                        class="w-full bg-gray-700 text-white py-2 rounded-lg hover:bg-gray-600 transition">
                    إغلاق
                </button>
            </div>

        </div>
    </div>
</div>

<script>
function toggleDebtActions(id) {
    document.getElementById('actions-' + id).classList.toggle('hidden');
}

function collectPartial(id) {
    const amount = document.getElementById('partialAmount-' + id).value;

    if (!amount || amount <= 0) {
        alert("الرجاء إدخال مبلغ صحيح");
        return;
    }

    window.location.href = "/user/debt/collect/partial/" + id + "/" + amount;
}
</script>