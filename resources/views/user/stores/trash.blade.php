@extends('dashboard.app')


@section('content')

<div class="max-w-4xl mx-auto">

    {{-- العنوان --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-white">
            سلة المحذوفات
        </h1>

        {{-- زر الرجوع --}}
        <a href="{{ route('user.stores.index') }}"
           class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition">
            رجوع للمتاجر
        </a>
    </div>

    {{-- إذا لا يوجد متاجر محذوفة --}}
    @if($stores->count() === 0)
        <div class="bg-[#1b1d21] border border-[#2a2d31] rounded-xl p-6 text-center text-gray-400">
            لا يوجد متاجر محذوفة.
        </div>
    @endif

    {{-- عرض المتاجر المحذوفة --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($stores as $store)
            @include('user.stores.includes.store-card', ['store' => $store])
        @endforeach
    </div>

</div>

@endsection
