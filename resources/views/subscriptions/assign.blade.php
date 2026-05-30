@extends('dashboard.app')

@section('content')

<h1 class="text-2xl font-semibold mb-6">تفعيل اشتراك لمستخدم</h1>

<div class="bg-gray-800 border border-gray-700 rounded-xl p-6 max-w-2xl mx-auto">

    <form>

        {{-- اختيار المستخدم --}}
        <div class="mb-4">
            <label class="text-gray-300 mb-1 block">المستخدم</label>
            <select class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-200">
                {{-- لاحقًا foreach --}}
                <option value="1">أحمد – مدير فرع</option>
                <option value="2">سعود – محاسب</option>
                <option value="3">محمد – كاشير</option>
            </select>
        </div>

        {{-- اختيار الخطة --}}
        <div class="mb-4">
            <label class="text-gray-300 mb-1 block">الخطة</label>
            <select class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-200">
                {{-- لاحقًا foreach --}}
                <option value="basic">الخطة الأساسية – 49 ريال</option>
                <option value="pro">الخطة المتقدمة – 99 ريال</option>
                <option value="enterprise">الخطة الاحترافية – 199 ريال</option>
            </select>
        </div>

        {{-- اختيار مدة جاهزة --}}
        <div class="mb-4">
            <label class="text-gray-300 mb-1 block">المدة</label>
            <select id="duration_select"
                    class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-200">
                <option value="">اختر مدة</option>
                <option value="30">شهر واحد</option>
                <option value="90">3 أشهر</option>
                <option value="180">6 أشهر</option>
                <option value="365">سنة كاملة</option>
            </select>
        </div>

        {{-- إدخال يدوي للمدة --}}
        <div class="mb-4">
            <label class="text-gray-300 mb-1 block">إدخال يدوي (عدد الأيام)</label>
            <input type="number" min="1"
                   id="manual_days"
                   class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-200"
                   placeholder="مثال: 45 يوم">
        </div>

        {{-- ملاحظة توضيحية --}}
        <div class="bg-gray-900 border border-gray-700 rounded p-4 text-gray-400 text-sm mb-6">
            ملاحظة: إذا تم اختيار مدة جاهزة **و** إدخال مدة يدوية، سيتم استخدام الإدخال اليدوي.
        </div>

        {{-- زر التفعيل --}}
        <div class="flex justify-end mt-6">
            <button class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded text-white">
                تفعيل الاشتراك
            </button>
        </div>

    </form>

</div>

@endsection
