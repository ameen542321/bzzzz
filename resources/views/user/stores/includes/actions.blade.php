<div class="flex items-center gap-2">

    {{-- إذا كان المتجر محذوف (Soft Deleted) --}}
    @if($store->trashed())

        {{-- زر الاسترجاع --}}
        <form action="{{ route('user.stores.restore', $store->id) }}" method="POST">
            @csrf
            <button
                class="px-3 py-1 text-xs rounded-lg bg-green-600/20 text-green-400 border border-green-600/30 hover:bg-green-600/30 transition">
                استرجاع
            </button>
        </form>

        {{-- زر الحذف النهائي --}}
        <form action="{{ route('user.stores.forceDelete', $store->id) }}" method="POST">
            @csrf
            @method('DELETE')
            <button
                class="px-3 py-1 text-xs rounded-lg bg-red-600/20 text-red-400 border border-red-600/30 hover:bg-red-600/30 transition">
                حذف نهائي
            </button>
        </form>

    @else

        {{-- زر التفعيل والإيقاف (الجديد) --}}
        <form action="{{ route('user.stores.toggle-status', $store->id) }}" method="POST">
            @csrf
            @method('PATCH')
            <button type="submit"
                class="px-3 py-1 text-xs rounded-lg transition border {{ $store->status === 'active' 
                    ? 'bg-orange-600/20 text-orange-400 border-orange-600/30 hover:bg-orange-600/30' 
                    : 'bg-emerald-600/20 text-emerald-400 border-emerald-600/30 hover:bg-emerald-600/30' }}">
                {{ $store->status === 'active' ? 'إيقاف' : 'تفعيل' }}
            </button>
        </form>

        {{-- زر عرض المتجر --}}
        <a href="{{ route('user.stores.show', $store->id) }}"
           class="px-3 py-1 text-xs rounded-lg bg-blue-600/20 text-blue-400 border border-blue-600/30 hover:bg-blue-600/30 transition">
            عرض
        </a>

        {{-- زر تعديل المتجر --}}
        <a href="{{ route('user.stores.edit', $store->id) }}"
           class="px-3 py-1 text-xs rounded-lg bg-yellow-600/20 text-yellow-400 border border-yellow-600/30 hover:bg-yellow-600/30 transition">
            تعديل
        </a>

        {{-- زر الحذف (Soft Delete) --}}
        <form action="{{ route('user.stores.destroy', $store->id) }}" method="POST">
            @csrf
            @method('DELETE')
            <button
                class="px-3 py-1 text-xs rounded-lg bg-red-600/20 text-red-400 border border-red-600/30 hover:bg-red-600/30 transition">
                حذف
            </button>
        </form>

    @endif

</div>