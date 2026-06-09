@extends('dashboard.app')

@section('title', 'تعديل المنتج – ' . $product->name)

@section('content')

<div class="max-w-4xl mx-auto py-10">

    {{-- عرض الأخطاء --}}
    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-900/50 border border-red-500 text-red-200 rounded-lg text-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- الهيدر --}}
    <div class="flex items-center justify-between mb-8">
        <a href="{{ route('user.stores.products.index', $store->id) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-800 border border-gray-700 text-gray-300 hover:bg-gray-700 hover:text-white transition shadow-sm">
            <i class="fa-solid fa-arrow-right text-sm"></i>
            <span class="text-sm font-medium">رجوع إلى المنتجات</span>
        </a>

        <h1 class="text-2xl font-bold text-white">
            تعديل المنتج – {{ $product->name }}
        </h1>

        <div class="w-32"></div>
    </div>

    <div class="bg-gray-900 border border-gray-800 p-8 rounded-xl">

        <form action="{{ route('user.stores.products.update', [$store->id, $product->id]) }}"
              method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            @php
                $mainCategories = $categories->where('is_main_category', 1);
                $normalCategories = $categories->where('is_main_category', 0);
            @endphp

            {{-- القسم --}}
            <div class="mb-6">
                <label class="block text-gray-300 mb-2">القسم</label>
                <select name="category_id" id="category_id" onchange="updateFractionalGuidance()" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2">
                    @if($mainCategories->isNotEmpty())
                        <optgroup label="الأنشطة">
                            @foreach($mainCategories as $category)
                                <option value="{{ $category->id }}" data-category-name="{{ $category->name }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </optgroup>
                    @endif
                    @if($normalCategories->isNotEmpty())
                        <optgroup label="الأقسام">
                            @foreach($normalCategories as $category)
                                <option value="{{ $category->id }}" data-category-name="{{ $category->name }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </optgroup>
                    @endif
                </select>
            </div>

            {{-- نوع المنتج --}}
            <div class="mb-6">
                <label class="block text-gray-300 mb-2 font-bold text-blue-400">نوع المنتج</label>
                <select name="product_type" id="product_type" onchange="toggleFractionSection()"
                        class="w-full bg-gray-800 border border-blue-900 text-white rounded-lg px-4 py-2">
                    <option value="standard" @selected(old('product_type', $product->product_type) == 'standard')>منتج عادي (بالحبة)</option>
                    <option value="fractional" @selected(old('product_type', $product->product_type) == 'fractional')>منتج قابل للتجزئة (رول/قص)</option>
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
                        <p class="text-gray-300">سعر كل عمل يوضع في خيار التجزئة. ونسبة الهالك تُطبق على <strong class="text-white">كل عملية أو خيار بيع</strong>، فتزيد الأمتار المخصومة وتكلفة المادة المستهلكة، ولا تُحسب مرة واحدة على الرول الكامل.</p>
                    </div>
                </div>

                <div class="mt-4 p-3 bg-emerald-950/30 border border-emerald-500/30 rounded-lg text-xs text-emerald-100 leading-6">
                    <i class="fa-solid fa-calculator text-emerald-300 ml-1"></i>
                    مثال: إذا كان استهلاك «أمامي» <strong>1.5 متر</strong> والهالك <strong>10%</strong>، يخصم النظام <strong>1.65 متر</strong> من المخزون، وتُحسب تكلفة البيع على 1.65 متر. وعند بيع «خلفي» لاحقًا تُطبق النسبة عليه أيضًا.
                </div>
            </div>

            {{-- نظام الأطقم (يظهر فقط للمنتج العادي) --}}
<div id="splittable_options_div" class="mb-6 p-4 bg-gray-800 border border-gray-700 rounded-lg">
    <div class="flex items-center justify-between">
        <label for="is_splittable" class="text-gray-300 font-medium">تفعيل نظام البيع كطقم / حبة</label>
        
        <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" name="is_splittable" id="is_splittable" value="1" 
                   @checked(old('is_splittable', $product->is_splittable)) 
                   onchange="toggleSplittableFields()"
                   class="sr-only peer">
            <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
        </label>
    </div>
    
    <div id="splittable_fields" class="grid grid-cols-2 gap-4 mt-4" style="display: none;">
        <div class="col-span-2">
            <label class="block text-gray-400 mb-2 text-sm">الوضع الافتراضي في البيع السريع</label>
            <select name="quick_sale_default_unit" class="w-full bg-gray-900 border border-gray-700 text-white rounded px-3 py-2">
                <option value="unit" @selected(old('quick_sale_default_unit', $product->quick_sale_default_unit ?? 'unit') === 'unit')>طقم (افتراضي)</option>
                <option value="piece" @selected(old('quick_sale_default_unit', $product->quick_sale_default_unit ?? 'unit') === 'piece')>حبة</option>
            </select>
        </div>
        <div>
            <label class="block text-gray-400 mb-2 text-sm">عدد الحبات في الطقم</label>
            <input type="number" name="items_per_unit" value="{{ old('items_per_unit', $product->items_per_unit) }}" class="w-full bg-gray-900 border border-gray-700 text-white rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-gray-400 mb-2 text-sm">سعر الحبة المنفردة</label>
            <input type="number" step="0.01" name="piece_price" value="{{ old('piece_price', $product->piece_price) }}" class="w-full bg-gray-900 border border-gray-700 text-white rounded px-3 py-2">
        </div>
    </div>
</div>
            {{-- طول الرول (يظهر فقط للمجزأ) --}}
            <div id="roll_length_div" class="mb-6" style="display: none;">
                <label class="block text-blue-400 mb-2 font-bold">طول الرول الكامل (بالأمتار)</label>
                <input type="number" step="0.01" name="roll_length" value="{{ old('roll_length', $product->roll_length) }}"
                       class="w-full bg-gray-800 border border-blue-900 text-white rounded-lg px-4 py-2">
            </div>

            {{-- الاسم --}}
            <div class="mb-6">
                <label class="block text-gray-300 mb-2">اسم المنتج</label>
                <input type="text" name="name" id="product_name" value="{{ old('name', $product->name) }}"
                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2">
            </div>

            {{-- سعر البيع --}}
            <div class="mb-6">
                <label class="block text-gray-300 mb-2">سعر البيع</label>
                <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $product->price) }}"
                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2">
            </div>

            {{-- سعر التكلفة --}}
            <div class="mb-6">
                <label class="block text-gray-300 mb-2">سعر التكلفة</label>
                <input type="number" step="0.01" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}"
                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2">
            </div>

            {{-- الحد الأدنى للمخزون --}}
            <div class="mb-6">
                <label class="block text-gray-300 mb-2">الحد الأدنى للمخزون</label>
                <input type="number" step="0.01" name="min_stock" value="{{ old('min_stock', $product->min_stock) }}"
                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2">
            </div>

            {{-- نسبة الهالك --}}
            <div class="mb-6" id="waste_percentage_div" style="display: none;">
                <label class="block text-blue-400 mb-2">نسبة الهالك %</label>
                <input type="number" step="0.01" name="waste_percentage" value="{{ old('waste_percentage', $product->waste_percentage) }}"
                       class="w-full bg-gray-800 border border-blue-900 text-white rounded-lg px-4 py-2">
            </div>

            {{-- خيارات التجزئة --}}
            <div id="fractions_section" style="display: none;" class="mb-6 p-4 bg-gray-800 rounded-lg">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-blue-400 font-bold">خيارات التجزئة الحالية</h3>
                    <button type="button" onclick="addFractionRow()" class="text-xs bg-blue-600 text-white px-3 py-1 rounded">+ إضافة خيار جديد</button>
                </div>
                {{-- قيمة deduction_value هنا محفوظة كاستهلاك فعلي بالمتر لكل خيار رول، وليست نسبة من طول الرول. --}}
                <p class="mb-4 text-xs text-gray-400">لكل سطر أدخل: <strong class="text-gray-200">اسم العمل</strong>، ثم <strong class="text-gray-200">استهلاك عمل واحد بالمتر</strong>، ثم <strong class="text-gray-200">سعر بيع عمل واحد</strong>.</p>
                <div id="fractions_container">
                    @php
                        $data = old('fractions') ?? $product->fractions()->get()->toArray();
                    @endphp
                    @foreach($data as $index => $item)
                        @php $item = (array) $item; @endphp
                        <div class="flex gap-2 mb-2" id="row_{{ $index }}">
                            <input type="text" name="fractions[{{ $index }}][option_label]" value="{{ $item['option_label'] ?? '' }}" placeholder="الاسم" class="flex-1 bg-gray-900 border border-gray-700 text-white rounded px-2 py-1 text-sm">
                            <input type="number" step="0.01" name="fractions[{{ $index }}][deduction_value]" value="{{ $item['deduction_value'] ?? '' }}" placeholder="الاستهلاك بالمتر" class="w-24 bg-gray-900 border border-gray-700 text-white rounded px-2 py-1 text-sm">
                            <input type="number" step="0.01" name="fractions[{{ $index }}][price]" value="{{ $item['price'] ?? '' }}" placeholder="السعر" class="w-24 bg-gray-900 border border-gray-700 text-white rounded px-2 py-1 text-sm">
                            <button type="button" onclick="this.parentElement.remove()" class="text-red-500 px-2 font-bold">×</button>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- الكمية الحالية --}}
            <div class="mb-6">
                <label class="block text-gray-300 mb-2">الكمية الحالية</label>
                <div class="w-full bg-gray-800 border border-gray-700 text-gray-400 rounded-lg px-4 py-2">
                    {{ number_format($product->quantity, 2) }}
                </div>
                <a href="{{ route('user.stores.products.stock', [$store->id, $product->id]) }}" class="inline-block mt-2 text-blue-400 hover:text-blue-300 text-sm">إدارة المخزون</a>
            </div>

            {{-- الوصف --}}
            <div class="mb-6">
                <label class="block text-gray-300 mb-2">الوصف</label>
                <textarea name="description" rows="4" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2">{{ old('description', $product->description) }}</textarea>
            </div>

            {{-- الحالة --}}
            <div class="mb-6">
                <label class="block text-gray-300 mb-2">الحالة</label>
                <select name="status" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2">
                    <option value="active" @selected($product->status == 'active')>مفعل</option>
                    <option value="inactive" @selected($product->status == 'inactive')>غير مفعل</option>
                </select>
            </div>

            {{-- الصورة --}}
            <div class="mb-6">
                <label class="block text-gray-300 mb-2">صورة المنتج</label>
                <input type="file" name="image" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2">
                <img src="{{ $product->image ? asset('storage/' . $product->image) : asset('images/default-product.png') }}" class="w-32 h-32 object-cover rounded-lg mt-3 border border-gray-700">
            </div>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                <i class="fa-solid fa-save ml-1"></i> حفظ التعديلات
            </button>
        </form>
    </div>
</div>

<script>
    let fractionIndex = {{ count(old('fractions') ?? $product->fractions) }};

    function toggleFractionSection() {
        const type = document.getElementById('product_type').value;
        document.getElementById('fractional_product_guidance').style.display = type === 'fractional' ? 'block' : 'none';
        updateFractionalGuidance();
        document.getElementById('fractions_section').style.display = type === 'fractional' ? 'block' : 'none';
        document.getElementById('waste_percentage_div').style.display = type === 'fractional' ? 'block' : 'none';
        document.getElementById('roll_length_div').style.display = type === 'fractional' ? 'block' : 'none';
        document.getElementById('splittable_options_div').style.display = type === 'standard' ? 'block' : 'none';
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
        const isChecked = document.getElementById('is_splittable').checked;
        document.getElementById('splittable_fields').style.display = isChecked ? 'grid' : 'none';
    }

    function addFractionRow() {
        // عند إضافة خيار جديد للرول، قيمة الاستهلاك تُدخل بالمتر حتى يخصم البيع نفس الرقم من المخزون.
        const container = document.getElementById('fractions_container');
        const div = document.createElement('div');
        div.className = "flex gap-2 mb-2";
        div.innerHTML = `
            <input type="text" name="fractions[${fractionIndex}][option_label]" placeholder="الاسم" required class="flex-1 bg-gray-900 border border-gray-700 text-white rounded px-2 py-1 text-sm">
            <input type="number" step="0.01" name="fractions[${fractionIndex}][deduction_value]" placeholder="الاستهلاك بالمتر" required class="w-24 bg-gray-900 border border-gray-700 text-white rounded px-2 py-1 text-sm">
            <input type="number" step="0.01" name="fractions[${fractionIndex}][price]" placeholder="السعر" required class="w-24 bg-gray-900 border border-gray-700 text-white rounded px-2 py-1 text-sm">
            <button type="button" onclick="this.parentElement.remove()" class="text-red-500 px-2">×</button>
        `;
        container.appendChild(div);
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
        toggleSplittableFields();
        disableNumberWheelInputs();
    };
</script>
@endsection