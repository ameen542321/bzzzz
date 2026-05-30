@extends('dashboard.app')
@section('title', 'إدارة المحاسبين')

@section('content')

@php
    $user = auth()->user();
    $plan = $user->plan;

    $currentCount = $accountants->total();
    $allowed = $plan->allowed_accountants;
    $remaining = max(0, $allowed - $currentCount);

    $canAdd = $currentCount < $allowed;

    $returnTo = url()->current();
@endphp

{{-- ========================= --}}
{{--        HEADER             --}}
{{-- ========================= --}}
<div class="mb-10 flex items-center justify-between">

    {{-- زر الرجوع --}}
    <a href="{{ request('return_to', url()->previous()) }}"
       class="flex items-center gap-2 bg-gray-800 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition shadow">
        <i class="fa-solid fa-arrow-right text-lg"></i>
        <span>رجوع</span>
    </a>

    {{-- العنوان --}}
    <div class="text-center flex-1">
        <h1 class="text-3xl font-bold text-white">إدارة المحاسبين</h1>
        <p class="text-gray-400 mt-1 text-sm">التحكم بالمحاسبين المرتبطين بمتاجرك</p>
    </div>

    {{-- زر إضافة محاسب --}}
    @if($canAdd)
        <a href="{{ route('user.accountants.create', ['from' => 'all', 'return_to' => $returnTo]) }}"
           class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition shadow">
            <i class="fa-solid fa-user-plus text-lg"></i>
            <span>إضافة محاسب</span>
        </a>
    @else
        <button disabled
            class="flex items-center gap-2 bg-gray-700 text-gray-400 px-4 py-2 rounded-lg cursor-not-allowed">
            <i class="fa-solid fa-lock text-lg"></i>
            <span>تم بلوغ الحد ({{ $allowed }})</span>
        </button>
    @endif

</div>


{{-- ========================= --}}
{{--     PLAN INFO BOX         --}}
{{-- ========================= --}}
<div class="bg-gray-900 border border-gray-800 rounded-2xl p-8 mb-10 shadow-lg">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">

        <div>
            <p class="text-gray-400 text-sm">الحد المسموح</p>
            <p class="text-blue-400 text-3xl font-bold mt-1">{{ $allowed }}</p>
        </div>

        <div>
            <p class="text-gray-400 text-sm">المستخدم حاليًا</p>
            <p class="text-blue-400 text-3xl font-bold mt-1">{{ $currentCount }}</p>
        </div>

        <div>
            <p class="text-gray-400 text-sm">المتبقي</p>
            <p class="text-green-400 text-3xl font-bold mt-1">{{ $remaining }}</p>
        </div>

    </div>

</div>


{{-- ========================= --}}
{{--     EMPTY STATE           --}}
{{-- ========================= --}}
@if($accountants->count() === 0)
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-12 text-center text-gray-400 shadow-lg">
        <i class="fa-solid fa-user-slash text-6xl mb-4 text-gray-600"></i>
        <p class="text-xl">لا يوجد محاسبين حتى الآن.</p>
    </div>
@endif


{{-- ========================= --}}
{{--     TABLE OF ACCOUNTANTS  --}}
{{-- ========================= --}}
@if($accountants->count() > 0)
    <div class="overflow-x-auto rounded-2xl shadow-xl border border-gray-800">

        <table class="w-full text-gray-300 border-collapse">
            <thead>
                <tr class="bg-gray-800 text-gray-400 text-sm uppercase tracking-wide">
                    <th class="p-4 text-right">#</th>
                    <th class="p-4 text-right">الاسم</th>
                    <th class="p-4 text-right">الجوال</th>
                    <th class="p-4 text-right">المتجر</th>
                    <th class="p-4 text-right">الحالة</th>
                    <th class="p-4 text-right md:hidden">تفاصيل</th>
                    <th class="hidden md:table-cell p-4 text-right">إجراءات</th>
                </tr>
            </thead>

            <tbody x-data="{ expanded: null }">

                @foreach($accountants as $acc)

                {{-- الصف الرئيسي --}}
                <tr
                    @click="if (window.innerWidth < 768) { expanded === {{ $acc->id }} ? expanded = null : expanded = {{ $acc->id }} }"
                    class="cursor-pointer border-b border-gray-800 hover:bg-gray-850 transition"
                >

                    <td class="p-4">
                        {{ $accountants->firstItem() + $loop->index }}
                    </td>

                    <td class="p-4 font-semibold text-white">
                        {{ $acc->employee?->name ?? '—' }}
                    </td>

                    <td class="p-4">
                        {{ $acc->employee?->phone ?? '—' }}
                    </td>

                    <td class="p-4">
                        {{ $acc->employee?->store?->name ?? 'غير مرتبط' }}
                    </td>

                    <td class="p-4">
                        @if($acc->status === 'active')
                            <span class="px-3 py-1 bg-green-600/20 text-green-400 rounded-full text-sm">
                                نشط
                            </span>
                        @else
                            <span class="px-3 py-1 bg-red-600/20 text-red-400 rounded-full text-sm">
                                موقوف
                            </span>
                        @endif
                    </td>

                    {{-- زر + للموبايل --}}
                    <td class="p-4 text-right md:hidden">
                        <i class="fa-solid text-2xl"
                           :class="expanded === {{ $acc->id }} ? 'fa-minus' : 'fa-plus'"></i>
                    </td>

                    {{-- الإجراءات للديسكتوب --}}
                    <td class="hidden md:table-cell p-4 text-right">
                        <div class="flex items-center gap-5 text-xl">

                            @if($acc->employee)
                                <a href="{{ route('user.employees.actions', [$acc->employee->id, 'return_to' => $returnTo]) }}"
                                   class="text-gray-300 hover:text-white">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            @endif

                            <a href="{{ route('user.accountants.edit', [$acc->id, 'return_to' => $returnTo]) }}"
                               class="text-blue-400 hover:text-blue-300">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>

                            @if($acc->status === 'active')
                                <a href="{{ route('user.accountants.suspend', [$acc->id, 'return_to' => $returnTo]) }}"
                                   class="text-yellow-400 hover:text-yellow-300">
                                    <i class="fa-solid fa-pause"></i>
                                </a>
                            @else
                                <a href="{{ route('user.accountants.activate', [$acc->id, 'return_to' => $returnTo]) }}"
                                   class="text-green-400 hover:text-green-300">
                                    <i class="fa-solid fa-play"></i>
                                </a>
                            @endif

                            {{-- @if($acc->employee)
                                <button onclick="document.getElementById('debtModal-{{ $acc->employee->id }}').classList.remove('hidden')"
                                        class="text-purple-400 hover:text-purple-300">
                                    <i class="fa-solid fa-money-bill"></i>
                                </button>
                            @endif --}}

                            <form action="{{ route('user.accountants.delete', $acc->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('هل أنت متأكد من حذف هذا المحاسب؟')"
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

                {{-- الصف الفرعي (موبايل) --}}
                <tr
                    x-show="expanded === {{ $acc->id }}"
                    x-cloak x-transition
                    class="bg-gray-850 border-b border-gray-800 md:hidden"
                >
                    <td colspan="7" class="p-6 text-gray-300">

                        <div class="grid grid-cols-1 gap-6">

                            <div>
                                <p class="text-gray-400 text-sm">البريد الإلكتروني</p>
                                <p class="font-semibold">{{ $acc->email }}</p>
                            </div>

                        </div>

                        <div class="mt-6 flex flex-wrap items-center gap-5">

                            @if($acc->employee)
                                <a href="{{ route('user.employees.actions', [$acc->employee->id, 'return_to' => $returnTo]) }}"
                                   class="p-3 rounded-full bg-gray-700 hover:bg-gray-600 transition text-white text-xl">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            @endif

                            <a href="{{ route('user.accountants.edit', [$acc->id, 'return_to' => $returnTo]) }}"
                               class="text-blue-400 hover:text-blue-300 text-2xl">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>

                            @if($acc->status === 'active')
                                <a href="{{ route('user.accountants.suspend', [$acc->id, 'return_to' => $returnTo]) }}"
                                   class="text-yellow-400 hover:text-yellow-300 text-2xl">
                                    <i class="fa-solid fa-pause"></i>
                                </a>
                            @else
                                <a href="{{ route('user.accountants.activate', [$acc->id, 'return_to' => $returnTo]) }}"
                                   class="text-green-400 hover:text-green-300 text-2xl">
                                    <i class="fa-solid fa-play"></i>
                                </a>
                            @endif

                            @if($acc->employee)
                                <button onclick="document.getElementById('debtModal-{{ $acc->employee->id }}').classList.remove('hidden')"
                                        class="p-3 rounded-full bg-purple-600 hover:bg-purple-700 transition text-white text-xl">
                                    <i class="fa-solid fa-money-bill"></i>
                                </button>
                            @endif

                            <form action="{{ route('user.accountants.delete', $acc->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('هل أنت متأكد من حذف هذا المحاسب؟')"
                                  class="inline">
                                @csrf
                                @method('DELETE')

                                <button class="text-red-400 hover:text-red-300 text-2xl">
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

    <div class="mt-8">
        {{ $accountants->links('pagination::tailwind') }}
    </div>

@endif


{{-- ========================= --}}
{{--     TRASH BUTTON          --}}
{{-- ========================= --}}
@if(($trashedCount ?? 0) > 0)
    <div class="mt-12 flex justify-center">
        <a href="{{ route('user.accountants.trash', ['from' => 'main']) }}"
           class="flex items-center gap-3 bg-gray-900 hover:bg-gray-800 border border-gray-800
                  text-gray-300 px-6 py-3 rounded-xl transition shadow-lg">

            <i class="fa-solid fa-trash text-red-400 text-lg"></i>

            <span class="text-sm">
                عرض سلة المحذوفات
                <span class="text-red-400 font-bold">({{ $trashedCount }})</span>
            </span>
        </a>
    </div>
@endif

@endsection
