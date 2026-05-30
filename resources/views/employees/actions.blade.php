@extends('dashboard.app')
@php
    $hasOldAccount = $employee->accountant ? true : false;
    $label = $hasOldAccount ? 'المحاسب' : 'الموظف';
@endphp

@section('title', $label . ' - ' . $employee->name)

@section('content')

<div class="px-6 py-8 max-w-5xl mx-auto">

    @php
        $hasOldAccount = $employee->accountant ? true : false;
        $label = $hasOldAccount ? 'المحاسب' : 'الموظف';
    @endphp

   <div class="flex items-center justify-between mb-10">

    {{-- زر الرجوع (يمين) --}}
    <a href="{{ $returnTo ?? request('return_to', route('user.employees.index')) }}"
       class="flex items-center gap-2 bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
        <i class="fa-solid fa-arrow-right text-lg"></i>
        <span>رجوع</span>
    </a>

    {{-- العنوان (وسط) --}}
    <div class="text-center flex-1">
        <h1 class="text-3xl font-bold text-gray-100">
            {{ $label }} — {{ $employee->name }}
        </h1>
        <p class="text-gray-400 mt-1 text-sm">
            عرض وإدارة بيانات المحاسب
        </p>
    </div>

    {{-- يسار فارغ للتوازن --}}
    <div class="w-24"></div>

</div>


    <!-- العمليات -->
    <div class="bg-gray-900/80 border border-gray-800 rounded-2xl p-6 md:p-7 shadow-xl backdrop-blur-sm">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-xl font-bold text-gray-100">عمليات الموظف</h2>
            <p class="text-xs text-gray-500">تصميم موحد وبألوان هادئة لسهولة الاستخدام</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
            <a href="/user/send-all-reports"
               class="group bg-gray-800/70 hover:bg-gray-800 text-gray-100 rounded-xl border border-gray-700/80 p-5 transition-all hover:-translate-y-0.5">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 rounded-xl bg-blue-500/15 text-blue-300 flex items-center justify-center ring-1 ring-blue-500/30">
                        <i class="fa-solid fa-paper-plane text-lg"></i>
                    </span>
                    <div>
                        <p class="font-semibold">إرسال تقارير الجميع</p>
                        <p class="text-xs text-gray-400 mt-1">تنفيذ سريع للتقارير</p>
                    </div>
                </div>
            </a>

            @php
                $actionCards = [
                    ['modal' => 'employeeDetailsModal', 'title' => 'بيانات المستخدم', 'hint' => 'مراجعة ملف الموظف', 'icon' => 'fa-id-card', 'accent' => 'blue'],
                    ['modal' => 'withdrawalModal', 'title' => 'سحب', 'hint' => 'تسجيل عملية سحب', 'icon' => 'fa-money-bill-transfer', 'accent' => 'sky'],
                    ['modal' => 'absenceModal', 'title' => 'غياب', 'hint' => 'إضافة يوم غياب', 'icon' => 'fa-user-xmark', 'accent' => 'amber'],
                    ['modal' => 'debtModal', 'title' => 'مديونية', 'hint' => 'تسجيل مديونية', 'icon' => 'fa-hand-holding-dollar', 'accent' => 'rose'],
                    ['modal' => 'creditSaleModal', 'title' => 'بيع آجل', 'hint' => 'إنشاء عملية بيع آجل', 'icon' => 'fa-cart-shopping', 'accent' => 'violet'],
                    ['modal' => 'creditSaleCollectionModal', 'title' => 'تحصيل', 'hint' => 'تحصيل من المديونية', 'icon' => 'fa-sack-dollar', 'accent' => 'emerald'],
                ];
            @endphp

            @foreach($actionCards as $card)
                <button type="button"
                        onclick="document.getElementById('{{ $card['modal'] }}').classList.remove('hidden')"
                        class="group text-right bg-gray-800/70 hover:bg-gray-800 text-gray-100 rounded-xl border border-gray-700/80 p-5 transition-all hover:-translate-y-0.5 w-full">
                    <div class="flex items-center gap-3">
                        <span class="w-11 h-11 rounded-xl bg-{{ $card['accent'] }}-500/15 text-{{ $card['accent'] }}-300 flex items-center justify-center ring-1 ring-{{ $card['accent'] }}-500/30">
                            <i class="fa-solid {{ $card['icon'] }} text-lg"></i>
                        </span>
                        <div>
                            <p class="font-semibold">{{ $card['title'] }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ $card['hint'] }}</p>
                        </div>
                    </div>
                </button>
            @endforeach

            @if($employee->accountant && $employee->accountant->status === 'active')
                <form action="{{ route('user.employees.demote', $employee->id) }}" method="POST" class="sm:col-span-2 xl:col-span-3">
                    @csrf
                    <button class="w-full bg-gray-800 hover:bg-gray-700 text-gray-100 border border-orange-500/40 font-semibold py-3 rounded-xl transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-arrow-rotate-left text-orange-300"></i>
                        <span>إرجاع إلى موظف</span>
                    </button>
                </form>
            @else
                <button type="button"
                        onclick="document.getElementById('promoteModal').classList.remove('hidden')"
                        class="sm:col-span-2 xl:col-span-3 w-full bg-gray-800 hover:bg-gray-700 text-gray-100 border border-indigo-500/40 font-semibold py-3 rounded-xl transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-arrow-up text-indigo-300"></i>
                    <span>ترقية إلى محاسب</span>
                </button>
            @endif
        </div>
    </div>

    <!-- سجل العمليات -->
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-8 shadow-xl mt-12">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-10">
        <div>
            <h2 class="text-3xl font-bold text-gray-100">سجل العمليات</h2>
            <p class="text-gray-500 text-sm mt-1">آخر الأنشطة المسجلة على هذا الموظف</p>
        </div>

        <a href="{{ route('user.employees.logs', $employee->id) }}?return_to={{ urlencode(url()->current()) }}"
           class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-lg transition shadow-lg">
            <i class="fa-solid fa-clock-rotate-left text-lg"></i>
            <span class="font-semibold">عرض السجل كاملًا</span>
        </a>
    </div>

    @php
        $recentLogs = $employee->logs()->latest()->take(5)->get();

        // Badge System
        $map = [
            'withdraw' => [
                'label' => 'سحب نقدي',
                'color' => 'text-blue-400',
                'icon'  => 'fa-money-bill-transfer'
            ],
            'absence' => [
                'label' => 'غياب',
                'color' => 'text-yellow-400',
                'icon'  => 'fa-user-xmark'
            ],
            'debt' => [
                'label' => 'مديونية',
                'color' => 'text-red-400',
                'icon'  => 'fa-circle-exclamation'
            ],
            'collect' => [
                'label' => 'تحصيل مديونية',
                'color' => 'text-green-400',
                'icon'  => 'fa-hand-holding-dollar'
            ],
            'sale_credit' => [
                'label' => 'بيع آجل',
                'color' => 'text-purple-400',
                'icon'  => 'fa-file-invoice-dollar'
            ],
            'store_transfer' => [
                'label' => 'نقل بين المتاجر',
                'color' => 'text-indigo-400',
                'icon'  => 'fa-right-left'
            ],
            'salary_update' => [
                'label' => 'تعديل راتب',
                'color' => 'text-gray-400',
                'icon'  => 'fa-sack-dollar'
            ],
        ];
    @endphp

    @forelse ($recentLogs as $log)

        @php
            $action = $map[$log->action] ?? [
                'label' => $log->action,
                'color' => 'text-gray-400',
                'icon'  => 'fa-circle-dot'
            ];

            // details JSON
            $details = is_array($log->details) ? $log->details : json_decode($log->details, true);
        @endphp

        {{-- Log Item --}}
        <div class="flex items-start justify-between py-6 border-b border-gray-800 last:border-none">

            {{-- Left --}}
            <div class="flex items-start gap-4">

                {{-- Icon --}}
                <div class="w-12 h-12 rounded-xl bg-gray-800 flex items-center justify-center shadow-inner">
                    <i class="fa-solid {{ $action['icon'] }} {{ $action['color'] }} text-xl"></i>
                </div>

                {{-- Text --}}
                <div class="space-y-1">
                    <p class="text-lg font-semibold {{ $action['color'] }}">
                        {{ $action['label'] }}
                    </p>

                    <p class="text-gray-400 text-sm leading-relaxed">
                        {{ $log->description }}
                    </p>

                    {{-- Details --}}
                    @if($details)
                        <div class="text-gray-500 text-xs mt-2 space-y-1">
                            @foreach($details as $key => $value)
                                <div>{{ $key }}: {{ $value }}</div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>

            {{-- Date --}}
            <p class="text-gray-500 text-sm whitespace-nowrap">
                {{ optional($log->created_at)->format('Y-m-d H:i') }}
            </p>

        </div>

    @empty

        <div class="py-12 text-center text-gray-500">
            لا يوجد عمليات حتى الآن
        </div>

    @endforelse

</div>




</div>

<!-- المودالات -->
{{-- @include('components.employee.debt-operations-modal', ['person' => $employee]) --}}
@include('components.employee.details-modal', ['person' => $employee])
@include('components.employee.withdrawal-form', ['person' => $employee])
@include('components.employee.absence-form', ['person' => $employee])
@include('components.employee.debt-form', ['person' => $employee])
@include('components.employee.credit-sale-form', ['person' => $employee])
@include('components.employee.credit-sale-collection', ['person' => $employee])


<!-- مودال الترقية -->
<div id="promoteModal" class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50">
    <div class="bg-gray-900 border border-gray-700 rounded-xl p-8 w-full max-w-md">

        <h2 class="text-xl font-bold text-gray-100 mb-6">ترقية الموظف إلى محاسب</h2>

        <form action="{{ route('user.employees.promote', $employee->id) }}" method="POST">
            @csrf

            @if($employee->accountant)
                <div class="mb-4 p-3 bg-yellow-600 text-white rounded-lg">
                    هذا الموظف لديه بريد محاسب سابق. يمكنك فقط إعادة تعيين كلمة المرور أو تركها كما هي.
                </div>

                <label class="block text-gray-300 mb-2">البريد الإلكتروني (غير قابل للتعديل)</label>
                <input type="text"
                       value="{{ $employee->accountant->email }}"
                       readonly
                       class="w-full bg-gray-800 border border-gray-700 text-gray-400 rounded-lg p-3 mb-4 cursor-not-allowed select-all">

            @else
                <label class="block text-gray-300 mb-2">البريد الإلكتروني</label>
                <input type="email" name="email" id="emailInput"
                       pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}"
                       class="w-full bg-gray-800 border border-gray-700 text-gray-200 rounded-lg p-3 mb-2"
                       placeholder="example@mail.com" required>

                <div id="emailExistsWarning"
                     class="hidden mb-4 p-3 bg-red-600 text-white rounded-lg text-sm">
                    هذا البريد مستخدم مسبقًا. الرجاء إدخال بريد آخر.
                </div>
            @endif

            <label class="block text-gray-300 mb-2">
                كلمة المرور @if($employee->accountant) <span class="text-gray-400">(اختياري)</span> @endif
            </label>

            <input type="password" name="password"
                   class="w-full bg-gray-800 border border-gray-700 text-gray-200 rounded-lg p-3 mb-4"
                   placeholder="********">

            <div class="flex items-center justify-between mt-6">
                <button type="button"
                        onclick="document.getElementById('promoteModal').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600">
                    إلغاء
                </button>

                <button id="promoteSubmit"
                        type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    تأكيد الترقية
                </button>
            </div>
        </form>

    </div>
</div>


<!-- فحص الإيميل عبر AJAX -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    const emailInput   = document.getElementById('emailInput');
    const warningBox   = document.getElementById('emailExistsWarning');
    const submitButton = document.getElementById('promoteSubmit');
    const form         = document.querySelector('#promoteModal form');

    // منع إرسال النموذج إذا كان الزر معطلاً
    if (form) {
        form.addEventListener('submit', function (e) {
            if (submitButton.disabled) {
                e.preventDefault();
            }
        });
    }

    // فحص البريد عبر AJAX
    if (emailInput) {
        emailInput.addEventListener('input', function () {

            // منع الرموز غير المسموح بها
            const invalidPattern = /[^a-zA-Z0-9@._\-+]/;
            if (invalidPattern.test(emailInput.value)) {
                warningBox.textContent = "البريد يحتوي على حروف أو رموز غير مسموح بها.";
                warningBox.classList.remove('hidden');
                submitButton.disabled = true;
                submitButton.classList.add('opacity-50', 'cursor-not-allowed');
                return;
            }

            fetch("{{ route('user.employees.checkEmail') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ email: emailInput.value })
            })
            .then(res => res.json())
            .then(data => {
                if (data.exists) {
                    warningBox.textContent = "هذا البريد مستخدم مسبقًا. الرجاء إدخال بريد آخر.";
                    warningBox.classList.remove('hidden');
                    submitButton.disabled = true;
                    submitButton.classList.add('opacity-50', 'cursor-not-allowed');
                } else {
                    warningBox.classList.add('hidden');
                    submitButton.disabled = false;
                    submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            })
            .catch(() => {
                warningBox.classList.add('hidden');
                submitButton.disabled = false;
                submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
            });

        });
    }

});
</script>

@endsection
