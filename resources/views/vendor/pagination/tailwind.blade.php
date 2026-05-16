@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between gap-3">
        <div class="flex justify-between flex-1 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold leading-5 text-slate-400">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="relative inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold leading-5 text-slate-700 transition duration-150 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-200">
                    {!! __('pagination.previous') !!}
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="relative ml-3 inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold leading-5 text-slate-700 transition duration-150 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-200">
                    {!! __('pagination.next') !!}
                </a>
            @else
                <span class="relative ml-3 inline-flex items-center rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold leading-5 text-slate-400">
                    {!! __('pagination.next') !!}
                </span>
            @endif
        </div>

        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-medium leading-5 text-slate-500">
                    {!! __('Showing') !!}
                    @if ($paginator->firstItem())
                        <span class="font-bold text-slate-700">{{ $paginator->firstItem() }}</span>
                        {!! __('to') !!}
                        <span class="font-bold text-slate-700">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    {!! __('of') !!}
                    <span class="font-bold text-slate-700">{{ $paginator->total() }}</span>
                    {!! __('results') !!}
                </p>
            </div>

            <div>
                <span class="relative z-0 inline-flex gap-1 rounded-xl border border-slate-200 bg-white p-1 shadow-sm">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                            <span class="relative inline-flex h-8 w-8 cursor-default items-center justify-center rounded-lg text-sm font-semibold leading-5 text-slate-300" aria-hidden="true">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="relative inline-flex h-8 w-8 items-center justify-center rounded-lg text-sm font-semibold leading-5 text-slate-500 transition duration-150 hover:bg-blue-50 hover:text-blue-700 focus:z-10 focus:outline-none focus:ring-2 focus:ring-blue-200" aria-label="{{ __('pagination.previous') }}">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span aria-disabled="true">
                                <span class="relative inline-flex h-8 min-w-8 items-center justify-center rounded-lg px-2 text-xs font-semibold leading-5 text-slate-400">{{ $element }}</span>
                            </span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page">
                                        <span class="relative inline-flex h-8 min-w-8 cursor-default items-center justify-center rounded-lg bg-blue-600 px-2 text-xs font-bold leading-5 text-white">{{ $page }}</span>
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="relative inline-flex h-8 min-w-8 items-center justify-center rounded-lg px-2 text-xs font-semibold leading-5 text-slate-600 transition duration-150 hover:bg-blue-50 hover:text-blue-700 focus:z-10 focus:outline-none focus:ring-2 focus:ring-blue-200" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="relative inline-flex h-8 w-8 items-center justify-center rounded-lg text-sm font-semibold leading-5 text-slate-500 transition duration-150 hover:bg-blue-50 hover:text-blue-700 focus:z-10 focus:outline-none focus:ring-2 focus:ring-blue-200" aria-label="{{ __('pagination.next') }}">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                            <span class="relative inline-flex h-8 w-8 cursor-default items-center justify-center rounded-lg text-sm font-semibold leading-5 text-slate-300" aria-hidden="true">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
