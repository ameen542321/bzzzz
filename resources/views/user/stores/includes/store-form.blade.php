@php
    $isEdit = isset($store) && $store->exists;
@endphp
<div class="max-w-7xl mx-auto px-4 py-8">

    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
        <div class="flex items-center gap-5">
            <div class="w-14 h-14 bg-gradient-to-tr {{ $isEdit ? 'from-emerald-600 to-teal-700' : 'from-blue-600 to-indigo-700' }} rounded-2xl flex items-center justify-center shadow-2xl shadow-blue-900/40 transform -rotate-3">
                <i class="fa-solid {{ $isEdit ? 'fa-pen-to-square' : 'fa-store' }} text-white text-2xl"></i>
            </div>
            <div>
                <h1 class="text-3xl font-extrabold text-white tracking-tight">
                    {{ $isEdit ? 'تعديل المتجر: ' . $store->name : 'إضافة متجر جديد' }}
                </h1>
                <p class="text-slate-400 text-sm mt-1 flex items-center gap-2">
                    <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                    {{ $isEdit ? 'تحديث المعلومات المسجلة في النظام' : 'إعداد البيانات وفقاً لهيكلية النظام' }}
                </p>
            </div>
        </div>

        <button onclick="window.history.back()"
                class="inline-flex items-center gap-3 bg-slate-800/50 hover:bg-slate-700 text-slate-300 px-6 py-3 rounded-2xl border border-slate-700 transition-all group font-semibold backdrop-blur-sm">
            <i class="fa-solid fa-chevron-right transition-transform group-hover:translate-x-1"></i>
            <span>رجوع</span>
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">

        <div class="lg:col-span-8">
            <form action="{{ $isEdit ? route('user.stores.update', $store->id) : route('user.stores.store') }}"
                  method="POST" enctype="multipart/form-data" class="space-y-8">

                @csrf
                @if($isEdit) @method('PUT') @endif

                {{-- بطاقة الهوية التجارية --}}
                <div class="bg-slate-900/40 border border-slate-800 rounded-3xl shadow-2xl overflow-hidden backdrop-blur-md">
                    <div class="p-8 border-b border-slate-800 bg-gradient-to-r from-blue-600/5 to-transparent">
                        <h2 class="text-white text-xl font-bold flex items-center gap-3">
                            <i class="fa-solid fa-id-card text-blue-500"></i>
                            الهوية التجارية
                        </h2>
                    </div>

                    <div class="p-8 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label class="text-slate-300 text-sm font-semibold mr-1">اسم المتجر</label>
                                <input type="text" name="name" value="{{ old('name', $store->name ?? '') }}"
                                       class="w-full bg-slate-950/50 border border-slate-700 text-white rounded-2xl px-5 py-3.5 focus:border-blue-500 transition-all outline-none" required>
                            </div>

                            <div class="space-y-2">
                                <label class="text-slate-300 text-sm font-semibold mr-1">شعار المتجر</label>
                                <input type="file" name="logo" class="w-full bg-slate-950/50 border border-slate-700 text-slate-400 rounded-2xl px-5 py-2.5 focus:border-blue-500 transition-all outline-none">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-slate-300 text-sm font-semibold mr-1">الوصف</label>
                            <textarea name="description" rows="3" class="w-full bg-slate-950/50 border border-slate-700 text-white rounded-2xl px-5 py-3 focus:border-blue-500 transition-all outline-none">{{ old('description', $store->description ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- بطاقة البيانات القانونية والبنكية (تم إصلاح الجريد هنا) --}}
                <div class="bg-slate-900/40 border border-slate-800 rounded-3xl shadow-2xl overflow-hidden backdrop-blur-md">
                    <div class="p-8 border-b border-slate-800 bg-gradient-to-r from-emerald-600/5 to-transparent">
                        <h2 class="text-white text-xl font-bold flex items-center gap-3">
                            <i class="fa-solid fa-file-contract text-emerald-500"></i>
                            البيانات القانونية والبنكية
                        </h2>
                    </div>

                    <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-slate-300 text-sm font-semibold mr-1">السجل التجاري</label>
                            <input type="text" name="commercial_registration" value="{{ old('commercial_registration', $store->commercial_registration ?? '') }}" class="w-full bg-slate-950/50 border border-slate-700 text-white rounded-2xl px-5 py-3.5 focus:border-emerald-500 outline-none">
                        </div>

                        <div class="space-y-2">
                            <label class="text-slate-300 text-sm font-semibold mr-1">الرقم الضريبي</label>
                            <input type="text" name="tax_number" value="{{ old('tax_number', $store->tax_number ?? '') }}" class="w-full bg-slate-950/50 border border-slate-700 text-white rounded-2xl px-5 py-3.5 focus:border-emerald-500 outline-none">
                        </div>

                        <div class="space-y-2">
                            <label class="text-slate-300 text-sm font-semibold mr-1">رقم الهاتف</label>
                            <input type="text" name="phone" value="{{ old('phone', $store->phone ?? '') }}" class="w-full bg-slate-950/50 border border-slate-700 text-white rounded-2xl px-5 py-3.5 focus:border-emerald-500 outline-none text-left" dir="ltr">
                        </div>

                        <div class="space-y-2">
                            <label class="text-slate-300 text-sm font-semibold mr-1">العنوان</label>
                            <input type="text" name="address" value="{{ old('address', $store->address ?? '') }}" class="w-full bg-slate-950/50 border border-slate-700 text-white rounded-2xl px-5 py-3.5 focus:border-emerald-500 outline-none">
                        </div>

                        <div class="md:col-span-2 space-y-2">
                            <label class="text-slate-300 text-sm font-semibold mr-1">الحسابات البنكية (IBAN)</label>
                            <textarea name="bank_accounts" rows="3" class="w-full bg-slate-950/50 border border-slate-700 text-white rounded-2xl px-5 py-3 focus:border-emerald-500 outline-none italic">{{ old('bank_accounts', $store->bank_accounts ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- زر الإرسال --}}
                <button type="submit" class="w-full h-16 bg-gradient-to-r {{ $isEdit ? 'from-emerald-600 to-teal-700' : 'from-blue-600 to-indigo-700' }} text-white rounded-2xl font-black text-lg shadow-2xl transition-all hover:scale-[1.01] active:scale-95 flex items-center justify-center gap-3">
                    <i class="fa-solid {{ $isEdit ? 'fa-rotate' : 'fa-circle-check' }}"></i>
                    {{ $isEdit ? 'تحديث بيانات المتجر' : 'تأكيد وإنشاء المتجر' }}
                </button>
            </form>
        </div>

        {{-- الجانب الأيمن (نصائح) --}}
        <div class="lg:col-span-4 space-y-6">
            <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-8 backdrop-blur-md sticky top-10">
                <h3 class="text-white font-bold text-lg mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-blue-500"></i> ملاحظات الإعداد
                </h3>
                <ul class="text-slate-400 text-sm space-y-4">
                    <li class="flex gap-3">
                        <i class="fa-solid fa-check text-emerald-500 mt-1"></i>
                        تأكد من مطابقة اسم المتجر للسجل التجاري الرسمي.
                    </li>
                    <li class="flex gap-3">
                        <i class="fa-solid fa-check text-emerald-500 mt-1"></i>
                        يفضل أن يكون الشعار بصيغة PNG وبخلفية شفافة.
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
