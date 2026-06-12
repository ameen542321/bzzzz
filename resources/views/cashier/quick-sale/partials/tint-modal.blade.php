{{--
    المرحلة الأولى من دمج التضليل داخل البيع السريع.
    تعرض المعاينة الحالية داخل Modal فقط، دون ربطها بسلة البيع أو تنفيذ أي خصم للمخزون.
--}}
<div x-data="{
        open: false,
        loaded: false,
        openModal() {
            this.loaded = true;
            this.open = true;
            document.documentElement.classList.add('overflow-hidden');
        },
        closeModal() {
            this.open = false;
            document.documentElement.classList.remove('overflow-hidden');
        }
    }"
    @open-tint-sale-modal.window="openModal()"
    @keydown.escape.window="if (open) closeModal()"
    x-cloak>
    <div x-show="open"
         x-transition.opacity.duration.150ms
         class="fixed inset-0 z-[100] bg-black/80 backdrop-blur-sm p-0 sm:p-3 md:p-5"
         role="dialog"
         aria-modal="true"
         aria-labelledby="quick-sale-tint-modal-title">
        <div class="mx-auto flex h-full w-full max-w-7xl flex-col overflow-hidden bg-gray-950 shadow-2xl sm:rounded-2xl sm:border sm:border-gray-700">
            <div class="flex shrink-0 items-center justify-between gap-3 border-b border-gray-800 bg-gray-900 px-3 py-3 sm:px-5">
                <div class="min-w-0">
                    <h2 id="quick-sale-tint-modal-title" class="text-base font-black text-white sm:text-lg">بيع التضليل</h2>
                    <p class="mt-0.5 text-[10px] text-gray-400 sm:text-xs">مرحلة المعاينة داخل صفحة البيع — لا يتم حفظ بيع أو خصم مخزون حتى الآن.</p>
                </div>
                <button type="button"
                        @click="closeModal()"
                        class="shrink-0 rounded-xl border border-gray-700 bg-gray-800 px-3 py-2 text-xs font-black text-white transition hover:border-red-500/60 hover:bg-red-500/10 hover:text-red-300 sm:px-4 sm:text-sm">
                    إغلاق
                </button>
            </div>

            <div class="min-h-0 flex-1 bg-gray-950">
                <template x-if="loaded">
                    <iframe
                        src="{{ route('accountant.quick-sale.tint-preview') }}"
                        title="نافذة بيع التضليل"
                        class="h-full w-full border-0 bg-gray-950"
                        loading="eager"
                        referrerpolicy="same-origin">
                    </iframe>
                </template>
            </div>
        </div>
    </div>
</div>
