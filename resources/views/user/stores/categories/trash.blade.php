@extends('dashboard.app')

@section('title', 'سلة المحذوفات – متجر ' . $store->name)

@section('content')

<div class="max-w-7xl mx-auto py-10">

    {{-- الهيدر --}}
    <div class="flex items-center justify-between mb-10">

        {{-- زر الرجوع --}}
        <a href="{{ route('user.stores.categories.index', $store->id) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-800 border border-gray-700 text-gray-300 hover:bg-gray-700 hover:text-white transition shadow-sm">
            <i class="fa-solid fa-arrow-right text-sm"></i>
            <span class="text-sm font-medium">رجوع</span>
        </a>

        {{-- عنوان الصفحة --}}
        <h1 class="text-2xl font-bold text-white">
            سلة المحذوفات
        </h1>

        {{-- مساحة فارغة --}}
        <div class="w-20"></div>

    </div>

    {{-- في حال لا توجد عناصر محذوفة --}}
    @if($categories->isEmpty())
        <div class="bg-gray-900 border border-gray-800 p-10 rounded-xl text-center shadow-lg">
            <i class="fa-solid fa-trash-can text-gray-500 text-6xl mb-4"></i>
            <p class="text-gray-400 text-lg">لا توجد عناصر في سلة المحذوفات</p>
        </div>

    @else

        {{-- قائمة العناصر المحذوفة --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            @foreach($categories as $category)
                <div class="bg-gray-900 border border-gray-800 p-6 rounded-xl hover:bg-gray-800 transition duration-200 shadow-sm">

                    {{-- الاسم --}}
                    <h2 class="text-xl font-bold text-white mb-3 truncate">
                        {{ $category->name }}
                    </h2>

                    {{-- الوصف --}}
                    <p class="text-gray-400 text-sm mb-4 line-clamp-2">
                        {{ $category->description ?: 'لا يوجد وصف' }}
                    </p>

                    {{-- تاريخ الحذف --}}
                    <p class="text-gray-500 text-xs mb-6">
                        <i class="fa-solid fa-clock ml-1"></i>
                        تم الحذف في: {{ $category->deleted_at->format('Y-m-d H:i') }}
                    </p>

                    {{-- الأزرار --}}
                    <div class="flex items-center justify-between">

                        {{-- زر الاسترجاع --}}
                        <form action="{{ route('user.stores.categories.restore', [$store->id, $category->id]) }}"
      method="POST">
    @csrf
    @method('PUT')

                            <button class="flex items-center text-green-400 hover:text-green-300 text-sm transition">
                                <i class="fa-solid fa-rotate-left ml-1"></i>
                                استرجاع
                            </button>
                        </form>

                        {{-- زر الحذف النهائي --}}
                        <form action="{{ route('user.stores.categories.force-delete', [$store->id, $category->id]) }}"
                              method="POST"
                              onsubmit="return confirm('هل أنت متأكد من حذف هذا القسم نهائيًا؟ لا يمكن التراجع.');">
                            @csrf
                            @method('DELETE')

                            <button class="flex items-center text-red-400 hover:text-red-300 text-sm transition">
                                <i class="fa-solid fa-trash ml-1"></i>
                                حذف نهائي
                            </button>
                        </form>

                    </div>

                </div>
            @endforeach

        </div>

    @endif

</div>

@endsection
