@php
    // تحديد الحالة برمجياً إذا لم يتم تمريرها
    $displayStatus = $status ?? ($store->trashed() ? 'deleted' : $store->status);

    $colors = [
        'active'    => 'bg-green-600/20 text-green-400 border-green-600/30',
        'suspended' => 'bg-red-600/20 text-red-400 border-red-600/30',
        'deleted'   => 'bg-gray-600/20 text-gray-400 border-gray-600/30',
    ];

    $label = [
        'active'    => 'نشط',
        'suspended' => 'موقوف',
        'deleted'   => 'محذوف مؤقتاً',
    ];
@endphp

<span class="px-3 py-1 text-xs font-medium rounded-full border {{ $colors[$displayStatus] ?? $colors['active'] }}">
    {{ $label[$displayStatus] ?? $label['active'] }}
</span>