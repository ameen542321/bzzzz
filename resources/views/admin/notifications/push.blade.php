@extends('dashboard.app')

@section('content')
<div class="p-6 space-y-6">

    <h1 class="text-3xl font-bold text-white mb-4">إرسال إشعار OneSignal</h1>

    <form action="{{ route('admin.notifications.push.store') }}" method="POST" class="space-y-6">
        @csrf

        {{-- بطاقة اختيار الفئة --}}
        <div class="bg-gray-900 border border-gray-700 rounded-xl p-6">
            <h2 class="text-xl font-semibold text-white mb-4">الفئة المستهدفة</h2>

            <select name="target_type" id="target_type"
                class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg p-3 focus:ring-2 focus:ring-blue-500">
                <option value="all">جميع الأجهزة</option>
                <option value="users">المستخدمين فقط</option>
                <option value="accountants">المحاسبين فقط</option>
            </select>
        </div>

        {{-- بطاقة المستلمين --}}
        <div class="bg-gray-900 border border-gray-700 rounded-xl p-6">

            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-white">المستلمون</h2>
                <span id="selectedCount" class="text-gray-300 text-sm">0 مختار</span>
            </div>

            {{-- أدوات التحكم --}}
            <div class="flex items-center gap-3 mb-4">
                <button type="button" id="selectAll"
                    class="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm">
                    اختيار الكل
                </button>

                <button type="button" id="clearAll"
                    class="bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded-lg text-sm">
                    إلغاء الكل
                </button>

                <select id="filterType"
                    class="bg-gray-800 border border-gray-700 text-white rounded-lg p-2 text-sm">
                    <option value="all">الكل</option>
                    <option value="user">المستخدمين</option>
                    <option value="accountant">المحاسبين</option>
                </select>
            </div>

            {{-- مربع البحث --}}
            <input type="text" id="recipientSearch"
                class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg p-3 mb-4"
                placeholder="ابحث عن مستخدم أو محاسب...">

            {{-- التاجز المختارة --}}
            <div id="selectedRecipients" class="flex flex-wrap gap-2 mb-4"></div>

            {{-- القائمة --}}
            <div id="recipientList" class="space-y-2 max-h-64 overflow-y-auto">

                @foreach($users as $user)
                    <div class="recipient-item flex items-center gap-3 p-3 bg-gray-800 rounded-lg cursor-pointer hover:bg-gray-700"
                        data-id="{{ $user->id }}"
                        data-name="{{ $user->name }}"
                        data-type="user">

                        <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white">
                            {{ mb_substr($user->name, 0, 1) }}
                        </div>

                        <div>
                            <div class="text-white font-semibold">{{ $user->name }}</div>
                            <div class="text-gray-400 text-sm">مستخدم</div>
                        </div>
                    </div>
                @endforeach

                @foreach($accountants as $acc)
                    <div class="recipient-item flex items-center gap-3 p-3 bg-gray-800 rounded-lg cursor-pointer hover:bg-gray-700"
                        data-id="{{ $acc->id }}"
                        data-name="{{ $acc->name }}"
                        data-type="accountant">

                        <div class="w-10 h-10 rounded-full bg-green-600 flex items-center justify-center text-white">
                            {{ mb_substr($acc->name, 0, 1) }}
                        </div>

                        <div>
                            <div class="text-white font-semibold">{{ $acc->name }}</div>
                            <div class="text-gray-400 text-sm">محاسب</div>
                        </div>
                    </div>
                @endforeach

            </div>

            {{-- الحقل المخفي --}}
            <input type="hidden" name="target_ids" id="target_ids">
        </div>

        {{-- بطاقة كتابة الإشعار --}}
        <div class="bg-gray-900 border border-gray-700 rounded-xl p-6">
            <h2 class="text-xl font-semibold text-white mb-4">محتوى الإشعار</h2>

            <div class="space-y-4">
                <div>
                    <label class="text-gray-300 mb-1 block">عنوان الإشعار</label>
                    <input type="text" name="title"
                        class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg p-3 focus:ring-2 focus:ring-blue-500"
                        placeholder="مثال: تحديث مهم في النظام">
                </div>

                <div>
                    <label class="text-gray-300 mb-1 block">نص الإشعار</label>
                    <textarea name="message" rows="5"
                        class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg p-3 focus:ring-2 focus:ring-blue-500"
                        placeholder="اكتب محتوى الإشعار هنا..."></textarea>
                </div>

                <button
                    class="bg-blue-600 hover:bg-blue-700 transition text-white px-6 py-3 rounded-lg font-semibold text-lg">
                    إرسال الإشعار الآن
                </button>
            </div>
        </div>

    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const selected = new Map()
    const items = document.querySelectorAll('.recipient-item')
    const selectedContainer = document.getElementById('selectedRecipients')
    const selectedCount = document.getElementById('selectedCount')
    const hiddenInput = document.getElementById('target_ids')
    const list = document.getElementById('recipientList')

    function updateCount() {
        selectedCount.textContent = `${selected.size} مختار`
    }

    function renderSelected() {
        selectedContainer.innerHTML = ''

        selected.forEach(rec => {
            const tag = document.createElement('div')
            tag.className = "flex items-center gap-2 bg-blue-700 text-white px-3 py-1 rounded-full"

            tag.innerHTML = `
                <span>${rec.name}</span>
                <button class="text-white hover:text-red-300" onclick="removeRecipient('${rec.id}')">×</button>
            `

            selectedContainer.appendChild(tag)
        })

        hiddenInput.value = JSON.stringify([...selected.keys()])
        updateCount()
    }

    window.removeRecipient = function(id) {
        selected.delete(id)
        renderSelected()
    }

    items.forEach(item => {
        item.addEventListener('click', () => {
            const id = item.dataset.id
            const name = item.dataset.name
            const type = item.dataset.type

            if (!selected.has(id)) {
                selected.set(id, { id, name, type })
                renderSelected()
            }
        })
    })

    document.getElementById('selectAll').addEventListener('click', () => {
        document.querySelectorAll('.recipient-item').forEach(item => {
            if (item.style.display !== 'none') {
                const id = item.dataset.id
                const name = item.dataset.name
                const type = item.dataset.type
                selected.set(id, { id, name, type })
            }
        })
        renderSelected()
    })

    document.getElementById('clearAll').addEventListener('click', () => {
        selected.clear()
        renderSelected()
    })

    document.getElementById('filterType').addEventListener('change', function () {
        const type = this.value

        items.forEach(item => {
            if (type === 'all' || item.dataset.type === type) {
                item.style.display = 'flex'
            } else {
                item.style.display = 'none'
            }
        })
    })

    document.getElementById('target_type').addEventListener('change', function () {
        const type = this.value

        selected.clear()
        renderSelected()

        if (type === 'all') {
            list.style.display = 'none'
            return
        }

        list.style.display = 'block'

        items.forEach(item => {
            if (type === 'users' && item.dataset.type === 'user') {
                item.style.display = 'flex'
            } else if (type === 'accountants' && item.dataset.type === 'accountant') {
                item.style.display = 'flex'
            } else {
                item.style.display = 'none'
            }
        })
    })

    document.getElementById('recipientSearch').addEventListener('input', function () {
        const term = this.value.toLowerCase()

        items.forEach(item => {
            const name = item.dataset.name.toLowerCase()
            item.style.display = name.includes(term) ? 'flex' : 'none'
        })
    })

})
</script>

@endsection
