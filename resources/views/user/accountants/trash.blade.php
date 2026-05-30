@extends('dashboard.app')
@section('title', 'سلة المحاسبين المحذوفين')

@section('content')

<div class="px-6 py-8 max-w-6xl mx-auto">

    {{-- الهيدر --}}
    <div class="flex items-center justify-between mb-10">

    @php
    // تحديد رابط الرجوع بناءً على المعطيات
    $backUrl = route('user.accountants.index'); // الافتراضي

    if(request('from') == 'store' && request('store_id')) {
        $backUrl = route('user.stores.accountants.index', request('store_id'));
    }
@endphp

<a href="{{ $backUrl }}" 
   class="bg-gray-800 text-gray-200 px-4 py-2 rounded-lg">
   <i class="fa-solid fa-chevron-right ml-2"></i>
   رجوع
</a>

        <div class="text-center flex-1">
            <h1 class="text-3xl font-bold text-white">سلة المحاسبين المحذوفين</h1>
            <p class="text-gray-400 mt-1 text-sm">إدارة المحاسبين المحذوفين واستعادة أو حذف بياناتهم نهائيًا</p>
        </div>

        <div class="w-24"></div>
    </div>


    {{-- حالة عدم وجود بيانات --}}
    @if($accountants->count() === 0)
        <div class="bg-[#101216] border border-gray-800 rounded-2xl p-10 text-center text-gray-400 shadow-xl">
            <i class="fa-solid fa-trash text-5xl mb-4 text-gray-500"></i>
            <p class="text-lg">لا يوجد محاسبين محذوفين.</p>
        </div>
    @endif


    {{-- جدول المحاسبين --}}
    @if($accountants->count() > 0)

    <div class="overflow-x-auto rounded-2xl shadow-xl border border-gray-800">

        <table class="w-full text-gray-300 border-collapse">
            <thead>
                <tr class="bg-gray-800 text-gray-400 text-sm uppercase tracking-wide">
                    <th class="p-4 text-right">#</th>
                    <th class="p-4 text-right">الاسم</th>
                    <th class="p-4 text-right">البريد</th>
                    <th class="p-4 text-right">المتجر</th>
                    <th class="p-4 text-right">تاريخ الحذف</th>
                    <th class="p-4 text-right">إجراءات</th>
                </tr>
            </thead>

            <tbody>

                @foreach($accountants as $acc)

                <tr class="border-b border-gray-800 hover:bg-gray-850 transition">

                    {{-- رقم الصف --}}
                    <td class="p-4">{{ $loop->iteration }}</td>

                    <td class="p-4 font-semibold text-white">
                        {{ $acc->name }}
                    </td>

                    <td class="p-4">
                        {{ $acc->email }}
                    </td>

                    <td class="p-4">
                        {{ $acc->store?->name ?? 'غير مرتبط' }}
                    </td>

                    <td class="p-4 text-gray-400 text-sm">
                        {{ $acc->deleted_at->format('Y-m-d H:i') }}
                    </td>

                    <td class="p-4">
                        <div class="flex items-center gap-5 text-xl">

                            {{-- استعادة --}}
                            <a href="{{ route('user.accountants.restore', $acc->id) }}"
                               class="text-green-400 hover:text-green-300">
                                <i class="fa-solid fa-rotate-left"></i>
                            </a>

                            {{-- حذف نهائي --}}
                            <form action="{{ route('user.accountants.forceDelete', $acc->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('هل أنت متأكد من الحذف النهائي؟ سيتم حذف الموظف وجميع بياناته.')"
                                  class="inline">
                                @csrf
                                @method('DELETE')

                                <button class="text-red-400 hover:text-red-300">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>

                        </div>
                    </td>

                </tr>

                @endforeach

            </tbody>

        </table>

    </div>

    @endif

</div>

@endsection
