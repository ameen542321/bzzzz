@extends('dashboard.app')
@section('title', ' الموظفين المحذوفين ')

@section('content')

<div class="px-6 py-8">

    <!-- الهيدر -->
    <div class="flex items-center justify-between mb-10">
        <div>
            <h1 class="text-3xl font-bold text-gray-100">سلة الموظفين المحذوفين</h1>
            <p class="text-gray-400 text-sm mt-1">يمكنك استعادة الموظفين أو حذفهم نهائيًا</p>
        </div>

      @php
    // تحديد رابط العودة الافتراضي
    $backUrl = route('user.employees.index'); 

    // إذا كان المستخدم جاء من صفحة متجر محدد
    if(request('from') == 'store' && request('store_id')) {
        $backUrl = route('user.stores.employees.index', request('store_id'));
    }
@endphp

<div class="mb-6">
    <a href="{{ $backUrl }}" 
       class="inline-flex items-center gap-2 px-4 py-2 bg-gray-800 border border-gray-700 text-gray-300 rounded-xl hover:bg-gray-700 hover:text-white transition-all shadow-sm group">
        <i class="fa-solid fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
        <span class="font-bold text-sm">رجوع</span>
    </a>
</div>
    </div>

    <!-- بطاقة إحصائية -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 flex items-center gap-4 shadow hover:shadow-lg transition">
            <div class="bg-red-700/20 text-red-400 p-3 rounded-lg">
                <i class="fa-solid fa-trash text-2xl"></i>
            </div>
            <div>
                <p class="text-gray-400 text-sm">عدد المحذوفين</p>
                <p class="text-2xl font-bold text-gray-100">{{ $employees->total() }}</p>
            </div>
        </div>

    </div>

    <!-- الجدول -->
    <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden shadow-xl">

        <table class="w-full text-right">
            <thead class="bg-gray-800 border-b border-gray-700">
                <tr class="text-gray-400 text-sm">
                    <th class="p-4">الموظف</th>
                    <th class="p-4">المتجر</th>
                    <th class="p-4">تاريخ الحذف</th>
                    <th class="p-4 text-center">إجراءات</th>
                </tr>
            </thead>

            <tbody class="text-gray-300">

                @forelse ($employees as $employee)
                    <tr class="border-b border-gray-800 hover:bg-gray-800/40 transition">

                        <!-- الاسم -->
                        <td class="p-4 font-semibold flex items-center gap-3">
                            <div class="bg-gray-700 text-gray-300 w-10 h-10 rounded-full flex items-center justify-center shadow-inner">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <span>{{ $employee->name }}</span>
                        </td>

                        <!-- المتجر -->
                        <td class="p-4">
                            {{ $employee->store->name ?? '—' }}
                        </td>

                        <!-- تاريخ الحذف -->
                        <td class="p-4 text-gray-400 text-sm">
                            {{ $employee->deleted_at->format('Y-m-d H:i') }}
                        </td>

                        <!-- الإجراءات -->
                        <td class="p-4">
                            <div class="flex items-center justify-center gap-6">

                                <!-- استرجاع -->
                                <form action="{{ route('user.employees.restore', $employee->id) }}" method="POST">
                                    @csrf
                                    <button class="text-green-400 hover:text-green-300 transition" title="استرجاع">
                                        <i class="fa-solid fa-rotate-left text-xl"></i>
                                    </button>
                                </form>

                                <!-- حذف نهائي -->
                                <form action="{{ route('user.employees.forceDelete', $employee->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('هل تريد حذف هذا الموظف نهائيًا؟ لا يمكن التراجع')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-500 hover:text-red-400 transition" title="حذف نهائي">
                                        <i class="fa-solid fa-trash text-xl"></i>
                                    </button>
                                </form>

                            </div>
                        </td>

                    </tr>

                @empty
                    <tr>
                        <td colspan="4" class="p-10 text-center text-gray-500">
                            لا يوجد موظفين محذوفين
                        </td>
                    </tr>
                @endforelse

            </tbody>
        </table>

    </div>

    <!-- الباجينيشن -->
    <div class="mt-6 text-gray-400">
        {{ $employees->links() }}
    </div>

</div>

@endsection
