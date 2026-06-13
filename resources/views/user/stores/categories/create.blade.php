@extends('dashboard.app')

@section('title', ($is_main_category ? 'إضافة نشاط جديد – متجر ' : 'إضافة قسم جديد – متجر ') . $store->name)

@section('content')

<div class="max-w-3xl mx-auto py-10">

   <div class="flex items-center justify-between mb-10">

    {{-- زر الرجوع --}}
    <a href="{{ route('user.stores.categories.index', $store->id) }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-800 border border-gray-700 text-gray-300 hover:bg-gray-700 hover:text-white transition shadow-sm">
        <i class="fa-solid fa-arrow-right text-sm"></i>
        <span class="text-sm font-medium">رجوع</span>
    </a>

    {{-- عنوان الصفحة --}}
    <h1 class="text-2xl font-bold text-white">
        {{ $is_main_category ? 'إضافة نشاط جديد' : 'إضافة قسم جديد' }}
    </h1>

    <div class="w-20"></div>
</div>


    <div class="bg-gray-900 border border-gray-800 p-8 rounded-xl shadow-lg">

        <form action="{{ route('user.stores.categories.store', $store->id) }}" method="POST">
            @csrf

            {{-- نوع القسم (نشاط / قسم عادي) --}}
            <input type="hidden" name="is_main_category" value="{{ $is_main_category }}">

            {{-- الاسم --}}
            <div class="mb-6">
                <label class="block text-gray-300 mb-2 font-medium">
                    {{ $is_main_category ? 'اسم النشاط' : 'اسم القسم' }}
                </label>

                <input type="text" name="name" id="category_name"
                       value="{{ old('name') }}"
                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-blue-500 transition">

                @unless($is_main_category)
                    <input type="hidden" name="category_name_preset" id="category_name_preset" value="{{ old('category_name_preset') }}">
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
                    </div>
                @endunless

                @error('name')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- الوصف --}}
            <div class="mb-6">
                <label class="block text-gray-300 mb-2 font-medium">الوصف</label>
                <textarea name="description" rows="4"
                          class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-blue-500 transition">{{ old('description') }}</textarea>
            </div>

            {{-- الحالة --}}
            <div class="mb-6">
                <label class="block text-gray-300 mb-2 font-medium">الحالة</label>
                <select name="status"
                        class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-blue-500 transition">
                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>مفعل</option>
                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>غير مفعل</option>
                </select>
            </div>

            {{-- الأزرار --}}
            <div class="flex items-center justify-between mt-10">

                {{-- زر الإضافة --}}
                <button type="submit"
                        class="flex items-center bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition shadow-md">
                    <i class="fa-solid fa-plus ml-2"></i>
                    {{ $is_main_category ? 'إضافة النشاط' : 'إضافة القسم' }}
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
