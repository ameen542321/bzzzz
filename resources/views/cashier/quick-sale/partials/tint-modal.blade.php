<div
    x-data="tintSaleModal({ productsUrl: {{ Illuminate\Support\Js::from(route('accountant.quick-sale.tint-preview-products')) }} })"
    @open-tint-sale-modal.window="openModal()"
    @keydown.escape.window="if (open) closeModal()"
    x-cloak
>
    <div
        x-show="open"
        x-transition.opacity.duration.150ms
        class="fixed inset-0 z-[100] bg-black/80 backdrop-blur-sm sm:p-3"
        role="dialog"
        aria-modal="true"
        aria-labelledby="quick-sale-tint-modal-title"
    >
        <div class="mx-auto flex h-full w-full max-w-6xl flex-col overflow-hidden bg-gray-950 shadow-2xl sm:rounded-2xl sm:border sm:border-gray-700">
            <header class="flex shrink-0 items-center justify-between gap-3 border-b border-gray-800 bg-gray-900 px-3 py-3 sm:px-5">
                <div class="min-w-0">
                    <h2 id="quick-sale-tint-modal-title" class="text-base font-black text-white sm:text-lg">بيع التضليل</h2>
                    <p class="mt-0.5 text-[10px] text-gray-400 sm:text-xs">اختر العمل والمنتج، ثم أضف العملية مباشرة إلى سلة البيع.</p>
                </div>
                <button type="button" @click="closeModal()" class="shrink-0 rounded-xl border border-gray-700 bg-gray-800 px-3 py-2 text-xs font-black text-white hover:border-red-500/60 hover:bg-red-500/10 hover:text-red-300 sm:px-4 sm:text-sm">
                    إغلاق
                </button>
            </header>

            <div class="min-h-0 flex-1 overflow-y-auto p-3 sm:p-5">
                <div x-show="loading" class="flex min-h-64 items-center justify-center rounded-2xl border border-gray-800 bg-gray-900/60 text-sm font-bold text-gray-300">
                    جارٍ تحميل منتجات التضليل...
                </div>

                <div x-show="error" class="rounded-2xl border border-red-500/40 bg-red-500/10 p-4 text-sm font-bold text-red-200">
                    <span x-text="error"></span>
                    <button type="button" @click="loadProducts()" class="mt-3 block rounded-lg bg-red-500 px-3 py-2 text-xs font-black text-white">إعادة المحاولة</button>
                </div>

                <div x-show="!loading && !error" class="space-y-4">
                    <section class="rounded-2xl border border-gray-800 bg-gray-900/80 p-3 sm:p-4">
                        <div class="mb-3 flex items-center justify-between gap-2">
                            <h3 class="text-sm font-black text-white">نوع العمل</h3>
                            <span class="group relative flex h-5 w-5 cursor-help items-center justify-center rounded-full border border-gray-600 text-[10px] text-gray-300" tabindex="0">
                                ؟
                                <span class="pointer-events-none absolute left-0 top-7 z-20 hidden w-56 rounded-lg border border-gray-700 bg-gray-950 p-2 text-[10px] leading-5 text-gray-200 shadow-xl group-hover:block group-focus:block">«كامل» يلغي الأعمال الجزئية. ويمكن الجمع بين أمامي وخلفي ودريشة في عملية واحدة.</span>
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                            <button type="button" @click="selectFullWork()" :class="fullMode ? 'border-indigo-400 bg-indigo-600 text-white' : 'border-gray-700 bg-gray-800 text-gray-200'" class="rounded-xl border px-3 py-3 text-sm font-black transition">كامل</button>
                            <button type="button" @click="toggleWork('front')" :class="isWorkSelected('front') ? 'border-indigo-400 bg-indigo-600 text-white' : 'border-gray-700 bg-gray-800 text-gray-200'" class="rounded-xl border px-3 py-3 text-sm font-black transition">أمامي</button>
                            <button type="button" @click="toggleWork('rear')" :class="isWorkSelected('rear') ? 'border-indigo-400 bg-indigo-600 text-white' : 'border-gray-700 bg-gray-800 text-gray-200'" class="rounded-xl border px-3 py-3 text-sm font-black transition">خلفي</button>
                            <button type="button" @click="toggleWork('window')" :class="isWorkSelected('window') ? 'border-indigo-400 bg-indigo-600 text-white' : 'border-gray-700 bg-gray-800 text-gray-200'" class="rounded-xl border px-3 py-3 text-sm font-black transition">دريشة</button>
                        </div>
                        <div x-show="isWorkSelected('window')" class="mt-3 flex items-center gap-2 rounded-xl border border-gray-800 bg-gray-950/70 p-2">
                            <span class="ml-auto text-xs font-bold text-gray-300">عدد الدرايش</span>
                            <template x-for="count in [1, 2, 3, 4]" :key="count">
                                <button type="button" @click="windowCount = count; syncPrice()" :class="windowCount === count ? 'bg-cyan-500 text-gray-950' : 'bg-gray-800 text-gray-200'" class="h-9 w-9 rounded-lg text-xs font-black" x-text="count"></button>
                            </template>
                        </div>
                    </section>

                    <section x-show="fullMode" class="space-y-3">
                        <template x-for="component in fullComponents" :key="component.id">
                            <div class="rounded-2xl border border-indigo-500/25 bg-gray-900/80 p-3 sm:p-4">
                                <div class="mb-3 flex items-center justify-between gap-2">
                                    <div>
                                        <h3 class="text-sm font-black text-white" x-text="component.label"></h3>
                                        <p class="mt-0.5 text-[10px] text-gray-400" x-text="component.hint"></p>
                                    </div>
                                    <span class="rounded-full bg-indigo-500/10 px-2 py-1 text-[10px] font-bold text-indigo-300" x-text="component.quantity > 1 ? ('× ' + component.quantity) : 'قطعة واحدة'"></span>
                                </div>
                                <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                                    <label class="space-y-1">
                                        <span class="text-[10px] font-bold text-gray-400">نوع التضليل</span>
                                        <select x-model="fullSelections[component.id].type" @change="resetDependentSelection(fullSelections[component.id]); syncPrice()" class="w-full rounded-xl border border-gray-700 bg-gray-950 px-3 py-3 text-sm font-bold text-white">
                                            <option value="">اختر النوع</option>
                                            <template x-for="type in availableTypesForWork(component.work)" :key="type.id"><option :value="type.id" x-text="type.label"></option></template>
                                        </select>
                                    </label>
                                    <label class="space-y-1">
                                        <span class="text-[10px] font-bold text-gray-400">الحجم</span>
                                        <select x-model="fullSelections[component.id].size" @change="fullSelections[component.id].grade = ''; syncPrice()" :disabled="!fullSelections[component.id].type" class="w-full rounded-xl border border-gray-700 bg-gray-950 px-3 py-3 text-sm font-bold text-white disabled:opacity-40">
                                            <option value="">اختر الحجم</option>
                                            <template x-for="size in sizesFor(component.work, fullSelections[component.id].type)" :key="size.id"><option :value="size.id" x-text="size.label"></option></template>
                                        </select>
                                    </label>
                                    <label class="space-y-1">
                                        <span class="text-[10px] font-bold text-gray-400">الدرجة</span>
                                        <select x-model="fullSelections[component.id].grade" @change="syncPrice()" :disabled="!fullSelections[component.id].size" class="w-full rounded-xl border border-gray-700 bg-gray-950 px-3 py-3 text-sm font-bold text-white disabled:opacity-40">
                                            <option value="">اختر الدرجة</option>
                                            <template x-for="grade in gradesFor(component.work, fullSelections[component.id].type, fullSelections[component.id].size)" :key="grade"><option :value="grade" x-text="grade"></option></template>
                                        </select>
                                    </label>
                                </div>
                            </div>
                        </template>
                    </section>

                    <section x-show="!fullMode && selectedWorks.length" class="space-y-3">
                        <template x-for="work in selectedWorks" :key="work">
                            <div class="rounded-2xl border border-gray-800 bg-gray-900/80 p-3 sm:p-4">
                                <div class="mb-3 flex items-center justify-between gap-2">
                                    <h3 class="text-sm font-black text-white" x-text="workLabel(work) + (work === 'window' ? ' × ' + windowCount : '')"></h3>
                                    <button type="button" @click="toggleWork(work)" class="rounded-lg border border-red-500/30 bg-red-500/10 px-2 py-1 text-[10px] font-bold text-red-300">إلغاء</button>
                                </div>
                                <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                                    <label class="space-y-1">
                                        <span class="text-[10px] font-bold text-gray-400">نوع التضليل</span>
                                        <select x-model="workSelections[work].type" @change="resetDependentSelection(workSelections[work]); syncPrice()" class="w-full rounded-xl border border-gray-700 bg-gray-950 px-3 py-3 text-sm font-bold text-white">
                                            <option value="">اختر النوع</option>
                                            <template x-for="type in availableTypesForWork(work)" :key="type.id"><option :value="type.id" x-text="type.label"></option></template>
                                        </select>
                                    </label>
                                    <label class="space-y-1">
                                        <span class="text-[10px] font-bold text-gray-400">الحجم</span>
                                        <select x-model="workSelections[work].size" @change="workSelections[work].grade = ''; syncPrice()" :disabled="!workSelections[work].type" class="w-full rounded-xl border border-gray-700 bg-gray-950 px-3 py-3 text-sm font-bold text-white disabled:opacity-40">
                                            <option value="">اختر الحجم</option>
                                            <template x-for="size in sizesFor(work, workSelections[work].type)" :key="size.id"><option :value="size.id" x-text="size.label"></option></template>
                                        </select>
                                    </label>
                                    <label class="space-y-1">
                                        <span class="text-[10px] font-bold text-gray-400">الدرجة</span>
                                        <select x-model="workSelections[work].grade" @change="syncPrice()" :disabled="!workSelections[work].size" class="w-full rounded-xl border border-gray-700 bg-gray-950 px-3 py-3 text-sm font-bold text-white disabled:opacity-40">
                                            <option value="">اختر الدرجة</option>
                                            <template x-for="grade in gradesFor(work, workSelections[work].type, workSelections[work].size)" :key="grade"><option :value="grade" x-text="grade"></option></template>
                                        </select>
                                    </label>
                                </div>
                            </div>
                        </template>
                    </section>

                    <section class="rounded-2xl border border-gray-800 bg-gray-900/80 p-3 sm:p-4">
                        <button type="button" @click="toggleCustomPanel()" class="flex w-full items-center justify-between gap-3 text-right">
                            <span>
                                <strong class="block text-sm text-white">إضافة مخصصة</strong>
                                <small class="text-[10px] text-gray-400">لأي عمل غير موجود ضمن الخيارات القياسية.</small>
                            </span>
                            <span class="text-lg font-black text-indigo-300" x-text="customOpen ? '−' : '+'"></span>
                        </button>
                        <div x-show="customOpen" class="mt-3 space-y-3">
                            <template x-for="row in customRows" :key="row.id">
                                <div class="grid grid-cols-1 gap-2 rounded-xl border border-gray-800 bg-gray-950/70 p-3 sm:grid-cols-2 lg:grid-cols-5">
                                    <select x-model="row.productId" class="rounded-lg border border-gray-700 bg-gray-900 px-2 py-2 text-xs font-bold text-white lg:col-span-2">
                                        <option value="">اختر منتج الرول</option>
                                        <template x-for="product in products" :key="product.id"><option :value="product.id" x-text="product.name"></option></template>
                                    </select>
                                    <input type="text" x-model="row.name" placeholder="وصف العمل" class="rounded-lg border border-gray-700 bg-gray-900 px-2 py-2 text-xs font-bold text-white">
                                    <input type="number" min="0.01" step="0.01" x-model.number="row.meters" placeholder="الأمتار" class="rounded-lg border border-gray-700 bg-gray-900 px-2 py-2 text-xs font-bold text-white">
                                    <div class="flex gap-2">
                                        <input type="number" min="0.01" step="0.01" x-model.number="row.price" @input="syncPrice()" placeholder="السعر" class="min-w-0 flex-1 rounded-lg border border-gray-700 bg-gray-900 px-2 py-2 text-xs font-bold text-white">
                                        <button type="button" @click="removeCustomRow(row.id)" class="rounded-lg bg-red-500/10 px-3 text-xs font-black text-red-300">حذف</button>
                                    </div>
                                </div>
                            </template>
                            <button type="button" @click="addCustomRow()" class="rounded-xl border border-dashed border-indigo-500/50 px-3 py-2 text-xs font-black text-indigo-300">+ إضافة سطر آخر</button>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-gray-800 bg-gray-900/80 p-3 sm:p-4">
                        <div class="mb-3 flex items-center justify-between gap-2">
                            <h3 class="text-sm font-black text-white">ملخص العملية</h3>
                            <span class="text-[10px] font-bold text-gray-400" x-text="resolvedParts.length ? resolvedParts.length + ' مكون' : 'لم يكتمل أي اختيار'"></span>
                        </div>
                        <div class="space-y-2">
                            <template x-for="part in resolvedParts" :key="part.key">
                                <div class="flex items-start justify-between gap-3 rounded-xl border border-gray-800 bg-gray-950/70 p-3">
                                    <div class="min-w-0">
                                        <strong class="block text-xs text-white" x-text="part.label"></strong>
                                        <span class="mt-1 block truncate text-[10px] text-gray-400" x-text="part.product.name"></span>
                                    </div>
                                    <span class="shrink-0 text-xs font-black text-green-400" x-text="money(part.linePrice)"></span>
                                </div>
                            </template>
                            <div x-show="!resolvedParts.length" class="rounded-xl border border-dashed border-gray-700 px-3 py-6 text-center text-xs text-gray-500">حدد العمل والمنتجات لإظهار الملخص.</div>
                        </div>
                        <label class="mt-4 block space-y-1">
                            <span class="text-[10px] font-bold text-gray-400">سعر العملية النهائي</span>
                            <input type="number" min="0.01" step="0.01" x-model.number="finalPrice" class="w-full rounded-xl border border-green-500/40 bg-gray-950 px-4 py-3 text-center text-xl font-black text-green-400 outline-none focus:border-green-400">
                        </label>
                    </section>
                </div>
            </div>

            <footer class="flex shrink-0 gap-2 border-t border-gray-800 bg-gray-900 p-3 sm:justify-end sm:px-5">
                <button type="button" @click="resetBuilder()" class="flex-1 rounded-xl border border-gray-700 bg-gray-800 px-4 py-3 text-xs font-black text-gray-200 sm:flex-none">بدء جديد</button>
                <button type="button" @click="addToQuickSaleCart()" :disabled="loading || !resolvedParts.length" class="flex-[2] rounded-xl bg-green-600 px-4 py-3 text-sm font-black text-white disabled:cursor-not-allowed disabled:opacity-40 sm:flex-none sm:min-w-48">إضافة إلى السلة</button>
            </footer>
        </div>
    </div>
</div>

@once
<script>
function tintSaleModal(config) {
    return {
        open: false,
        loading: false,
        error: '',
        products: [],
        types: [],
        sizes: [],
        fullMode: false,
        selectedWorks: [],
        workSelections: {},
        fullSelections: { front: { type: '', size: '', grade: '' }, rear: { type: '', size: '', grade: '' }, windows: { type: '', size: '', grade: '' } },
        windowCount: 1,
        customOpen: false,
        customRows: [],
        customSequence: 0,
        finalPrice: 0,
        fullComponents: [
            { id: 'front', work: 'front', label: 'الأمامي', hint: 'المقاس المعتاد: كبير.', quantity: 1 },
            { id: 'rear', work: 'rear', label: 'الخلفي', hint: 'المقاس المعتاد: كبير.', quantity: 1 },
            { id: 'windows', work: 'window', label: 'الجوانب والدرايش', hint: 'المقاس المعتاد: صغير.', quantity: 4 },
        ],

        async openModal() {
            this.open = true;
            document.documentElement.classList.add('overflow-hidden');
            if (!this.products.length) await this.loadProducts();
        },
        closeModal() {
            this.open = false;
            document.documentElement.classList.remove('overflow-hidden');
        },
        async loadProducts() {
            this.loading = true;
            this.error = '';
            try {
                const response = await fetch(config.productsUrl, { headers: { Accept: 'application/json' }, cache: 'no-store' });
                if (!response.ok) throw new Error(response.status === 401 ? 'انتهت جلسة المحاسب. أعد تسجيل الدخول.' : `تعذر تحميل منتجات التضليل (${response.status}).`);
                const payload = await response.json();
                this.products = (payload.products || []).map(product => this.mapProduct(product));
                this.rebuildFilters();
                if (!this.products.length) this.error = 'لا توجد منتجات رول نشطة داخل قسم تضليل.';
            } catch (error) {
                this.error = error.message || 'تعذر تحميل بيانات التضليل.';
            } finally {
                this.loading = false;
            }
        },
        normalize(value) {
            return String(value || '').trim().toLowerCase().replace(/[أإآ]/g, 'ا').replace(/ى/g, 'ي').replace(/ة/g, 'ه').replace(/\s+/g, ' ');
        },
        slug(value) { return this.normalize(value).replace(/\s+/g, '-'); },
        parseIdentity(name) {
            const tokens = String(name || '').trim().replace(/\s+/g, ' ').split(' ').filter(Boolean);
            if (tokens.length < 3) return { type: '', typeId: '', size: '', sizeLabel: '', grade: '' };
            let sizeIndex = tokens.findIndex(token => ['كبير', 'صغير'].includes(token));
            let gradeIndex = tokens.findIndex(token => token === 'شفاف' || /^0?[1-9]\d*$/.test(token));
            if (sizeIndex < 0 || gradeIndex < 0) { sizeIndex = 1; gradeIndex = 2; }
            const type = tokens.slice(0, sizeIndex).join(' ') || tokens[0];
            const sizeLabel = tokens[sizeIndex] || '';
            return { type, typeId: this.slug(type), size: this.slug(sizeLabel), sizeLabel, grade: tokens[gradeIndex] || '' };
        },
        inferWork(label) {
            const value = this.normalize(label);
            if (value.includes('امامي')) return 'front';
            if (value.includes('خلفي')) return 'rear';
            if (value.includes('دريش')) return 'window';
            if (value.includes('كامل')) return 'full';
            return '';
        },
        mapProduct(product) {
            const identity = this.parseIdentity(product.name);
            return {
                id: String(product.id), name: product.name, type: identity.type, typeId: identity.typeId,
                size: identity.size, sizeLabel: identity.sizeLabel, grade: identity.grade,
                price: Number(product.price || 0), stock: Number(product.quantity || 0), waste: Number(product.waste_percentage || 0),
                fractions: (product.fractions || []).map(fraction => ({
                    id: String(fraction.id), label: fraction.option_label,
                    work: this.inferWork(fraction.option_label), meters: Number(fraction.deduction_value || 0), price: Number(fraction.price || 0),
                })),
            };
        },
        rebuildFilters() {
            const typeMap = new Map();
            const sizeMap = new Map();
            this.products.forEach(product => {
                if (product.typeId && !typeMap.has(product.typeId)) typeMap.set(product.typeId, { id: product.typeId, label: product.type });
                if (product.size && !sizeMap.has(product.size)) sizeMap.set(product.size, { id: product.size, label: product.sizeLabel });
            });
            this.types = [...typeMap.values()];
            this.sizes = [...sizeMap.values()];
        },
        workLabel(work) { return ({ front: 'أمامي', rear: 'خلفي', window: 'دريشة' })[work] || work; },
        emptySelection() { return { type: '', size: '', grade: '' }; },
        resetDependentSelection(selection) { selection.size = ''; selection.grade = ''; },
        isWorkSelected(work) { return this.selectedWorks.includes(work); },
        selectFullWork() {
            this.fullMode = true;
            this.selectedWorks = [];
            this.workSelections = {};
            this.fullSelections = { front: this.emptySelection(), rear: this.emptySelection(), windows: this.emptySelection() };
            this.syncPrice();
        },
        toggleWork(work) {
            this.fullMode = false;
            this.fullSelections = { front: this.emptySelection(), rear: this.emptySelection(), windows: this.emptySelection() };
            if (this.selectedWorks.includes(work)) {
                this.selectedWorks = this.selectedWorks.filter(item => item !== work);
                delete this.workSelections[work];
            } else {
                this.selectedWorks.push(work);
                this.workSelections[work] = this.emptySelection();
            }
            this.syncPrice();
        },
        productsFor(work, typeId = '', size = '', grade = '') {
            return this.products.filter(product =>
                (!typeId || product.typeId === typeId) && (!size || product.size === size) && (!grade || product.grade === grade)
                && product.fractions.some(fraction => fraction.work === work)
            );
        },
        availableTypesForWork(work) {
            const ids = new Set(this.productsFor(work).map(product => product.typeId));
            return this.types.filter(type => ids.has(type.id));
        },
        sizesFor(work, typeId) {
            const ids = new Set(this.productsFor(work, typeId).map(product => product.size));
            return this.sizes.filter(size => ids.has(size.id));
        },
        gradesFor(work, typeId, size) {
            return [...new Set(this.productsFor(work, typeId, size).map(product => product.grade).filter(Boolean))];
        },
        resolvePart(work, selection, quantity, label, owner) {
            if (!selection?.type || !selection?.size || !selection?.grade) return null;
            const product = this.productsFor(work, selection.type, selection.size, selection.grade)[0];
            const fraction = product?.fractions.find(item => item.work === work);
            if (!product || !fraction) return null;
            return { key: `${owner}-${work}`, owner, work, label, quantity, product, fraction, unitPrice: fraction.price, linePrice: fraction.price * quantity };
        },
        get resolvedParts() {
            const parts = [];
            if (this.fullMode) {
                this.fullComponents.forEach(component => {
                    const part = this.resolvePart(component.work, this.fullSelections[component.id], component.quantity, component.label + (component.quantity > 1 ? ` × ${component.quantity}` : ''), `full-${component.id}`);
                    if (part) parts.push(part);
                });
            } else {
                this.selectedWorks.forEach(work => {
                    const quantity = work === 'window' ? this.windowCount : 1;
                    const part = this.resolvePart(work, this.workSelections[work], quantity, this.workLabel(work) + (quantity > 1 ? ` × ${quantity}` : ''), `work-${work}`);
                    if (part) parts.push(part);
                });
            }
            this.customRows.forEach(row => {
                const product = this.products.find(item => item.id === String(row.productId));
                if (product && row.name && Number(row.meters) > 0 && Number(row.price) > 0) {
                    parts.push({ key: `custom-${row.id}`, owner: 'custom', work: 'custom', label: row.name, quantity: 1, product, fraction: null, customMeters: Number(row.meters), unitPrice: Number(row.price), linePrice: Number(row.price) });
                }
            });
            return parts;
        },
        recordedPrice() { return this.resolvedParts.reduce((sum, part) => sum + part.linePrice, 0); },
        syncPrice() { this.$nextTick(() => { this.finalPrice = Number(this.recordedPrice().toFixed(2)); }); },
        toggleCustomPanel() { this.customOpen = !this.customOpen; if (this.customOpen && !this.customRows.length) this.addCustomRow(); },
        addCustomRow() { this.customRows.push({ id: ++this.customSequence, productId: '', name: '', meters: '', price: '' }); },
        removeCustomRow(id) { this.customRows = this.customRows.filter(row => row.id !== id); this.syncPrice(); },
        resetBuilder() {
            this.fullMode = false; this.selectedWorks = []; this.workSelections = {}; this.fullSelections = { front: this.emptySelection(), rear: this.emptySelection(), windows: this.emptySelection() };
            this.windowCount = 1; this.customOpen = false; this.customRows = []; this.finalPrice = 0;
        },
        money(value) { return Number(value || 0).toFixed(2) + ' ر.س'; },
        distributeFinalPrice(parts) {
            const target = Number(this.finalPrice || 0);
            const recorded = parts.reduce((sum, part) => sum + part.unitPrice, 0);
            let remaining = target;
            return parts.map((part, index) => {
                const price = index === parts.length - 1
                    ? Number(remaining.toFixed(2))
                    : Number((recorded > 0 ? target * (part.unitPrice / recorded) : target / parts.length).toFixed(2));
                remaining -= price;
                return { ...part, distributedPrice: price };
            });
        },
        buildCartItems() {
            const expanded = [];
            this.resolvedParts.forEach(part => {
                const count = part.work === 'custom' ? 1 : part.quantity;
                for (let index = 0; index < count; index++) expanded.push({ ...part, unitPrice: part.work === 'custom' ? part.unitPrice : part.fraction.price, componentIndex: index + 1 });
            });
            const pricedParts = this.distributeFinalPrice(expanded);
            const groupId = `tint-${Date.now()}-${Math.random().toString(16).slice(2)}`;
            const groupLabel = [...new Set(this.resolvedParts.map(part => part.product.type).filter(Boolean))].join(' / ') || 'تضليل';
            return pricedParts.map((part, index) => ({
                temp_id: `${groupId}-${index}`,
                product_id: Number(part.product.id),
                name: part.product.name,
                is_fractional: true,
                is_splittable: false,
                items_per_unit: 1,
                piece_price: 0,
                sale_unit: 'unit',
                base_price: Number(part.product.price || 0),
                price: part.distributedPrice,
                quantity: 1,
                total: part.distributedPrice,
                fraction_id: part.work === 'custom' ? 'custom' : part.fraction.id,
                is_custom: part.work === 'custom',
                custom_name: part.work === 'custom' ? part.label : '',
                custom_consumption: part.work === 'custom' ? part.customMeters : '',
                available_fractions: part.product.fractions.map(fraction => ({ id: fraction.id, option_label: fraction.label, deduction_value: fraction.meters, price: fraction.price })),
                tint_group_id: groupId,
                tint_group_label: `تضليل ${groupLabel}`,
                tint_component_label: part.label + (part.quantity > 1 ? ` (${part.componentIndex}/${part.quantity})` : ''),
            }));
        },
        stockErrors(parts) {
            const requiredByProduct = new Map();
            parts.forEach(part => {
                const baseMeters = part.work === 'custom'
                    ? Number(part.customMeters || 0)
                    : Number(part.fraction?.meters || 0) * Number(part.quantity || 1);
                const requiredMeters = baseMeters * (1 + Number(part.product.waste || 0) / 100);
                requiredByProduct.set(part.product.id, (requiredByProduct.get(part.product.id) || 0) + requiredMeters);
            });
            return [...requiredByProduct.entries()].flatMap(([productId, required]) => {
                const product = this.products.find(item => item.id === productId);
                return product && required > product.stock + 0.0001
                    ? [`${product.name}: المطلوب ${required.toFixed(2)}م والمتوفر ${product.stock.toFixed(2)}م.`]
                    : [];
            });
        },
        addToQuickSaleCart() {
            const parts = this.resolvedParts;
            const expectedCount = this.fullMode ? this.fullComponents.length : this.selectedWorks.length;
            const standardCount = parts.filter(part => part.work !== 'custom').length;
            if (!parts.length) return Swal.fire({ title: 'تنبيه', text: 'أكمل اختيار عمل واحد على الأقل.', icon: 'warning' });
            if (standardCount < expectedCount) return Swal.fire({ title: 'تنبيه', text: 'أكمل نوع التضليل والحجم والدرجة لجميع الأعمال المحددة.', icon: 'warning' });
            if (Number(this.finalPrice || 0) <= 0) return Swal.fire({ title: 'تنبيه', text: 'سعر العملية النهائي يجب أن يكون أكبر من صفر.', icon: 'warning' });
            const stockErrors = this.stockErrors(parts);
            if (stockErrors.length) return Swal.fire({ title: 'المخزون غير كافٍ', html: stockErrors.join('<br>'), icon: 'error' });
            const items = this.buildCartItems();
            this.$dispatch('tint-items-ready', { items, groupId: items[0]?.tint_group_id });
            this.closeModal();
            this.resetBuilder();
        },
    };
}
</script>
@endonce
