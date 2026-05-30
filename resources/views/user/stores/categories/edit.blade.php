@extends('dashboard.app')

@section('title', ($is_main_category ? 'تعديل النشاط – متجر ' : 'تعديل القسم – متجر ') . $store->name)

@section('content')

<div class="max-w-3xl mx-auto py-10 px-4" dir="rtl">

    {{-- الهيدر --}}
    <div class="flex items-center justify-between mb-10">
        <a href="{{ route('user.stores.categories.index', $store->id) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-800 border border-gray-700 text-gray-300 hover:bg-gray-700 hover:text-white transition shadow-sm">
            <i class="fa-solid fa-arrow-right text-sm"></i>
            <span class="text-sm font-medium">رجوع</span>
        </a>

        <h1 class="text-2xl font-bold text-white text-right">
            {{ $is_main_category ? 'تعديل النشاط' : 'تعديل القسم' }}
        </h1>
        <div class="w-20"></div>
    </div>

    {{-- النموذج --}}
    <div class="bg-gray-900 border border-gray-800 p-8 rounded-xl shadow-lg">
        <form action="{{ route('user.stores.categories.update', [$store->id, $category->id]) }}" method="POST">
            @csrf
            @method('PUT')

<input type="hidden" name="is_main_category" value="{{ $category->is_main_category ? 1 : 0 }}">
            {{-- الاسم --}}
            <div class="mb-6 text-right">
                <label class="block text-gray-300 mb-2 font-medium">
                    {{ $is_main_category ? 'اسم النشاط' : 'اسم القسم' }}
                </label>
                <input type="text" name="name" value="{{ old('name', $category->name) }}"
                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-blue-500 transition outline-none">
                @error('name') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- الوصف --}}
            <div class="mb-6 text-right">
                <label class="block text-gray-300 mb-2 font-medium">الوصف</label>
                <textarea name="description" rows="3"
                          class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2 focus:border-blue-500 transition outline-none">{{ old('description', $category->description) }}</textarea>
            </div>

            {{-- الحالة --}}
            <div class="mb-6 text-right">
                <label class="block text-gray-300 mb-2 font-medium">الحالة</label>
                <select name="status" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2 focus:border-blue-500 outline-none transition">
                    <option value="active" {{ old('status', $category->status) == 'active' ? 'selected' : '' }}>مفعل</option>
                    <option value="inactive" {{ old('status', $category->status) == 'inactive' ? 'selected' : '' }}>غير مفعل</option>
                </select>
            </div>

            <hr class="border-gray-800 my-8">

            {{-- ميزة النقل (الذكاء المضاف) --}}
            <div class="bg-blue-900/10 border border-blue-800/30 p-6 rounded-xl text-right">
                <h3 class="text-blue-400 font-bold mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-truck-fast"></i> نقل القسم لمتجر آخر (اختياري)
                </h3>

                <div class="mb-4">
                    <label class="block text-gray-400 text-sm mb-2 font-medium">اختر المتجر الجديد في حال رغبت بنقل القسم</label>
                    <select name="target_store_id" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2 focus:border-blue-500 outline-none transition">
                        <option value="">-- ابقائه في المتجر الحالي --</option>
                        @foreach(App\Models\Store::where('user_id', auth()->id())->where('id', '!=', $store->id)->get() as $otherStore)
                            <option value="{{ $otherStore->id }}">{{ $otherStore->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" name="move_products" id="move_products" value="1" checked class="w-4 h-4 rounded border-gray-700 bg-gray-800 text-blue-600">
                    <label for="move_products" class="text-gray-400 text-xs">نقل كافة المنتجات المرتبطة بهذا القسم للمتجر الجديد</label>
                </div>
            </div>

            {{-- الأزرار --}}
            <div class="flex items-center justify-between mt-10">
                <button type="submit" class="flex items-center bg-blue-600 hover:bg-blue-700 text-white px-8 py-2 rounded-lg transition shadow-md font-bold">
                    <i class="fa-solid fa-floppy-disk ml-2"></i> حفظ كافة التغييرات
                </button>
            </div>

        </form>
    </div>
</div>

@endsection
