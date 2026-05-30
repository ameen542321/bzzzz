@extends('dashboard.app')

@section('content')

<h1 class="text-2xl font-semibold mb-6">اشتراكي</h1>

<div class="bg-gray-800 border border-gray-700 rounded-xl p-6 max-w-2xl mx-auto">

    {{-- اسم الخطة --}}
    <h2 class="text-3xl font-bold text-blue-400 mb-4">
        {{ $subscription->plan_name ?? 'لا يوجد اشتراك' }}
    </h2>

    {{-- حالة الاشتراك --}}
    <p class="text-gray-300 text-lg mb-4">
        الحالة:
        @if($subscription)
            <span class="text-green-400 font-semibold">نشط</span>
        @else
            <span class="text-red-400 font-semibold">غير نشط</span>
        @endif
    </p>

    @if($subscription)

        {{-- تاريخ بداية الاشتراك --}}
        <p class="text-gray-300 mb-2">
            تاريخ بداية الاشتراك:
            <span class="text-gray-200">{{ $subscription->start_date }}</span>
        </p>

        {{-- تاريخ انتهاء الاشتراك (محسوب) --}}
        <p class="text-gray-300 mb-2">
            تاريخ انتهاء الاشتراك:
            <span class="text-blue-400 font-semibold">
                {{ $subscription->end_date }}
            </span>
        </p>

        {{-- المدة المتبقية --}}
        <p class="text-gray-300 mb-6">
            المدة المتبقية:
            <span class="text-green-400 font-semibold">
                {{ $subscription->remaining_days }} يوم
            </span>
        </p>

        {{-- ملاحظة --}}
        <div class="bg-gray-900 border border-gray-700 rounded p-4 text-gray-400 text-sm mb-6">
            ملاحظة: تجديد الاشتراك أو إضافة مدة جديدة يتم فقط من قبل
            <span class="text-blue-400">المدير العام</span>.
        </div>

    @else

        <div class="bg-gray-900 border border-gray-700 rounded p-4 text-gray-400 text-sm mb-6">
            لا يوجد لديك اشتراك حالي. يرجى التواصل مع المدير العام لتفعيل الاشتراك.
        </div>

    @endif

    {{-- زر رجوع --}}
    <div class="flex justify-end">
        <a href="{{ route('dashboard') }}"
           class="bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded text-white">
            رجوع
        </a>
    </div>

</div>

@endsection
