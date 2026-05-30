@extends('dashboard.app')

@section('title', 'سجل العمليات')

@section('content')
<div class="space-y-6">

    {{-- العنوان --}}
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-200">سجل العمليات</h1>
    </div>

    {{-- الفلاتر --}}
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-gray-800 p-4 rounded-lg">

        <div>
            <label class="text-gray-400 text-sm">نوع العملية</label>
            <select name="action" class="w-full bg-gray-900 border border-gray-700 text-gray-200 rounded-lg px-3 py-2">
                <option value="">الكل</option>
                @foreach ($actions as $action)
                    <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                        {{ $action }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-gray-400 text-sm">من تاريخ</label>
            <input type="date" name="from_date"
                   value="{{ request('from_date') }}"
                   class="w-full bg-gray-900 border border-gray-700 text-gray-200 rounded-lg px-3 py-2">
        </div>

        <div>
            <label class="text-gray-400 text-sm">إلى تاريخ</label>
            <input type="date" name="to_date"
                   value="{{ request('to_date') }}"
                   class="w-full bg-gray-900 border border-gray-700 text-gray-200 rounded-lg px-3 py-2">
        </div>

        <div class="flex items-end">
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg w-full">
                تطبيق الفلاتر
            </button>
        </div>

    </form>

    {{-- جدول اللوق --}}
    <div class="overflow-x-auto bg-gray-800 rounded-lg shadow">
        <table class="w-full text-gray-300">
            <thead class="bg-gray-700 text-gray-300">
                <tr>
                    <th class="px-4 py-3 text-right">العملية</th>
                    <th class="px-4 py-3 text-right">الوصف</th>
                    <th class="px-4 py-3 text-right">التاريخ</th>
                    <th class="px-4 py-3 text-right">تفاصيل</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($logs as $log)
                    <tr class="border-b border-gray-700 hover:bg-gray-700/50">

                        {{-- العملية --}}
                        <td class="px-4 py-3 font-semibold">
                            <span class="px-2 py-1 rounded bg-blue-600 text-white text-sm">
                                {{ $log->action_label }}
                            </span>
                        </td>

                        {{-- الوصف --}}
                        <td class="px-4 py-3">
                            <span title="{{ $log->description }}">
                                {{ $log->snippet }}
                            </span>
                        </td>

                        {{-- التاريخ --}}
                        <td class="px-4 py-3">
                            {{ $log->created_at->format('Y-m-d H:i') }}
                        </td>

                        {{-- زر التفاصيل --}}
                        <td class="px-4 py-3">
                           <button
    onclick='showLogDetails({
        "id": {{ $log->id }},
        "user": @json($log->user),
        "store": @json($log->store),
        "actor": @json($log->actor),
        "model": @json($log->model),
        "details": @json($log->details)
    })'
    class="text-blue-400 hover:text-blue-300"
>
    عرض
</button>

                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-6 text-gray-400">
                            لا توجد سجلات
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- الترقيم --}}
    <div>
        {{ $logs->links('pagination::tailwind') }}
    </div>

</div>

{{-- نافذة التفاصيل --}}
<script>
function showLogDetails(log) {

    let details = log.details;

    // إذا كانت details نص → نحولها إلى Object
    if (typeof details === "string") {
        try {
            details = JSON.parse(details);
        } catch (e) {
            details = null;
        }
    }

    let html = `
        <table style="width:100%; text-align:right; border-collapse:collapse;">
    `;

    /* ---------------------------------------------------------
     * 1) المستخدم
     * --------------------------------------------------------- */
    html += `
        <tr>
            <td style="padding:8px; color:#4FC3F7; width:130px;">المستخدم:</td>
            <td style="padding:8px; color:#fff;">
                ${log.user ? log.user.name : '-'}
            </td>
        </tr>
    `;

    /* ---------------------------------------------------------
     * 2) المتجر الحالي
     * --------------------------------------------------------- */
    html += `
        <tr>
            <td style="padding:8px; color:#4FC3F7;">المتجر:</td>
            <td style="padding:8px; color:#fff;">
                ${log.store ? log.store.name : '-'}
            </td>
        </tr>
    `;

    /* ---------------------------------------------------------
     * خط فاصل
     * --------------------------------------------------------- */
    html += `
        <tr>
            <td colspan="2">
                <hr style="border-color:#374151; margin:10px 0;">
            </td>
        </tr>
    `;

    /* ---------------------------------------------------------
     * 3) إذا لا توجد تفاصيل
     * --------------------------------------------------------- */
    if (!details) {
        html += `
            <tr>
                <td colspan="2" style="padding:8px; color:#9CA3AF;">
                    لا توجد تفاصيل إضافية لهذه العملية.
                </td>
            </tr>
        `;
    } else {

        /* ---------------------------------------------------------
         * 4) عرض الحقول التي تغيّرت فقط
         * --------------------------------------------------------- */
        if (details.old_values || details.new_values) {

            const oldVals = details.old_values || {};
            const newVals = details.new_values || {};

            html += `
                <tr>
                    <td colspan="2" style="padding:10px; color:#4FC3F7; font-size:16px;">
                        🔍 الحقول التي تم تعديلها
                    </td>
                </tr>
            `;

            for (const key in newVals) {

                const oldVal = oldVals[key] ?? "—";
                const newVal = newVals[key] ?? "—";

                if (oldVal == newVal) continue;

                let label = key;

                if (key === "store_id") label = "نقل إلى متجر";

                // استبدال رقم المتجر باسم المتجر
                let oldDisplay = oldVal;
                let newDisplay = newVal;

                if (key === "store_id") {
                    oldDisplay = oldVal == log.store?.id ? log.store.name : oldVal;
                    newDisplay = newVal == log.store?.id ? log.store.name : newVal;
                }

                html += `
                    <tr>
                        <td style="padding:8px; color:#9CA3AF;">${label}</td>
                        <td style="padding:8px;">
                            <div style="color:#F87171;">❌ القديم: ${oldDisplay}</div>
                            <div style="color:#4ADE80;">✅ الجديد: ${newDisplay}</div>
                        </td>
                    </tr>
                `;
            }
        }

        /* ---------------------------------------------------------
         * 5) عرض باقي التفاصيل (store_id, employee_id…)
         * --------------------------------------------------------- */
        for (const key in details) {

            if (key === 'old_values' || key === 'new_values') continue;

            let label = key;
            let value = details[key];

            if (key === "store_id") {
                label = "المتجر الحالي";
                value = log.store ? log.store.name : value;
            }

            if (key === "employee_id") {
                label = "الموظف";
            }

            html += `
                <tr>
                    <td style="padding:8px; color:#4FC3F7;">${label}:</td>
                    <td style="padding:8px; color:#fff;">${value}</td>
                </tr>
            `;
        }
    }

    html += "</table>";

    Swal.fire({
        title: "تفاصيل العملية",
        html: html,
        confirmButtonText: "إغلاق",
        background: "#1f2937",
        color: "#fff",
        confirmButtonColor: "#3b82f6",
        width: 650
    });
}
</script>


@endsection
