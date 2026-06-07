@extends('dashboard.app')

@section('content')

<div class="container mx-auto px-4">
    {{-- عنوان الصفحة --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <h1 class="text-2xl font-semibold">تجديد الاشتراك</h1>
        <div class="flex flex-wrap gap-2">
            <a href="" class="bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded text-white text-sm transition">
                <i class="fas fa-history ml-2"></i>
                سجل الاشتراكات
            </a>
            <a href="" class="bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded text-white text-sm transition">
                <i class="fas fa-arrow-right ml-2"></i>
                العودة للرئيسية
            </a>
        </div>
    </div>

    {{-- رسالة النجاح --}}
    @if(session('subscription_success'))
        <div class="bg-green-500 text-white p-4 rounded-lg mb-6 flex items-center animate-pulse">
            <i class="fas fa-check-circle ml-2 text-xl"></i>
            {{ session('subscription_success') }}
        </div>
    @endif

    {{-- معلومات المستخدم والاشتراك الحالي --}}
    <div class="bg-gradient-to-br from-gray-800 to-gray-900 border border-gray-700 rounded-xl p-6 mb-8">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center">
                <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-700 rounded-full flex items-center justify-center ml-4 shadow-lg">
                    <span class="text-xl font-bold">{{ substr($user->name, 0, 1) }}</span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white">{{ $user->name }}</h2>
                    <p class="text-gray-400 text-sm flex items-center">
                        <i class="fas fa-envelope ml-1 text-xs"></i>
                        {{ $user->email }}
                    </p>
                </div>
            </div>
            <span class="px-4 py-2 rounded-full text-sm font-semibold flex items-center
                @if($user->status === 'نشط') bg-green-500 bg-opacity-20 text-green-400 border border-green-500
                @else bg-red-500 bg-opacity-20 text-red-400 border border-red-500
                @endif">
                <i class="fas fa-circle text-xs ml-2"></i>
                {{ $user->status ?? 'غير نشط' }}
            </span>
        </div>

       @if($currentSubscription)
<div class="bg-gray-800 border border-gray-700 rounded-xl p-6 mb-8">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-bold text-white">الاشتراك الحالي</h3>
        <span class="px-3 py-1 rounded-full text-sm bg-green-500 bg-opacity-20 text-green-400">
            <i class="fas fa-circle text-xs ml-1"></i>
            نشط
        </span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <p class="text-sm text-gray-500">الخطة</p>
            <p class="text-white font-semibold">
                @switch($currentSubscription->type)
                    @case('basic') الخطة العادية @break
                    @case('silver') الخطة الفضية @break
                    @case('gold') الخطة الذهبية @break
                    @default {{ $currentSubscription->type }}
                @endswitch
            </p>
        </div>
        <div>
            <p class="text-sm text-gray-500">تاريخ الانتهاء</p>
            <p class="text-white">{{ \Carbon\Carbon::parse($currentSubscription->end_at)->format('Y-m-d') }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">المدة المتبقية</p>
            <p class="text-blue-400 font-semibold">
                @php
                    $daysLeft = \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($currentSubscription->end_at), false);
                @endphp
                @if($daysLeft > 0)
                    {{ floor($daysLeft) }} يوم
                @else
                    منتهي
                @endif
            </p>
        </div>
    </div>
</div>
@endif
    </div>

    {{-- عنوان اختيار الخطة --}}
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-white mb-2">اختر باقة التجديد</h2>
        <p class="text-gray-400 flex items-center">
            <i class="fas fa-gem ml-2 text-yellow-500"></i>
            جميع الباقات تأتي بمدة 6 أشهر مع إمكانية الترقية لاحقاً
        </p>
    </div>

    {{-- بطاقات الخطط --}}
    <form method="POST" action="{{ route('subscription.processRenew') }}" id="renewForm">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            @foreach($plans as $key => $plan)
            <div class="bg-gray-800 border-2 border-gray-700 rounded-xl p-6 hover:border-blue-500 transition-all duration-300 cursor-pointer plan-card relative {{ $key === 'silver' ? 'transform scale-105 border-blue-500 shadow-2xl shadow-blue-500/20 z-10' : '' }}" data-plan="{{ $key }}">

                @if($key === 'silver')
                <div class="absolute -top-4 right-4 bg-gradient-to-r from-yellow-400 to-yellow-500 text-black text-sm font-bold px-4 py-1 rounded-full shadow-lg flex items-center">
                    <i class="fas fa-star ml-1 text-xs"></i>
                    الأكثر طلباً
                </div>
                @endif

                @if($key === 'gold')
                <div class="absolute top-4 left-4">
                    <span class="bg-purple-600 text-white text-xs font-bold px-3 py-1 rounded-full flex items-center">
                        <i class="fas fa-crown ml-1 text-xs"></i>
                        VIP
                    </span>
                </div>
                @endif

                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-2xl font-bold text-white">{{ $plan['name'] }}</h3>
                    <div class="relative">
                        <input type="radio" name="plan" value="{{ $key }}" class="w-5 h-5 text-blue-600 accent-blue-600" {{ $key === 'silver' ? 'checked' : '' }}>
                    </div>
                </div>

                <div class="mb-4">
                    <span class="text-4xl font-bold text-blue-400">{{ number_format($plan['price']) }}</span>
                    <span class="text-gray-400 mr-1">ريال</span>
                    <span class="block text-sm text-gray-500 mt-1">
                        <i class="far fa-clock ml-1"></i>
                        لمدة 6 أشهر
                    </span>
                </div>

                <ul class="space-y-3 text-gray-300 mb-6">
                    @foreach($plan['features'] as $feature)
                    <li class="flex items-center">
                        <i class="fas fa-check-circle text-green-400 ml-2 text-sm"></i>
                        <span>{{ $feature }}</span>
                    </li>
                    @endforeach
                </ul>

                <div class="text-center border-t border-gray-700 pt-4">
                    @php
                        $discount = $key === 'gold' ? 30 : ($key === 'silver' ? 20 : 10);
                    @endphp
                    <span class="text-sm bg-blue-900 bg-opacity-40 text-blue-300 px-3 py-1 rounded-full">
                        <i class="fas fa-gift ml-1"></i>
                        وفر {{ $discount }}% مقارنة بالاشتراك الشهري
                    </span>
                </div>
            </div>
            @endforeach
        </div>

        {{-- ملخص الطلب --}}
        <div class="bg-gradient-to-br from-gray-800 to-gray-900 border border-gray-700 rounded-xl p-6 mb-6">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                <i class="fas fa-file-invoice ml-2 text-blue-400"></i>
                ملخص الطلب
            </h3>

            <div class="space-y-3">
                <div class="flex justify-between items-center text-gray-300">
                    <span>قيمة الخطة</span>
                    <span id="planPrice" class="font-semibold">1,400 ريال</span>
                </div>
                <div class="flex justify-between items-center text-gray-300">
                    <span>الضريبة (15%)</span>
                    <span id="taxAmount" class="font-semibold">210 ريال</span>
                </div>
                <div class="border-t border-gray-700 my-2"></div>
                <div class="flex justify-between items-center text-white">
                    <span class="text-lg font-bold">الإجمالي النهائي</span>
                    <span class="text-3xl font-bold text-blue-400" id="totalPrice">1,610 <span class="text-sm text-gray-400">ريال</span></span>
                </div>
            </div>
        </div>

        {{-- شروط وأحكام --}}
        <div class="mb-6">
            <label class="flex items-center cursor-pointer group">
                <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600 rounded bg-gray-700 border-gray-600 focus:ring-blue-500" required>
                <span class="mr-2 text-gray-300 text-sm group-hover:text-white transition">
                    أوافق على <a href="#" class="text-blue-400 hover:underline font-semibold">شروط وأحكام</a> التجديد، وأقر بأن المبلغ غير قابل للاسترداد.
                </span>
            </label>
        </div>

        {{-- أزرار التحكم --}}
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <a href="" class="w-full sm:w-auto bg-gray-700 hover:bg-gray-600 px-8 py-3 rounded text-white transition text-center">
                <i class="fas fa-times ml-2"></i>
                إلغاء
            </a>
            <button type="submit" class="w-full sm:w-auto bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 px-10 py-3 rounded text-white font-semibold transition transform hover:scale-105 shadow-lg flex items-center justify-center" id="submitBtn">
                <i class="fas fa-check ml-2"></i>
                تأكيد التجديد
            </button>
        </div>
    </form>

    {{-- عرض آخر 3 اشتراكات سابقة --}}
    @if($allSubscriptions->count() > 0)
    <div class="mt-12">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
            <i class="fas fa-history ml-2 text-gray-400"></i>
            آخر الاشتراكات السابقة
        </h3>
        <div class="bg-gray-800 border border-gray-700 rounded-xl overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-900">
                    <tr>
                        <th class="text-right py-3 px-4 text-gray-400 text-sm">النوع</th>
                        <th class="text-right py-3 px-4 text-gray-400 text-sm">المبلغ</th>
                        <th class="text-right py-3 px-4 text-gray-400 text-sm">تاريخ البداية</th>
                        <th class="text-right py-3 px-4 text-gray-400 text-sm">تاريخ النهاية</th>
                        <th class="text-right py-3 px-4 text-gray-400 text-sm">الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allSubscriptions->take(3) as $sub)
                    <tr class="border-t border-gray-700 hover:bg-gray-750">
                        <td class="py-3 px-4 text-gray-300">
                            @switch($sub->type)
                                @case('monthly') شهري @break
                                @case('yearly') سنوي @break
                                @case('trial') تجريبي @break
                                @default {{ $sub->type }}
                            @endswitch
                        </td>
                        <td class="py-3 px-4 text-gray-300">{{ number_format($sub->price) }} ريال</td>
                        <td class="py-3 px-4 text-gray-300">{{ \Carbon\Carbon::parse($sub->start_at)->format('Y-m-d') }}</td>
                        <td class="py-3 px-4 text-gray-300">{{ \Carbon\Carbon::parse($sub->end_at)->format('Y-m-d') }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-1 rounded text-xs
                                @if($sub->status === 'نشط') bg-green-500 bg-opacity-20 text-green-400
                                @elseif($sub->status === 'ملغي') bg-red-500 bg-opacity-20 text-red-400
                                @else bg-gray-500 bg-opacity-20 text-gray-400
                                @endif">
                                {{ $sub->status }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @if($allSubscriptions->count() > 3)
            <div class="p-4 border-t border-gray-700 text-center">
                <a href="{{ route('subscription.history') }}" class="text-blue-400 hover:text-blue-300 text-sm">
                    عرض جميع الاشتراكات ({{ $allSubscriptions->count() }})
                    <i class="fas fa-arrow-left mr-1 text-xs"></i>
                </a>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>

@endsection

<!-- @push('scripts') -->
<script>
    // تعريف أسعار الخطط مع الضريبة
    const plans = {
        basic: { price: 500, tax: 75, total: 575 },
        silver: { price: 1400, tax: 210, total: 1610 },
        gold: { price: 2700, tax: 405, total: 3105 }
    };

    // تحديث الأسعار عند اختيار خطة
    function updatePrices(planKey) {
        const plan = plans[planKey];
        if (plan) {
            document.getElementById('planPrice').innerText = plan.price.toLocaleString() + ' ريال';
            document.getElementById('taxAmount').innerText = plan.tax.toLocaleString() + ' ريال';
            document.getElementById('totalPrice').innerHTML = plan.total.toLocaleString() + ' <span class="text-sm text-gray-400">ريال</span>';
        }
    }

    // تفعيل البطاقات عند النقر
    document.querySelectorAll('.plan-card').forEach(card => {
        card.addEventListener('click', function() {
            // إزالة التحديد من جميع البطاقات
            document.querySelectorAll('.plan-card').forEach(c => {
                c.classList.remove('border-blue-500', 'shadow-2xl', 'shadow-blue-500/20', 'scale-105', 'z-10');
                c.classList.add('border-gray-700');
            });

            // إضافة التحديد للبطاقة الحالية
            this.classList.remove('border-gray-700');
            this.classList.add('border-blue-500', 'shadow-2xl', 'shadow-blue-500/20');

            // إذا كانت البطاقة الفضية، أضف scale
            if (this.dataset.plan === 'silver') {
                this.classList.add('scale-105', 'z-10');
            }

            const radio = this.querySelector('input[type="radio"]');
            radio.checked = true;

            // تحديث الأسعار
            updatePrices(radio.value);
        });
    });

    // تحديد الخطة الفضية افتراضياً عند تحميل الصفحة
    window.addEventListener('load', function() {
        updatePrices('silver');
    });

    // تفعيل زر التأكيد عند الموافقة على الشروط
    document.querySelector('.form-checkbox').addEventListener('change', function() {
        const submitBtn = document.getElementById('submitBtn');
        if (this.checked) {
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            submitBtn.disabled = false;
        } else {
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            submitBtn.disabled = true;
        }
    });

    // تعطيل زر التأكيد في البداية حتى يتم الموافقة على الشروط
    document.getElementById('submitBtn').disabled = true;
    document.getElementById('submitBtn').classList.add('opacity-50', 'cursor-not-allowed');
</script>
<!-- @endpush -->

<!-- @push('styles') -->
<style>
    .plan-card {
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .plan-card:hover {
        transform: translateY(-5px);
    }

    .plan-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #3b82f6, #8b5cf6);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .plan-card:hover::before {
        transform: scaleX(1);
    }

    .form-checkbox:checked {
        background-color: #3b82f6;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }

    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
</style>
<!-- @endpush -->
