@extends('dashboard.app')
@section('content')

<div class="p-6"
     x-data="{
        {{-- يفتح المودال تلقائياً إذا كان هناك خطأ في التحقق من البيانات المرسلة --}}
        openAddModal: {{ $errors->any() ? 'true' : 'false' }}, 
        openEditModal: false,
        editUser: {},
        openRow: null
     }">

    {{-- تنبيهات النجاح --}}
    @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm flex items-center gap-2">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- الهيدر --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">إدارة المستخدمين</h1>
            <p class="text-gray-500 text-xs mt-1">عرض وإدارة حسابات التجار والمحاسبين</p>
        </div>

        <button @click="openAddModal = true"
            class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700 transition flex items-center gap-2 shadow-sm">
            <i class="fa-solid fa-user-plus"></i>
            إضافة مستخدم
        </button>
    </div>

    {{-- البحث + الفلترة --}}
    <form method="GET" class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-3 text-right" dir="rtl">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="ابحث بالاسم أو البريد..."
               class="p-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 shadow-sm outline-none">


        <select name="status" class="p-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 shadow-sm outline-none">
            <option value="all">كل الحالات</option>
            <option value="active" {{ request('status')=='active' ? 'selected' : '' }}>نشط</option>
            <option value="suspended" {{ request('status')=='suspended' ? 'selected' : '' }}>موقوف</option>
        </select>

        <button type="submit" class="px-4 py-2 bg-gray-800 dark:bg-gray-700 text-white rounded-lg hover:bg-gray-900 transition shadow-sm font-bold">
            تطبيق الفلترة
        </button>
    </form>

    {{-- الجدول --}}
    <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
        <table class="w-full text-right text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-200">
                <tr>
                    <th class="p-4 w-16 text-center">#</th>
                    <th class="p-4 text-right">المستخدم</th>
                    <th class="p-4 hidden md:table-cell text-center">الدور</th>
                    <th class="p-4 hidden md:table-cell text-center">الحالة</th>
                    <th class="p-4 hidden md:table-cell text-center">انتهاء الاشتراك</th>
                    <th class="p-4 text-center">التحكم</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($users as $user)
                    @php
                        $daysLeft = $user->subscription_end_at ? \Carbon\Carbon::now()->diffInDays($user->subscription_end_at, false) : null;
                        $subColor = 'text-gray-400';
                        if ($daysLeft !== null) {
                            $subColor = ($daysLeft < 0) ? 'text-red-500' : (($daysLeft <= 7) ? 'text-yellow-500' : 'text-emerald-500');
                        }
                    @endphp

                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                        <td class="p-4 text-gray-500 text-center">{{ $user->id }}</td>
                        <td class="p-4 text-right">
                            <div class="font-medium text-gray-900 dark:text-white">{{ $user->name }}</div>
                            <div class="text-[10px] text-gray-500">{{ $user->email }}</div>
                        </td>
                        <td class="p-4 hidden md:table-cell text-center">
                            <span class="px-2 py-1 {{ $user->role === 'accountant' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400' }} rounded-md text-[10px]">
                                {{ $user->role === 'accountant' ? 'محاسب' : 'تاجر' }}
                            </span>
                        </td>
                        <td class="p-4 hidden md:table-cell text-center">
                            <form action="{{ route('admin.users.toggleStatus', $user->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" class="flex items-center justify-center gap-1.5 mx-auto hover:opacity-75 transition">
                                    <div class="w-2 h-2 rounded-full {{ $user->status === 'active' ? 'bg-emerald-500 shadow-[0_0_5px_rgba(16,185,129,0.5)]' : 'bg-red-500 shadow-[0_0_5px_rgba(239,68,68,0.5)]' }}"></div>
                                    <span class="{{ $user->status === 'active' ? 'text-emerald-500' : 'text-red-500' }} text-[11px] font-bold">
                                        {{ $user->status === 'active' ? 'نشط' : 'موقوف' }}
                                    </span>
                                </button>
                            </form>
                        </td>
                        <td class="p-4 hidden md:table-cell text-center {{ $subColor }} font-medium text-xs">
                            {{ $user->subscription_end_at ? \Carbon\Carbon::parse($user->subscription_end_at)->format('Y-m-d') : '—' }}
                        </td>
                        <td class="p-4">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.users.show', $user->id) }}" class="p-1 text-gray-400 hover:text-blue-500 transition" title="عرض"><i class="fa-solid fa-eye"></i></a>
                                <button @click="
                                    openEditModal = true;
                                    editUser = {
                                        id: '{{ $user->id }}',
                                        name: '{{ $user->name }}',
                                        phone: '{{ $user->phone ?? '' }}',
                                        email: '{{ $user->email }}',
                                        status: '{{ $user->status }}',
                                        plan_id: '{{ $user->plan_id }}',
                                        allowed_stores: '{{ $user->allowed_stores }}',
                                        allowed_accountants: '{{ $user->allowed_accountants }}',
                                        subscription_end_at: '{{ $user->subscription_end_at ? \Carbon\Carbon::parse($user->subscription_end_at)->format('Y-m-d') : '' }}',
                                        expires_at: '{{ $user->expires_at ? \Carbon\Carbon::parse($user->expires_at)->format('Y-m-d') : '' }}'
                                    }"
                                    class="p-1 text-gray-400 hover:text-yellow-500 transition" title="تعديل"><i class="fa-solid fa-pen-to-square"></i></button>
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('نقل إلى سلة المحذوفات؟')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1 text-gray-400 hover:text-red-500 transition" title="حذف ناعم"><i class="fa-solid fa-trash-can"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">{{ $users->links() }}</div>

    {{-- سلة المحذوفات --}}
    <div class="mt-6">
        <a href="{{ route('admin.users.trash') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-800/50 border border-gray-700 text-gray-300 rounded-xl text-sm relative hover:bg-red-500/10 transition-all group">
            <i class="fa-solid fa-trash-arrow-up text-xs group-hover:animate-bounce"></i> سلة المحذوفات
            @php $trashCount = \App\Models\User::onlyTrashed()->count(); @endphp
            @if($trashCount > 0)
                <span class="absolute -top-1 -left-1 w-4 h-4 bg-red-600 text-white text-[9px] flex items-center justify-center rounded-full border border-gray-900 font-bold">{{ $trashCount }}</span>
            @endif
        </a>
    </div>

    {{-- مودال إضافة مستخدم --}}
    <div x-show="openAddModal" x-cloak class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div @click.away="openAddModal = false" class="bg-white dark:bg-gray-800 w-full max-w-md p-6 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-2xl">
            <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-white flex items-center gap-2 text-right" dir="rtl">
                <i class="fa-solid fa-user-plus text-blue-600"></i> إضافة تاجر جديد
            </h3>
            
            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4 text-right" dir="rtl">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] text-gray-400 mb-1">الاسم</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="w-full p-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-sm outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-[10px] text-gray-400 mb-1">الهاتف</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="w-full p-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-sm outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] text-gray-400 mb-1">البريد الإلكتروني</label>
                    <input type="email" name="email" value="{{ old('email') }}" required 
                           class="w-full p-2 bg-gray-50 dark:bg-gray-900 border {{ $errors->has('email') ? 'border-red-500' : 'border-gray-200 dark:border-gray-700' }} rounded-lg text-sm outline-none focus:ring-1 focus:ring-blue-500">
                    @error('email')
                        <p class="text-[10px] text-red-500 mt-1 font-bold">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-[10px] text-gray-400 mb-1">الخطة</label>
                    <select name="plan_id" class="w-full p-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-sm outline-none cursor-pointer">
                        <option value="">خطة تلقائية</option>
                        @foreach(App\Models\Plan::all() as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <input type="password" name="password" placeholder="كلمة المرور" required class="w-full p-2 bg-gray-50 dark:bg-gray-900 border {{ $errors->has('password') ? 'border-red-500' : 'border-gray-200 dark:border-gray-700' }} rounded-lg text-sm outline-none">
                        @error('password')
                            <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <input type="password" name="password_confirmation" placeholder="تأكيد الكلمة" required class="w-full p-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-sm outline-none">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-4">
                    <button type="button" @click="openAddModal = false" class="px-4 py-2 text-xs text-gray-500">إلغاء</button>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 transition-colors shadow-lg shadow-blue-600/20">حفظ البيانات</button>
                </div>
            </form>
        </div>
    </div>

    {{-- مودال تعديل مستخدم --}}
    <div x-show="openEditModal" x-cloak class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div @click.away="openEditModal = false" class="bg-white dark:bg-gray-800 w-full max-w-md p-6 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-2xl max-h-[90vh] overflow-y-auto custom-scrollbar">
            <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-white text-right" dir="rtl">تعديل بيانات التاجر</h3>
            <form method="POST" :action="`{{ url('admin/users') }}/${editUser.id}`" class="space-y-4 text-right" dir="rtl">
                @csrf @method('PUT')
                <div>
                    <label class="block text-[10px] text-gray-400 mb-1">الاسم</label>
                    <input type="text" name="name" x-model="editUser.name" class="w-full p-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-sm outline-none">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] text-gray-400 mb-1">البريد</label>
                        <input type="email" name="email" x-model="editUser.email" class="w-full p-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] text-gray-400 mb-1">الهاتف</label>
                        <input type="text" name="phone" x-model="editUser.phone" class="w-full p-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-sm outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] text-gray-400 mb-1">الحالة</label>
                        <select name="status" x-model="editUser.status" class="w-full p-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs outline-none">
                            <option value="active">نشط</option>
                            <option value="suspended">موقوف</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] text-gray-400 mb-1">الخطة</label>
                        <select name="plan_id" x-model="editUser.plan_id" class="w-full p-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs outline-none">
                            @foreach(App\Models\Plan::all() as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[9px] text-gray-400">نهاية الاشتراك</label>
                        <input type="date" name="subscription_end_at" x-model="editUser.subscription_end_at" class="w-full p-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs outline-none">
                    </div>
                    <div>
                        <label class="block text-[9px] text-gray-400">إغلاق الحساب</label>
                        <input type="date" name="expires_at" x-model="editUser.expires_at" class="w-full p-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs outline-none">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-4">
                    <button type="button" @click="openEditModal = false" class="px-4 py-2 text-xs text-gray-500">إلغاء</button>
                    <button type="submit" class="px-6 py-2 bg-yellow-500 text-white rounded-xl text-xs font-bold hover:bg-yellow-600 transition-colors shadow-lg shadow-yellow-500/20">تحديث</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #4b5563; border-radius: 10px; }
</style>

@endsection