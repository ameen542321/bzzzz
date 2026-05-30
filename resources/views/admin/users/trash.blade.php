@extends('dashboard.app')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-white flex items-center gap-2">
            <i class="fa-solid fa-trash-arrow-up text-red-500"></i> سلة المحذوفات
        </h1>
        <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-400 hover:text-white transition">العودة للقائمة</a>
    </div>

    <div class="bg-gray-900/50 border border-gray-800 rounded-3xl overflow-hidden shadow-xl">
        <table class="w-full text-right" dir="rtl">
            <thead class="bg-gray-800/50 text-gray-400 text-xs uppercase">
                <tr>
                    <th class="px-6 py-4">التاجر</th>
                    <th class="px-6 py-4 text-center">تاريخ الحذف</th>
                    <th class="px-6 py-4 text-center">الإجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse($users as $user)
                <tr class="hover:bg-white/[0.02]">
                    <td class="px-6 py-4">
                        <div class="text-white font-medium">{{ $user->name }}</div>
                        <div class="text-xs text-gray-500">{{ $user->email }}</div>
                    </td>
                    <td class="px-6 py-4 text-center text-gray-400 text-sm">
                        {{ $user->deleted_at->diffForHumans() }}
                    </td>
                    <td class="px-6 py-4 flex items-center justify-center gap-2">
                        {{-- زر الاستعادة --}}
                        <form action="{{ route('admin.users.restore', $user->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="p-2 text-emerald-500 hover:bg-emerald-500/10 rounded-lg transition" title="استعادة">
                                <i class="fa-solid fa-rotate-left"></i>
                            </button>
                        </form>

                        {{-- زر الحذف النهائي --}}
                        <form action="{{ route('admin.users.force-delete', $user->id) }}" method="POST" onsubmit="return confirm('حذف نهائي من القاعدة؟')">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-2 text-red-500 hover:bg-red-500/10 rounded-lg transition" title="حذف نهائي">
                                <i class="fa-solid fa-eraser"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-6 py-10 text-center text-gray-600 italic">السلة فارغة حالياً</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
