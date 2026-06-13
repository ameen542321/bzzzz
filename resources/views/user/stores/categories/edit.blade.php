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
                <input type="text" name="name" id="category_name" value="{{ old('name', $category->name) }}"
                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-blue-500 transition outline-none">

                @unless($is_main_category)
                    @php
                        $currentPreset = old('category_name_preset', $category->name === 'تضليل' ? 'tint' : ($category->name === 'تنجيد وتلابيس' ? 'upholstery' : ''));
                    @endphp
                    <input type="hidden" name="category_name_preset" id="category_name_preset" value="{{ $currentPreset }}">
                    <div class="mt-4 p-4 bg-blue-950/30 border border-blue-500/30 rounded-xl">
                        <p class="text-sm font-bold text-blue-300 mb-2">أسماء أقسام معتمدة للنظام</p>
                        <p class="text-xs text-gray-400 mb-3">استخدم أحد الزرين لتثبيت اسم القسم دون أخطاء إملائية. ويمكنك كتابة أي اسم آخر يدويًا للأقسام الأخرى.</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <button type="button" data-category-preset="tint" data-category-name="تضليل"
                                    class="category-preset-btn border border-sky-500/50 bg-sky-500/10 hover:bg-sky-500/20 text-sky-200 rounded-lg px-4 py-3 text-sm font-bold transition">
                                <i class="fa-solid fa-sun ml-1"></i> تضليل
                            </button>
                            <button type="button" data-category-preset="upholstery" data-category-name="تنجيد وتلابيس"
                                    class="category-preset-btn border border-amber-500/50 bg-amber-500/10 hover:bg-amber-500/20 text-amber-200 rounded-lg px-4 py-3 text-sm font-bold transition">
                                <i class="fa-solid fa-couch ml-1"></i> تنجيد وتلابيس
                            </button>
                        </div>
                        <p id="category_preset_notice" class="hidden mt-3 text-xs text-emerald-300"></p>
                        @if($category->products()->exists())
                            <p class="mt-3 text-xs text-amber-300"><i class="fa-solid fa-triangle-exclamation ml-1"></i> تغيير اسم هذا القسم يؤثر على تصنيف المنتجات المرتبطة به في شاشات البيع المتخصصة.</p>
                        @endif
                    </div>
                @endunless
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


@unless($is_main_category)
<script>
    (() => {
        const nameInput = document.getElementById('category_name');
        const presetInput = document.getElementById('category_name_preset');
        const notice = document.getElementById('category_preset_notice');
        const buttons = document.querySelectorAll('[data-category-preset]');
        let applyingPreset = false;

        function refreshPresetState() {
            buttons.forEach((button) => {
                const selected = presetInput.value === button.dataset.categoryPreset;
                button.classList.toggle('ring-2', selected);
                button.classList.toggle('ring-emerald-400', selected);
            });
            if (presetInput.value) {
                notice.textContent = `سيُحفظ اسم القسم بالصيغة المعتمدة: ${nameInput.value}`;
                notice.classList.remove('hidden');
            } else {
                notice.classList.add('hidden');
            }
        }

        buttons.forEach((button) => button.addEventListener('click', () => {
            applyingPreset = true;
            nameInput.value = button.dataset.categoryName;
            presetInput.value = button.dataset.categoryPreset;
            applyingPreset = false;
            refreshPresetState();
            nameInput.focus();
        }));

        nameInput.addEventListener('input', () => {
            if (!applyingPreset) {
                presetInput.value = '';
                refreshPresetState();
            }
        });

        refreshPresetState();
    })();
</script>
@endunless
@endsection
