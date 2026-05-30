<div class="bg-[#1b1d21] border border-[#2a2d31] rounded-xl p-5 hover:border-[#3a3d41] transition-all duration-200">

    {{-- اسم المتجر والحالة --}}
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-white">
            {{ $store->name }}
        </h3>
        @include('user.stores.includes.status-badge', ['status' => $store->status])
    </div>

    {{-- الوصف --}}
    <p class="text-sm text-gray-400 line-clamp-2 mb-4 h-10">
        {{ $store->description ?? 'لا يوجد وصف لهذا المتجر' }}
    </p>

    {{-- معلومات المتجر (كلها بنفس الستايل) --}}
    <div class="space-y-2 mb-6">
       {{-- السجل التجاري --}}
<div class="flex items-center text-xs text-gray-400 gap-2">
    <i class="fa-solid fa-file-invoice w-4 text-blue-400/80"></i>
    <span class="text-gray-500">السجل:</span>
    {{-- تم تصحيح الاسم هنا من register إلى registration --}}
    <span class="text-gray-300">{{ $store->commercial_registration?? '—' }}</span>
</div>
        {{-- الرقم الضريبي --}}
        <div class="flex items-center text-xs text-gray-400 gap-2">
            <i class="fa-solid fa-receipt w-4 text-emerald-400/80"></i>
            <span class="text-gray-500">الضريبة:</span>
            <span class="text-gray-300">{{ $store->tax_number ?? '—' }}</span>
        </div>

        {{-- رقم الهاتف --}}
        <div class="flex items-center text-xs text-gray-400 gap-2">
            <i class="fa-solid fa-phone w-4 text-gray-500"></i>
            <span class="text-gray-300">{{ $store->phone ?? '—' }}</span>
        </div>

        {{-- العنوان --}}
        <div class="flex items-center text-xs text-gray-400 gap-2">
            <i class="fa-solid fa-location-dot w-4 text-gray-500"></i>
            <span class="text-gray-300 line-clamp-1">{{ $store->address ?? '—' }}</span>
        </div>
    </div>

    {{-- الأزرار --}}
    <div class="flex items-center justify-between mt-auto pt-4 border-t border-[#2a2d31]">
        @include('user.stores.includes.actions', ['store' => $store])
    </div>

</div>