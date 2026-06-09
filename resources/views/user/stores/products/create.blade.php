@extends('dashboard.app')

@section('title', 'إضافة منتج – متجر ' . $store->name)

@section('content')

<div class="max-w-4xl mx-auto py-10">
    {{-- عرض الأخطاء إذا وجدت --}}
    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-900/50 border border-red-500 text-red-200 rounded-lg text-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex items-center justify-between mb-8">
        <a href="{{ route('user.stores.products.index', $store->id) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-800 border border-gray-700 text-gray-300 hover:bg-gray-700 transition shadow-sm">
            <i class="fa-solid fa-arrow-right text-sm"></i>
            <span class="text-sm font-medium">رجوع إلى المنتجات</span>
        </a>
        <h1 class="text-2xl font-bold text-white">إضافة منتج جديد</h1>
        <div class="w-32"></div>
    </div>

    <div class="bg-gray-900 border border-gray-800 p-8 rounded-xl shadow-2xl">
        <form action="{{ route('user.stores.products.store', $store->id) }}" method="POST" enctype="multipart/form-data" id="productForm">
            @csrf

            @php
                $mainCategories = $categories->where('is_main_category', 1);
                $normalCategories = $categories->where('is_main_category', 0);
            @endphp

            {{-- القسم --}}
            <div class="mb-6">
                <label class="block text-gray-300 mb-2">القسم</label>
                <select name="category_id" id="category_id" onchange="updateFractionalGuidance()" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2 outline-none focus:ring-2 focus:ring-blue-600">
                    @foreach($mainCategories as $category)
                        <option value="{{ $category->id }}" data-category-name="{{ $category->name }}" @selected(old('category_id', $selectedCategory) == $category->id)>{{ $category->name }} (نشاط)</option>
                    @endforeach
                    @foreach($normalCategories as $category)
                        <option value="{{ $category->id }}" data-category-name="{{ $category->name }}" @selected(old('category_id', $selectedCategory) == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- نوع المنتج --}}
            <div class="mb-6">
                <label class="block text-gray-300 mb-2">نوع المنتج</label>
                <select name="product_type" id="product_type" onchange="toggleFractionSection()"
                        class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2 font-bold text-blue-400 outline-none focus:ring-2 focus:ring-blue-600">
                    <option value="standard" @selected(old('product_type') == 'standard')>📦 منتج عادي (بالوحدة)</option>
                    <option value="fractional" @selected(old('product_type') == 'fractional')>✂️ منتج قابل للتجزئة (رول/قص)</option>
                </select>
            </div>


            {{-- إرشادات منتج الرول؛ تتغير حسب القسم المختار وتظهر فقط للرول/القص. --}}
            <div id="fractional_product_guidance" class="mb-6 p-5 bg-sky-950/40 border border-sky-500/40 rounded-xl" style="display: none;">
                <div class="flex items-start gap-3 mb-4">
                    <div class="shrink-0 w-10 h-10 rounded-lg bg-sky-500/15 text-sky-300 flex items-center justify-center">
                        <i class="fa-solid fa-circle-info"></i>
                    </div>
                    <div>
                        <h3 id="fractional_guidance_title" class="text-sky-300 font-bold">دليل إدخال منتج رول/قص</h3>
                        <p class="text-xs text-gray-400 mt-1">سجّل كل رول فعلي كمنتج مستقل؛ فالتكلفة والمخزون والمتبقي تُتابع لكل منتج بالمتر.</p>
                    </div>
                </div>

                <div id="tint_product_guidance" class="hidden mb-4 p-4 bg-indigo-950/40 border border-indigo-500/30 rounded-lg">
                    <p class="text-indigo-200 font-bold text-sm mb-2"><i class="fa-solid fa-sun ml-1"></i> منتج تابع لقسم تضليل</p>
                    <ul class="space-y-1 text-xs text-gray-300 list-disc list-inside">
                        <li>اسم المنتج بالترتيب: <strong class="text-white">النوع + الحجم + الدرجة</strong>.</li>
                        <li>أمثلة صحيحة: <strong class="text-white">كوري كبير 01</strong>، <strong class="text-white">أمريكي صغير 02</strong>، <strong class="text-white">مخلوط صغير 01</strong>.</li>
                        <li>خيارات التجزئة القياسية: <strong class="text-white">كامل، أمامي، خلفي، دريشة</strong>. لا تضف «مخصص»؛ مكانه موجود في شاشة البيع.</li>
                        <li>استهلاك «دريشة» وسعرها يُدخلان لدريشة واحدة، والنظام يضربهما في العدد عند البيع.</li>
                    </ul>
                </div>

                <div id="upholstery_product_guidance" class="hidden mb-4 p-4 bg-amber-950/30 border border-amber-500/30 rounded-lg">
                    <p class="text-amber-200 font-bold text-sm mb-2"><i class="fa-solid fa-couch ml-1"></i> منتج تابع لقسم تنجيد وتلابيس</p>
                    <p class="text-xs text-gray-300">اكتب اسمًا يميز الخامة واللون أو المقاس، مثل: <strong class="text-white">جلد أسود عرض 1.5 متر</strong>. سمِّ خيارات القص حسب الأعمال الفعلية التي تبيعها، وحدد استهلاك كل خيار بالمتر.</p>
                </div>

                <div id="general_roll_guidance" class="hidden mb-4 p-4 bg-gray-900/70 border border-gray-700 rounded-lg">
                    <p class="text-gray-200 font-bold text-sm mb-1">منتج رول في قسم آخر</p>
                    <p class="text-xs text-gray-400">استخدم اسمًا واضحًا يميز المنتج، وأنشئ خيارات قص بأسماء يفهمها العامل مع استهلاك وسعر كل خيار.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <div class="p-3 bg-gray-900/70 border border-gray-700 rounded-lg">
                        <span class="block text-sky-300 font-bold mb-1">طول الرول وسعر التكلفة</span>
                        <p class="text-gray-300">أدخل طول الرول الكامل بالمتر، وسعر تكلفة <strong class="text-white">الرول الكامل</strong> وليس سعر المتر.</p>
                    </div>
                    <div class="p-3 bg-gray-900/70 border border-gray-700 rounded-lg">
                        <span class="block text-sky-300 font-bold mb-1">عدد الرولات والمخزون</span>
                        <p class="text-gray-300">عند الإضافة: المخزون بالمتر = عدد الرولات × طول الرول. وعند التعديل غيّر الكمية من إدارة المخزون.</p>
                    </div>
                    <div class="p-3 bg-gray-900/70 border border-gray-700 rounded-lg">
                        <span class="block text-sky-300 font-bold mb-1">الاستهلاك بالمتر</span>
                        <p class="text-gray-300">هو ما يُخصم من المخزون عند بيع الخيار. مثال: 1.5 تعني خصم 1.5 متر، وليست 1.5 رول.</p>
                    </div>
                    <div class="p-3 bg-gray-900/70 border border-gray-700 rounded-lg">
                        <span class="block text-sky-300 font-bold mb-1">السعر والهالك</span>
                        <p class="text-gray-300">سعر كل عمل يوضع في خيار التجزئة. ونسبة الهالك تزيد الأمتار المخصومة لتغطية فاقد القص.</p>
                    </div>
                </div>
            </div>

            {{-- نظام الأطقم (للعادي فقط) --}}
            <div id="splittable_options_div" class="mb-6 p-5 bg-purple-900/10 border border-purple-500/30 rounded-xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-purple-500/20 rounded-lg text-purple-400"><i class="fa-solid fa-boxes-stacked"></i></div>
                        <div>
                            <h3 class="text-white font-bold text-sm">نظام البيع كطقم / حبة</h3>
                            <p class="text-[10px] text-gray-400">تفعيل خيار بيع أجزاء من هذا المنتج بشكل منفرد.</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_splittable" id="is_splittable" value="1" @checked(old('is_splittable')) onchange="toggleSplittableFields()" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-700 rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-purple-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                    </label>
                </div>
                <div id="splittable_fields" class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-5 border-t border-purple-500/20 pt-5" style="display: none;">
                    <div class="md:col-span-2">
                        <label class="block text-purple-400 mb-2 text-xs font-bold">الوضع الافتراضي في البيع السريع</label>
                        <select name="quick_sale_default_unit" class="w-full bg-gray-800 border border-purple-500/40 text-white rounded-lg px-4 py-2">
                            <option value="unit" @selected(old('quick_sale_default_unit', 'unit') === 'unit')>طقم (افتراضي)</option>
                            <option value="piece" @selected(old('quick_sale_default_unit') === 'piece')>حبة</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-purple-400 mb-2 text-xs font-bold">عدد الحبات في الطقم</label>
                        <input type="number" name="items_per_unit" id="items_per_unit" value="{{ old('items_per_unit', 4) }}" class="w-full bg-gray-800 border border-purple-500/40 text-white rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label class="block text-purple-400 mb-2 text-xs font-bold">سعر الحبة المنفردة</label>
                        <input type="number" step="0.01" name="piece_price" id="piece_price" value="{{ old('piece_price') }}" class="w-full bg-gray-800 border border-purple-500/40 text-white rounded-lg px-4 py-2">
                    </div>
                </div>
            </div>

            {{-- حقول الأمتار والرولات --}}
            <div id="roll_length_div" class="mb-6 p-4 bg-blue-900/20 border border-blue-500/30 rounded-lg" style="display: none;">
                <label class="block text-blue-400 mb-2 font-bold italic"><i class="fa-solid fa-ruler-combined ml-1"></i> طول الرول (بالأمتار)</label>
                <input type="number" step="0.01" name="roll_length" id="roll_length" value="{{ old('roll_length', 30) }}" class="w-full bg-gray-800 border border-blue-500/50 text-white rounded-lg px-4 py-2">
            </div>

            <div class="mb-6">
                <label class="block text-gray-300 mb-2">اسم المنتج</label>
                <input type="text" name="name" id="product_name" value="{{ old('name') }}" required class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-gray-300 mb-2">سعر البيع</label>
                    <input type="number" step="0.01" name="price" value="{{ old('price') }}" required class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-gray-300 mb-2">سعر التكلفة</label>
                    <input type="number" step="0.01" name="cost_price" value="{{ old('cost_price') }}" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div id="standard_quantity_div">
                    <label class="block text-gray-300 mb-2">الكمية الابتدائية (وحدات)</label>
                    <input type="number" step="0.01" name="quantity" id="quantity" value="{{ old('quantity') }}" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2">
                </div>
                <div id="fractional_quantity_div" style="display: none;">
                    <label class="block text-blue-400 mb-2 font-bold">الكمية (عدد الرولات)</label>
                    <input type="number" step="0.01" name="num_rolls" id="num_rolls" value="{{ old('num_rolls') }}" class="w-full bg-gray-800 border border-blue-500/50 text-white rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-gray-300 mb-2">حد المخزون (التنبيه)</label>
                    <input type="number" step="0.01" name="min_stock" value="{{ old('min_stock', 1) }}" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2">
                </div>
            </div>

            <div id="waste_percentage_div" class="mb-6" style="display: none;">
                <label class="block text-blue-400 mb-2 font-bold italic">نسبة الهالك %</label>
                <input type="number" step="0.01" name="waste_percentage" value="{{ old('waste_percentage', 0) }}" class="w-full bg-gray-800 border border-blue-900 text-white rounded-lg px-4 py-2">
            </div>

            {{-- خيارات التجزئة --}}
            <div id="fractions_section" style="display: none;" class="mb-8 p-6 bg-gray-800/50 border border-blue-900/30 rounded-xl">
                <div class="flex items-center justify-between mb-3 border-b border-gray-700 pb-2">
                    <h3 class="text-blue-400 font-bold flex items-center gap-2"><i class="fa-solid fa-scissors text-sm"></i> خيارات التجزئة</h3>
                    <button type="button" onclick="addFractionRow()" class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-1 rounded-lg">+ إضافة خيار</button>
                </div>
                <p class="mb-4 text-xs text-gray-400">لكل سطر أدخل: <strong class="text-gray-200">اسم العمل</strong>، ثم <strong class="text-gray-200">استهلاك عمل واحد بالمتر</strong>، ثم <strong class="text-gray-200">سعر بيع عمل واحد</strong>.</p>
                <div id="fractions_container"></div>
            </div>

            <div class="mb-6">
                <label class="block text-gray-300 mb-2">الوصف</label>
                <textarea name="description" rows="3" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="block text-gray-300 mb-2 text-sm">الحالة</label>
                    <select name="status" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2">
                        <option value="active" @selected(old('status') == 'active')>مفعل</option>
                        <option value="inactive" @selected(old('status') == 'inactive')>غير مفعل</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-300 mb-2 text-sm">صورة المنتج</label>
                    <input type="file" name="image" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2">
                </div>
            </div>

            {{-- خيار البقاء في الصفحة (تم استعادته) --}}
            <div class="mb-8 flex items-center gap-3 bg-gray-800/30 p-4 rounded-lg border border-gray-700">
                <input type="checkbox" name="stay_here" id="stay_here" value="1" @checked(old('stay_here')) class="w-5 h-5 rounded border-gray-700 bg-gray-800 text-blue-600 focus:ring-blue-500">
                <label for="stay_here" class="text-gray-300 text-sm font-medium cursor-pointer">البقاء في هذه الصفحة بعد الإضافة لإنشاء منتج آخر</label>
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-lg transition shadow-lg active:scale-95">
                <i class="fa-solid fa-save ml-2"></i> حفظ المنتج
            </button>
        </form>
    </div>
</div>

<script>
    let fractionIndex = 0;

    function toggleFractionSection() {
        const type = document.getElementById('product_type').value;
        document.getElementById('fractional_product_guidance').style.display = type === 'fractional' ? 'block' : 'none';
        updateFractionalGuidance();
        const splittableDiv = document.getElementById('splittable_options_div');
        const rollDiv = document.getElementById('roll_length_div');
        const fractionSec = document.getElementById('fractions_section');
        const wasteDiv = document.getElementById('waste_percentage_div');
        const stdQty = document.getElementById('standard_quantity_div');
        const fracQty = document.getElementById('fractional_quantity_div');

        if (type === 'fractional') {
            splittableDiv.style.display = 'none';
            rollDiv.style.display = 'block';
            fractionSec.style.display = 'block';
            wasteDiv.style.display = 'block';
            stdQty.style.display = 'none';
            fracQty.style.display = 'block';
            
            // إضافة سطر تلقائي إذا كان فارغاً
            if (document.getElementById('fractions_container').children.length === 0) addFractionRow();
        } else {
            splittableDiv.style.display = 'block';
            rollDiv.style.display = 'none';
            fractionSec.style.display = 'none';
            wasteDiv.style.display = 'none';
            stdQty.style.display = 'block';
            fracQty.style.display = 'none';
            toggleSplittableFields();
        }
    }

    function updateFractionalGuidance() {
        const productType = document.getElementById('product_type').value;
        const categorySelect = document.getElementById('category_id');
        const categoryName = categorySelect?.selectedOptions[0]?.dataset.categoryName?.trim() || '';
        const isFractional = productType === 'fractional';
        const isTint = categoryName === 'تضليل';
        const isUpholstery = categoryName === 'تنجيد وتلابيس';

        document.getElementById('tint_product_guidance').classList.toggle('hidden', !isFractional || !isTint);
        document.getElementById('upholstery_product_guidance').classList.toggle('hidden', !isFractional || !isUpholstery);
        document.getElementById('general_roll_guidance').classList.toggle('hidden', !isFractional || isTint || isUpholstery);

        const title = document.getElementById('fractional_guidance_title');
        if (title) {
            title.textContent = isTint
                ? 'دليل إدخال رول التضليل'
                : (isUpholstery ? 'دليل إدخال رول التنجيد والتلابيس' : 'دليل إدخال منتج رول/قص');
        }
    }

    function toggleSplittableFields() {
        const isSplittable = document.getElementById('is_splittable').checked;
        const fields = document.getElementById('splittable_fields');
        fields.style.display = isSplittable ? 'grid' : 'none';
    }

    function addFractionRow() {
        // في منتجات الرول، حقل deduction_value يعني عدد الأمتار المستهلكة للخيار.
        // مثال: زجاج أمامي = 1.5 يعني خصم 1.5 متر من مخزون الرول وليس 1.5 رول.
        const container = document.getElementById('fractions_container');
        const row = document.createElement('div');
        row.id = `row_${fractionIndex}`;
        row.className = "flex flex-col md:flex-row gap-3 mb-4 p-4 bg-gray-900/80 rounded-lg border border-gray-700 relative";
        row.innerHTML = `
            <div class="flex-1">
                <input type="text" name="fractions[${fractionIndex}][option_label]" placeholder="اسم الخيار" class="w-full bg-gray-800 border border-gray-700 text-white text-sm rounded px-3 py-2">
            </div>
            <div class="w-full md:w-32">
                <input type="number" step="0.01" name="fractions[${fractionIndex}][deduction_value]" placeholder="الاستهلاك بالمتر" class="w-full bg-gray-800 border border-gray-700 text-white text-sm rounded px-3 py-2">
            </div>
            <div class="w-full md:w-32">
                <input type="number" step="0.01" name="fractions[${fractionIndex}][price]" placeholder="السعر" class="w-full bg-gray-800 border border-gray-700 text-white text-sm rounded px-3 py-2">
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-red-500 px-2"><i class="fa-solid fa-trash"></i></button>
        `;
        container.appendChild(row);
        fractionIndex++;
    }

    function disableNumberWheelInputs() {
        document.querySelectorAll('input[type="number"]').forEach((input) => {
            input.addEventListener('wheel', function(event) {
                event.preventDefault();
            }, { passive: false });
        });
    }

    window.onload = function() {
        toggleFractionSection();
        disableNumberWheelInputs();
    };
</script>
@endsection