@extends('dashboard.app')

@section('title', 'سلة محذوفات المنتجات – متجر ' . $store->name)

@section('content')

<div class="max-w-6xl mx-auto py-10">

    {{-- الهيدر --}}
    <div class="flex items-center justify-between mb-8">

        {{-- زر الرجوع إلى الكتالوج --}}
        <a href="{{ route('user.stores.products.index', $store->id) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-800 border border-gray-700 text-gray-300 hover:bg-gray-700 hover:text-white transition shadow-sm">
            <i class="fa-solid fa-arrow-right text-sm"></i>
            <span class="text-sm font-medium">رجوع إلى المنتجات</span>
        </a>

        {{-- عنوان الصفحة --}}
        <h1 class="text-2xl font-bold text-white">
            سلة محذوفات المنتجات
        </h1>

        <div class="w-32"></div>

    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">

        <table class="w-full text-white">
            <thead class="bg-gray-800">
                <tr class="text-gray-300">
                    <th class="py-3 px-4 text-right">المنتج</th>
                    <th class="py-3 px-4 text-right">القسم</th>
                    <th class="py-3 px-4 text-right">تاريخ الحذف</th>
                    <th class="py-3 px-4 text-right">العمليات</th>
                </tr>
            </thead>

            <tbody>
                @forelse($products as $product)
                    <tr class="border-b border-gray-800 hover:bg-gray-800 transition">

                        <td class="py-3 px-4">{{ $product->name }}</td>

                        <td class="py-3 px-4">
                            {{ $product->category->name ?? '—' }}
                        </td>

                        <td class="py-3 px-4">
                            {{ $product->deleted_at?->format('Y-m-d H:i') }}
                        </td>

                        <td class="py-3 px-4 flex items-center gap-4">

                            {{-- استرجاع --}}
                            <form action="{{ route('user.stores.products.restore', [$store->id, $product->id]) }}"
                                  method="POST">
                                @csrf
                                @method('PUT')
                                <button class="inline-flex items-center gap-1 text-green-400 hover:text-green-300 text-sm">
                                    <i class="fa-solid fa-rotate-left"></i>
                                    <span>استرجاع</span>
                                </button>
                            </form>

                            {{-- حذف نهائي --}}
                            <form action="{{ route('user.stores.products.force-delete', [$store->id, $product->id]) }}"
                                  method="POST"
                                  onsubmit="return confirm('هل أنت متأكد من الحذف النهائي؟');">
                                @csrf
                                @method('DELETE')
                                <button class="inline-flex items-center gap-1 text-red-400 hover:text-red-300 text-sm">
                                    <i class="fa-solid fa-trash"></i>
                                    <span>حذف نهائي</span>
                                </button>
                            </form>

                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-6 text-gray-400">
                            لا توجد منتجات محذوفة
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>

    </div>

</div>

@endsection
