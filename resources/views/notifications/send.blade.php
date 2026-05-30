@extends('dashboard.app')
@section('title', 'إرسال إشعار')

@section('content')
@php
    $oldTargetType = old('target_type', 'accountants');
    $oldTargetIds = collect(old('target_ids', []))->map(fn ($id) => (int) $id)->values()->all();
@endphp

<div class="max-w-5xl mx-auto px-4 py-6" x-data="notificationComposer(@js($oldTargetType), @js($oldTargetIds))">
    <div class="mb-6">
        <h1 class="text-3xl font-black text-white">مركز إرسال الإشعارات</h1>
        <p class="text-gray-400 mt-2 text-sm">إرسال إشعار داخلي للمحاسبين التابعين لك أو للإدارة بشكل سريع وآمن.</p>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 rounded-xl border border-emerald-500/40 bg-emerald-500/10 text-emerald-200 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-4 rounded-xl border border-red-500/40 bg-red-500/10 text-red-200 text-sm">
            <ul class="list-disc mr-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('user.notifications.send.store') }}" class="grid lg:grid-cols-3 gap-4">
        @csrf

        <div class="lg:col-span-2 bg-gradient-to-b from-gray-900 to-gray-900/70 border border-gray-800 rounded-2xl p-5 space-y-5">
            <div>
                <label class="text-gray-200 font-bold block mb-2">عنوان الإشعار</label>
                <input type="text" name="title" value="{{ old('title') }}" maxlength="255" required
                    class="w-full p-3 rounded-xl bg-gray-950 border border-gray-700 text-gray-100 focus:border-blue-500 focus:ring-0" placeholder="مثال: تحديث نظام العمل اليومي">
            </div>

            <div>
                <label class="text-gray-200 font-bold block mb-2">نص الإشعار</label>
                <textarea name="message" rows="7" maxlength="2000" required
                    class="w-full p-3 rounded-xl bg-gray-950 border border-gray-700 text-gray-100 focus:border-blue-500 focus:ring-0" placeholder="اكتب محتوى الإشعار هنا...">{{ old('message') }}</textarea>
            </div>
        </div>

        <div class="bg-gray-900/90 border border-gray-800 rounded-2xl p-5 space-y-4">
            <h2 class="text-white font-black text-lg">تحديد المستلمين</h2>

            <div class="grid grid-cols-1 gap-2">
                <button type="button" @click="setMode('accountants')"
                    :class="mode==='accountants' ? 'border-blue-500 bg-blue-500/15 text-blue-200' : 'border-gray-700 bg-gray-950 text-gray-300'"
                    class="w-full text-right p-3 rounded-xl border transition">
                    <div class="font-bold">عدة محاسبين</div>
                    <div class="text-xs opacity-80">يمكنك تحديد أكثر من محاسب</div>
                </button>

                <button type="button" @click="setMode('accountant')"
                    :class="mode==='accountant' ? 'border-blue-500 bg-blue-500/15 text-blue-200' : 'border-gray-700 bg-gray-950 text-gray-300'"
                    class="w-full text-right p-3 rounded-xl border transition">
                    <div class="font-bold">محاسب واحد</div>
                    <div class="text-xs opacity-80">يتم إرسال الإشعار لمحاسب واحد فقط</div>
                </button>

                <button type="button" @click="setMode('admin')"
                    :class="mode==='admin' ? 'border-amber-500 bg-amber-500/15 text-amber-200' : 'border-gray-700 bg-gray-950 text-gray-300'"
                    class="w-full text-right p-3 rounded-xl border transition">
                    <div class="font-bold">المدير العام</div>
                    <div class="text-xs opacity-80">إرسال مباشر للإدارة</div>
                </button>
            </div>

            <input type="hidden" name="target_type" :value="mode">

            <template x-if="mode !== 'admin'">
                <div>
                    <p class="text-xs text-gray-400 mb-2">اختر المحاسبين:</p>
                    <div class="max-h-72 overflow-y-auto space-y-2 pr-1">
                        @forelse($accountants as $acc)
                            <button type="button" @click="toggle({{ (int)$acc->id }})"
                                :class="selected.includes({{ (int)$acc->id }}) ? 'border-emerald-500 bg-emerald-500/10 text-emerald-200' : 'border-gray-700 bg-gray-950 text-gray-300'"
                                class="w-full text-right border rounded-xl p-3 transition">
                                <div class="font-bold">{{ $acc->name }}</div>
                                <div class="text-xs opacity-75">{{ $acc->email ?? $acc->phone ?? 'بدون بيانات إضافية' }}</div>
                            </button>
                        @empty
                            <div class="text-sm text-gray-400 p-3 rounded-xl border border-gray-700 bg-gray-950">لا يوجد محاسبون نشطون حاليًا.</div>
                        @endforelse
                    </div>

                    <template x-for="id in selected" :key="id">
                        <input type="hidden" name="target_ids[]" :value="id">
                    </template>
                </div>
            </template>

            <template x-if="mode==='admin'">
                <p class="text-sm text-gray-400 p-3 rounded-xl border border-gray-700 bg-gray-950">سيتم إرسال الإشعار إلى حسابات الإدارة مباشرة.</p>
            </template>

            <div class="pt-2 border-t border-gray-800">
                <button class="w-full py-3 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-black transition">إرسال الإشعار</button>
            </div>
        </div>
    </form>
</div>

<script>
    function notificationComposer(initialMode, initialIds) {
        return {
            mode: initialMode || 'accountants',
            selected: Array.isArray(initialIds) ? initialIds.map(Number) : [],
            setMode(next) {
                this.mode = next;
                if (next === 'admin') {
                    this.selected = [];
                }
                if (next === 'accountant' && this.selected.length > 1) {
                    this.selected = [this.selected[0]];
                }
            },
            toggle(id) {
                const idx = this.selected.indexOf(id);

                if (this.mode === 'accountant') {
                    this.selected = idx === -1 ? [id] : [];
                    return;
                }

                if (idx === -1) {
                    this.selected.push(id);
                } else {
                    this.selected.splice(idx, 1);
                }
            }
        }
    }
</script>
@endsection
