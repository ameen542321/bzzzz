@php
    $icons = [
        'sale' => ['icon' => 'fa-cart-shopping', 'color' => 'text-emerald-400', 'bg' => 'bg-emerald-500/10'],
        'withdrawal' => ['icon' => 'fa-hand-holding-dollar', 'color' => 'text-yellow-400', 'bg' => 'bg-yellow-500/10'],
        'employee_absence' => ['icon' => 'fa-user-clock', 'color' => 'text-orange-400', 'bg' => 'bg-orange-500/10'],
        'employee_debt' => ['icon' => 'fa-money-bill-wave', 'color' => 'text-amber-400', 'bg' => 'bg-amber-500/10'],
        'debt_collect' => ['icon' => 'fa-money-check-dollar', 'color' => 'text-teal-400', 'bg' => 'bg-teal-500/10'],
        'employee_debt_collect_partial' => ['icon' => 'fa-coins', 'color' => 'text-cyan-400', 'bg' => 'bg-cyan-500/10'],
        'employee_debt_collect_full' => ['icon' => 'fa-sack-dollar', 'color' => 'text-sky-400', 'bg' => 'bg-sky-500/10'],
        'credit_sale' => ['icon' => 'fa-file-invoice', 'color' => 'text-indigo-400', 'bg' => 'bg-indigo-500/10'],
        'credit_sale_partial' => ['icon' => 'fa-file-circle-check', 'color' => 'text-violet-400', 'bg' => 'bg-violet-500/10'],
        'credit_sale_deducted' => ['icon' => 'fa-file-circle-xmark', 'color' => 'text-purple-400', 'bg' => 'bg-purple-500/10'],
        'expense' => ['icon' => 'fa-file-invoice-dollar', 'color' => 'text-red-400', 'bg' => 'bg-red-500/10'],
        'expense_added' => ['icon' => 'fa-file-invoice-dollar', 'color' => 'text-rose-400', 'bg' => 'bg-rose-500/10'],
        'employee_internal_consumption' => ['icon' => 'fa-box-open', 'color' => 'text-lime-400', 'bg' => 'bg-lime-500/10'],
        'default' => ['icon' => 'fa-circle-dot', 'color' => 'text-gray-400', 'bg' => 'bg-gray-500/10']
    ];
    $style = $icons[$type] ?? $icons['default'];
@endphp

<div class="{{ $style['bg'] }} {{ $style['color'] }} p-2 rounded-lg w-10 h-10 flex items-center justify-center">
    <i class="fa-solid {{ $style['icon'] }}"></i>
</div>
