@extends('dashboard.app')
@section('title', 'المصروفات')

@section('content')
@php
    $isAccountant = $isAccountant ?? auth('accountant')->check();
    $isOwnerInternalUseRoute = $isOwnerInternalUseRoute ?? request()->routeIs('user.stores.internal-use.add-consumption');

    $dashboardRoute = $isAccountant ? route('accountant.dashboard') : route('user.dashboard');

    if ($isOwnerInternalUseRoute) {
        $indexRoute = route('user.stores.internal-use.add-consumption', request()->route('store'));
        $storeRoute = route('user.stores.internal-use.add-consumption.store', request()->route('store'));
    } else {
        $indexRoute = $isAccountant ? route('accountant.pos.expense.page') : route('user.expense.page');
        $storeRoute = $isAccountant ? route('accountant.pos.expense.store') : route('user.expense.store');
    }


    $canEditExpenses = !$isAccountant;
    $updateRouteTemplate = $canEditExpenses
        ? route('user.expense.update', ['id' => '__ID__'])
        : null;

    $expensesByDay = ($expenses ?? collect())->groupBy(function ($expense) {
        return optional($expense->created_at)->format('Y-m-d');
    });

@endphp

<div class="max-w-7xl mx-auto px-4 py-6 text-right" dir="rtl">
    @if(session('success'))
        <div class="mb-3 p-3 bg-green-500/10 border border-green-500/50 rounded-lg text-green-400 text-sm">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-3 p-3 bg-red-500/10 border border-red-500/50 rounded-lg text-red-400 text-sm">⚠️ {{ session('error') }}</div>
    @endif

    <div class="mb-5 bg-gray-800/50 p-4 rounded-2xl border border-gray-700">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-3">
            <div>
                <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                    <i class="fas fa-boxes text-green-500"></i>
                    المصروفات
                </h1>
                <p class="text-gray-400 text-sm mt-1">{{ $storeModel->name ?? 'المتجر' }} - إضافة وعرض المصروفات</p>
            </div>
            <a href="{{ $dashboardRoute }}" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-xl text-sm">رجوع</a>
        </div>
    </div>

    <div class="mb-4 bg-gray-800/50 p-4 rounded-2xl border border-gray-700">
        <div class="flex gap-2 w-full lg:w-auto">
            <button onclick="openExpenseModal()" class="bg-green-600 hover:bg-green-500 text-white px-4 py-2 rounded-lg text-sm">إضافة مصروف</button>
        </div>
    </div>

    <div class="bg-gray-800/40 border border-gray-700 rounded-2xl overflow-hidden">
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-right">
                <thead class="bg-gray-900/50 text-gray-400 text-xs">
                    <tr>
                        <th class="py-3 px-3">#</th>
                        <th class="py-3 px-3">النوع</th>
                        <th class="py-3 px-3">المصدر</th>
                        <th class="py-3 px-3">الوصف</th>
                        <th class="py-3 px-3 text-center">المبلغ</th>
                        <th class="py-3 px-3">الوقت</th>
                        
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700/40 text-sm">
                    @forelse($expensesByDay as $day => $dayExpenses)
                        <tr class="bg-gray-900/80">
                            <td colspan="6" class="py-2 px-3 text-cyan-300 font-bold">
                                يوم {{ \Carbon\Carbon::parse($day)->locale('ar')->translatedFormat('l') }} | التاريخ {{ $day }}
                            </td>
                        </tr>
                        @foreach($dayExpenses as $index => $expense)
                            @php $isOwnerPurchase = $expense->actor_type === 'owner_purchase'; @endphp
                            <tr class="hover:bg-gray-700/20">
                                <td class="py-3 px-3 text-gray-500">{{ $index + 1 }}</td>
                                <td class="py-3 px-3 text-white">{{ $expense->type }}</td>
                                <td class="py-3 px-3">
                                    <span class="text-xs px-2 py-1 rounded bg-blue-500/20 text-blue-400">عام</span>
                                </td>
                                <td class="py-3 px-3 text-gray-300">{{ $expense->description ?: '-' }}</td>
                                <td class="py-3 px-3 text-center text-yellow-400 font-bold">{{ number_format($expense->amount, 2) }} ر.س</td>
                                <td class="py-3 px-3 text-gray-300">{{ $expense->created_at->format('H:i') }}</td>
                            </tr>
                        @endforeach
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-gray-400">لا توجد بيانات في هذا الشهر.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="md:hidden divide-y divide-gray-700/40">
            @forelse($expensesByDay as $day => $dayExpenses)
                <div class="p-3 bg-gray-900/40 text-cyan-300 text-xs font-bold">يوم {{ \Carbon\Carbon::parse($day)->locale('ar')->translatedFormat('l') }} | التاريخ {{ $day }}</div>
                @foreach($dayExpenses as $expense)
                    @php $isOwnerPurchase = $expense->actor_type === 'owner_purchase'; @endphp
                    <div class="p-3">
                        <div class="flex justify-between mb-2">
                            <div>
                                <p class="text-white font-bold text-sm">{{ $expense->type }}</p>
                                <p class="text-xs text-blue-400">عام</p>
                            </div>
                            <p class="text-yellow-400 font-bold">{{ number_format($expense->amount, 2) }} ر.س</p>
                        </div>
                        <p class="text-xs text-gray-400">{{ $expense->description ?: '-' }}</p>
                        <div class="flex items-center justify-between mt-2">
                            <p class="text-[11px] text-gray-500">{{ $expense->created_at->format('H:i') }}</p>
                        </div>
                    </div>
                @endforeach
            @empty
                <div class="p-6 text-center text-gray-400">لا توجد بيانات في هذا الشهر.</div>
            @endforelse
        </div>
    </div>
</div>

<div id="expenseModal" class="hidden fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-3">
    <div class="bg-gray-900 border border-gray-800 rounded-xl w-full max-w-md shadow-2xl">
        <div class="flex justify-between items-center p-3 border-b border-gray-800">
            <h3 class="text-sm font-bold text-white">إضافة مصروف</h3>
            <button type="button" onclick="closeExpenseModal()" class="text-gray-400 hover:text-white">✕</button>
        </div>

        <form action="{{ $storeRoute }}" method="POST" class="p-3 space-y-3">
            @csrf

            @if(!$isAccountant)
                <div>
                    <label class="block text-xs text-gray-300 mb-1">مصدر المصروف</label>
                    <select name="consumption_source" class="w-full bg-gray-950 border border-gray-800 text-white rounded-lg p-2 text-sm">
                        <option value="operational"></option>
                        <option value="direct_purchase">عام</option>
                    </select>
                </div>
            @else
                <input type="hidden" name="consumption_source" value="operational">
            @endif

            <div>
                <label class="block text-xs text-gray-300 mb-1">نوع المصروف</label>
                <input type="text" name="type" list="ownerPurchaseTypes" class="w-full bg-gray-950 border border-gray-800 text-white rounded-lg p-2 text-sm" placeholder="مثال: غداء / عشاء / فطور / خبز">
                <datalist id="ownerPurchaseTypes">
                    <option value="غداء"></option>
                    <option value="عشاء"></option>
                    <option value="فطور"></option>
                    <option value="خبز"></option>
                </datalist>
            </div>

            <div>
                <label class="block text-xs text-gray-300 mb-1">المبلغ</label>
                <input type="number" name="amount" step="0.01" min="0.01" required class="w-full bg-gray-950 border border-gray-800 text-white rounded-lg p-2 text-sm" placeholder="0.00">
            </div>

            <div>
                <label class="block text-xs text-gray-300 mb-1">الوصف</label>
                <textarea name="description" rows="2" class="w-full bg-gray-950 border border-gray-800 text-white rounded-lg p-2 text-sm" placeholder="تفاصيل إضافية..."></textarea>
            </div>

            <div class="flex gap-2 pt-2">
                <button type="submit" class="flex-1 bg-green-600 hover:bg-green-500 text-white py-2 rounded-lg text-sm">حفظ</button>
            </div>
        </form>
    </div>
</div>



@if($canEditExpenses)
<div id="editExpenseModal" class="hidden fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-3">
    <div class="bg-gray-900 border border-gray-800 rounded-xl w-full max-w-md shadow-2xl">
        <div class="flex justify-between items-center p-3 border-b border-gray-800">
            <h3 class="text-sm font-bold text-white">تعديل مصروف</h3>
            <button type="button" onclick="closeEditExpenseModal()" class="text-gray-400 hover:text-white">✕</button>
        </div>

        <form id="editExpenseForm" method="POST" class="p-3 space-y-3">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs text-gray-300 mb-1">نوع المصروف</label>
                <input type="text" id="editType" name="type" required class="w-full bg-gray-950 border border-gray-800 text-white rounded-lg p-2 text-sm">
            </div>

            <div>
                <label class="block text-xs text-gray-300 mb-1">المبلغ</label>
                <input type="number" id="editAmount" name="amount" step="0.01" min="0.01" required class="w-full bg-gray-950 border border-gray-800 text-white rounded-lg p-2 text-sm">
            </div>

            <div>
                <label class="block text-xs text-gray-300 mb-1">الوصف</label>
                <textarea id="editDescription" name="description" rows="2" class="w-full bg-gray-950 border border-gray-800 text-white rounded-lg p-2 text-sm"></textarea>
            </div>

            <div class="flex gap-2 pt-2">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-500 text-white py-2 rounded-lg text-sm">تحديث</button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
function openExpenseModal() { document.getElementById('expenseModal').classList.remove('hidden'); }
function closeExpenseModal() { document.getElementById('expenseModal').classList.add('hidden'); }

@if($canEditExpenses)
function openEditExpenseModal(button) {
    const id = button.dataset.id;
    const type = button.dataset.type || '';
    const amount = button.dataset.amount || '';
    const description = button.dataset.description || '';

    const action = @json($updateRouteTemplate);
    document.getElementById('editExpenseForm').action = action.replace('__ID__', id);
    document.getElementById('editType').value = type;
    document.getElementById('editAmount').value = amount;
    document.getElementById('editDescription').value = description;
    document.getElementById('editExpenseModal').classList.remove('hidden');
}

function closeEditExpenseModal() {
    document.getElementById('editExpenseModal').classList.add('hidden');
}
@endif
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') return;
});
</script>
@endsection
