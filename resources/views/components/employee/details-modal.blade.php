@props([
'employee',
'modalId' => 'employeeDetailsModal',
'type' => 'الموظف'
])

@php
$totalWithdrawals = $employee->withdrawals()->sum('amount');
$totalAbsenceDays = $employee->absences()->count();
$totalDebt = $employee->debts()->sum('amount');
$pendingCredit = $employee->creditSales()->where('status', 'pending')->sum('amount');
$collectedCredit = $employee->creditSales()->where('status', 'deducted')->sum('amount');

$storeName = $employee->store->name ?? 'غير مرتبط';
$ownerName = $employee->store->user->name ?? '—';
@endphp

<div id="{{ $modalId }}"
    class="hidden fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/60 backdrop-blur-sm">

    {{-- حاوية التمركز مع Padding علوي --}}
    <div class="w-full max-w-3xl px-4 py-8">

        {{-- المحتوى الفعلي --}}
        <div class="bg-gray-900/95 border border-gray-800 shadow-2xl rounded-2xl p-8 md:p-9">

            {{-- العنوان + زر الإغلاق --}}
            <div class="flex items-center justئئئify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-100">
                    تفاصيل {{ $type }}: {{ $employee->name }}
                </h2>

                <button type="button"
                    onclick="document.getElementById('{{ $modalId }}').classList.add('hidden')"
                    class="w-9 h-9 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-xl text-sm transition flex items-center justify-center"><i class="fa-solid fa-xmark"></i></button>
            </div>

            {{-- المحتوى --}}
            <div class="space-y-8">

                {{-- بيانات أساسية --}}
                <div class="space-y-3">
                    @php
                    $basicInfo = [
                    ['icon' => 'fa-user', 'label' => 'اسم الموظف', 'value' => $employee->name],
                    ['icon' => 'fa-phone', 'label' => 'رقم الجوال', 'value' => $employee->phone ?? '—'],
                    ['icon' => 'fa-money-bill','label' => 'الراتب الشهري','value' => number_format($employee->salary, 2) . ' ريال'],
                    ['icon' => 'fa-store', 'label' => 'المتجر', 'value' => $storeName],
                    ['icon' => 'fa-user-tie', 'label' => 'اسم المالك', 'value' => $ownerName],
                    ];
                    @endphp

                    @foreach ($basicInfo as $item)
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400 flex items-center gap-2">
                            <i class="fa-solid {{ $item['icon'] }} text-gray-500"></i>
                            {{ $item['label'] }}
                        </span>
                        <span class="text-gray-100 font-semibold">
                            {{ $item['value'] }}
                        </span>
                    </div>
                    @endforeach
                </div>

                <hr class="border-gray-800">

                {{-- ملخص العمليات --}}
                <div class="space-y-4">
                    <h4 class="text-lg font-bold text-gray-100">ملخص العمليات</h4>

                    @php
                    $summary = [
                    [
                    'icon' => 'fa-money-bill-transfer',
                    'color' => 'text-blue-500',
                    'label' => 'إجمالي السحوبات',
                    'value' => number_format($totalWithdrawals, 2) . ' ريال',
                    ],
                    [
                    'icon' => 'fa-user-xmark',
                    'color' => 'text-yellow-500',
                    'label' => 'عدد أيام الغياب',
                    'value' => $totalAbsenceDays . ' يوم',
                    ],
                    [
                    'icon' => 'fa-hand-holding-dollar',
                    'color' => 'text-red-500',
                    'label' => 'إجمالي المديونية',
                    'value' => number_format($totalDebt, 2) . ' ريال',
                    ],
                    [
                    'icon' => 'fa-cart-shopping',
                    'color' => 'text-purple-500',
                    'label' => 'بيع آجل غير محصّل',
                    'value' => number_format($pendingCredit, 2) . ' ريال',
                    ],
                    [
                    'icon' => 'fa-sack-dollar',
                    'color' => 'text-green-500',
                    'label' => 'بيع آجل محصّل',
                    'value' => number_format($collectedCredit, 2) . ' ريال',
                    ],
                    ];
                    @endphp

                    <div class="space-y-3">
                        @foreach ($summary as $item)
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400 flex items-center gap-2">
                                <i class="fa-solid {{ $item['icon'] }} {{ $item['color'] }}"></i>
                                {{ $item['label'] }}
                            </span>
                            <span class="{{ $item['color'] }} font-semibold">
                                {{ $item['value'] }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <hr class="border-gray-800">

                {{-- زر تصدير PDF --}}
                <div>
                    <a href="{{ route('user.employees.exportLog', $employee->id) }}"
                        onclick="if(!confirm('تنبيه مهم: عند تصدير التقرير يتم تصفير السحب والغياب والمديونيات للشهر المستهدف (إذا كان اليوم 1-3 فسيتم اعتماد الشهر السابق، وباقي الأيام الشهر الحالي) مع تصفير الآجل المحصّل فقط. إذا فُقد الملف بعد التصدير أو لم تتمكن من حفظه فلن تستطيع الحصول عليه مرة أخرى. هل تريد المتابعة؟')) return false; document.getElementById('{{ $modalId }}').classList.add('hidden'); return true;"
                        class="w-full bg-indigo-600 hover:bg-indigo-500 text-white py-2.5 rounded-xl transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-file-pdf"></i>
                        تصدير ملخص العامل PDF
                    </a>
                </div>

            </div>

        </div>
    </div>
</div>