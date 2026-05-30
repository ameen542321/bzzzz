@extends('dashboard.app')

@section('title', $store ? 'إضافة موظف - ' . $store->name : 'إضافة موظف جديد')

@section('content')

<div class="px-6 py-8 max-w-3xl mx-auto">

    {{-- زر الرجوع --}}
    <div class="mb-10 flex items-center justify-between">

        {{-- زر الرجوع (يمين) --}}
        <a href="{{ request('return_to', url()->previous()) }}"
           class="flex items-center gap-2 bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
            <i class="fa-solid fa-arrow-right text-lg"></i>
            <span>رجوع</span>
        </a>

        {{-- العنوان (وسط) --}}
        <div class="text-center flex-1">
            <h1 class="text-3xl font-bold text-white">
                {{ $store ? 'إضافة موظف لمتجر ' . $store->name : 'إضافة موظف جديد' }}
            </h1>

            <p class="text-gray-400 mt-1 text-sm">
                {{ $store ? 'سيتم ربط الموظف بهذا المتجر تلقائيًا' : 'قم بإضافة موظف وربطه بالمتجر المناسب' }}
            </p>
        </div>

        {{-- يسار فارغ (للتوازن البصري) --}}
        <div class="w-24"></div>

    </div>

    {{-- بطاقة النموذج --}}
    <div class="bg-gray-900 border border-gray-800 shadow-xl rounded-xl p-8">

        <form action="{{ route('user.employees.store') }}" method="POST" class="space-y-6">
            @csrf

            {{-- return_to --}}
            <input type="hidden" name="return_to" value="{{ request('return_to') }}">

            {{-- إذا جئت من صفحة المتجر --}}
            @if($store)
                <input type="hidden" name="store_id" value="{{ $store->id }}">

                <div>
                    <label class="block text-gray-300 font-medium mb-1">المتجر</label>
                    <div class="relative">
                        <input type="text" value="{{ $store->name }}" disabled
                               class="w-full bg-gray-800 border border-gray-700 text-gray-400 rounded-lg px-10 py-2">
                        <i class="fa-solid fa-store text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    </div>
                </div>

            @else
                {{-- قائمة المتاجر --}}
                <div>
                    <label class="block text-gray-300 font-medium mb-1">المتجر</label>
                    <div class="relative">
                        <select name="store_id" required
                                class="w-full bg-gray-800 border border-gray-700 text-gray-200 rounded-lg px-10 py-2
                                       focus:ring-blue-500 focus:border-blue-500">
                            <option value="">اختر متجرًا</option>
                            @foreach ($stores as $st)
                                <option value="{{ $st->id }}">{{ $st->name }}</option>
                            @endforeach
                        </select>
                        <i class="fa-solid fa-store text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    </div>
                </div>
            @endif

            {{-- الاسم --}}
            <div>
                <label class="block text-gray-300 font-medium mb-1">اسم الموظف</label>
                <div class="relative">
                    <input type="text" name="name" required placeholder="مثال: محمد أحمد"
                           class="w-full bg-gray-800 border border-gray-700 text-gray-200 rounded-lg px-10 py-2
                                  focus:ring-blue-500 focus:border-blue-500">
                    <i class="fa-solid fa-user text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
                </div>
            </div>

            {{-- الجوال --}}
            <div>
                <label class="block text-gray-300 font-medium mb-1">رقم الجوال</label>
                <div class="relative">
                    <input type="text" name="phone" placeholder="05xxxxxxxx"
                           class="w-full bg-gray-800 border border-gray-700 text-gray-200 rounded-lg px-10 py-2
                                  focus:ring-blue-500 focus:border-blue-500">
                    <i class="fa-solid fa-phone text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
                </div>
            </div>

            {{-- الراتب --}}
            <div>
                <label class="block text-gray-300 font-medium mb-1">الراتب الشهري</label>
                <div class="relative">
                    <input type="number" name="salary" required step="0.01" placeholder="مثال: 3500"
                           class="w-full bg-gray-800 border border-gray-700 text-gray-200 rounded-lg px-10 py-2
                                  focus:ring-blue-500 focus:border-blue-500">
                    <i class="fa-solid fa-money-bill text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
                </div>
            </div>

            {{-- زر الحفظ --}}
            <div class="pt-4">
                <button
                    class="w-full bg-blue-600 text-white px-6 py-2.5 rounded-lg shadow hover:bg-blue-700 transition font-semibold">
                    <i class="fa-solid fa-check mr-2"></i>
                    إضافة الموظف
                </button>
            </div>

        </form>

    </div>

</div>

@endsection
