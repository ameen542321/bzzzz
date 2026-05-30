@if ($paginator->hasPages())
    <nav role="navigation" class="flex items-center justify-between mt-6">

        {{-- زر السابق --}}
        @if ($paginator->onFirstPage())
            <span class="px-4 py-2 text-gray-600 bg-gray-800 border border-gray-700 rounded-lg cursor-not-allowed">
                السابق
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}"
               class="px-4 py-2 bg-gray-800 border border-gray-700 text-gray-300 rounded-lg hover:bg-gray-700 transition">
                السابق
            </a>
        @endif

        {{-- أرقام الصفحات --}}
        <div class="hidden md:flex items-center gap-2">
            @foreach ($elements as $element)

                {{-- فاصل (...) --}}
                @if (is_string($element))
                    <span class="px-3 py-2 text-gray-500">{{ $element }}</span>
                @endif

                {{-- روابط الصفحات --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}"
                               class="px-4 py-2 bg-gray-800 border border-gray-700 text-gray-300 rounded-lg hover:bg-gray-700 transition">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif

            @endforeach
        </div>

        {{-- زر التالي --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}"
               class="px-4 py-2 bg-gray-800 border border-gray-700 text-gray-300 rounded-lg hover:bg-gray-700 transition">
                التالي
            </a>
        @else
            <span class="px-4 py-2 text-gray-600 bg-gray-800 border border-gray-700 rounded-lg cursor-not-allowed">
                التالي
            </span>
        @endif

    </nav>
@endif
