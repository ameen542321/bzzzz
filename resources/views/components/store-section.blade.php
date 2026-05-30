@props(['title', 'back', 'add','addLabel' => '+ إضافة','returnTo' => null])

@php
    // إذا كان الرابط يحتوي على ? نستخدم &
    $separator = str_contains($add, '?') ? '&' : '?';
@endphp

<div class="flex items-center justify-between mb-6">

    {{-- يسار: زر الرجوع + العنوان --}}
    <div class="flex items-center gap-3">
        <a href="{{ $back }}"
           class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-md flex items-center gap-1">
            ← رجوع
        </a>

        <h1 class="text-xl font-semibold text-white">
            {{ $title }}
        </h1>
    </div>

    {{-- يمين: زر الإضافة --}}
    <div class="flex items-center gap-3">

        @if($add)
            <a href="{{ $add . $separator . 'return_to=' . urlencode($returnTo ?? url()->current()) }}"
               class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-md">
                 {{ $addLabel ?? '+ إضافة' }}
            </a>
        @endif

    </div>

</div>

<div class="bg-gray-800 rounded-xl p-4 shadow-lg overflow-x-auto">
    {{ $slot }}
</div>
