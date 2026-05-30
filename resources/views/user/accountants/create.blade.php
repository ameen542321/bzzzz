@extends('dashboard.app')



@section('title', $store ? 'إضافة محاسب - ' . $store->name : 'إضافة محاسب جديد')



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
            {{ $store ? 'إضافة محاسب لمتجر ' . $store->name : 'إضافة محاسب جديد' }}
        </h1>

        <p class="text-gray-400 mt-1 text-sm">
            {{ $store ? 'سيتم انشاء ملف للمحاسب ضمن ملفات الموظفين وسيتم ربط المحاسب بهذا المتجر تلقائيًا' : 'قم بإضافة محاسب وربطه بالمتجر المناسب' }}
        </p>
    </div>

    {{-- يسار فارغ (للتوازن البصري) --}}
    <div class="w-24"></div>

</div>


    {{-- بطاقة النموذج --}}
    <div class="bg-gray-900 border border-gray-800 shadow-xl rounded-xl p-8">

        <form action="{{ route('user.accountants.store') }}" method="POST" class="space-y-6">
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
                <label class="block text-gray-300 font-medium mb-1">اسم المحاسب</label>
                <div class="relative">
                    <input type="text" name="name" required placeholder="مثال: أحمد علي"
                           class="w-full bg-gray-800 border border-gray-700 text-gray-200 rounded-lg px-10 py-2
                                  focus:ring-blue-500 focus:border-blue-500">
                    <i class="fa-solid fa-user text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
                </div>
            </div>

            {{-- البريد --}}
            <div>
                <label class="block text-gray-300 font-medium mb-1">البريد الإلكتروني</label>
                <div class="relative">
                    <input type="email" name="email" required placeholder="example@email.com"
                           class="w-full bg-gray-800 border border-gray-700 text-gray-200 rounded-lg px-10 py-2
                                  focus:ring-blue-500 focus:border-blue-500">
                    <i class="fa-solid fa-envelope text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
                </div>
            </div>

            {{-- الهاتف --}}
            <div>
                <label class="block text-gray-300 font-medium mb-1">رقم الهاتف</label>
                <div class="relative">
                    <input type="text" name="phone" required placeholder="05xxxxxxxx"
                           class="w-full bg-gray-800 border border-gray-700 text-gray-200 rounded-lg px-10 py-2
                                  focus:ring-blue-500 focus:border-blue-500">
                    <i class="fa-solid fa-phone text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
                </div>
            </div>
            {{-- حقل الراتب الجديد --}}
<div>
    <label class="block text-gray-300 font-medium mb-1">الراتب الشهري</label>
    <div class="relative">
        <input type="number" name="salary" step="0.01" required  placeholder="0.00"
               class="w-full bg-gray-800 border border-gray-700 text-gray-200 rounded-lg px-10 py-2
                                  focus:ring-blue-500 focus:border-blue-500">
                                  <i class="fa-solid fa-money-bill-wave text-gray-500 absolute left-3 top-1/2 -translate-y-1/2 text-xs"></i>
                                  
        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
            <span class="text-gray-400 text-xs">SAR</span>
        </div>
    </div>
    @error('salary')
        <p class="text-[10px] text-red-500 mt-1 font-bold">{{ $message }}</p>
    @enderror
</div>

            {{-- كلمة المرور --}}
            <div>
                <label class="block text-gray-300 font-medium mb-1">كلمة المرور</label>
                <div class="relative">
                    <input type="password" name="password" required placeholder="••••••••"
                           class="w-full bg-gray-800 border border-gray-700 text-gray-200 rounded-lg px-10 py-2
                                  focus:ring-blue-500 focus:border-blue-500">
                    <i class="fa-solid fa-lock text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
                </div>
            </div>

            {{-- زر الحفظ --}}
            <div class="pt-4">
                <button
                    class="w-full bg-blue-600 text-white px-6 py-2.5 rounded-lg shadow hover:bg-blue-700 transition font-semibold">
                    <i class="fa-solid fa-check mr-2"></i>
                    حفظ المحاسب
                </button>
            </div>

        </form>

    </div>

</div>

@endsection
