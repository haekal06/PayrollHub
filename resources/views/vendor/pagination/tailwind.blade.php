@if ($paginator->hasPages())
<nav
    class="pagination-nav"
    role="navigation"
    aria-label="Navigasi halaman">

    <div class="pagination-info">
        Menampilkan
        <strong>{{ $paginator->firstItem() }}</strong>
        sampai
        <strong>{{ $paginator->lastItem() }}</strong>
        dari
        <strong>{{ $paginator->total() }}</strong>
        data
    </div>

    <div class="pagination-buttons">
        @if ($paginator->onFirstPage())
        <span
            class="
                    pagination-button
                    pagination-disabled
                ">
            Sebelumnya
        </span>
        @else
        <a
            href="{{ $paginator->previousPageUrl() }}"
            class="pagination-button"
            rel="prev">
            Sebelumnya
        </a>
        @endif

        @foreach ($elements as $element)
        @if (is_string($element))
        <span
            class="
                        pagination-button
                        pagination-disabled
                    ">
            {{ $element }}
        </span>
        @endif

        @if (is_array($element))
        @foreach ($element as $page => $url)
        @if ($page === $paginator->currentPage())
        <span
            class="
                                pagination-button
                                pagination-active
                            "
            aria-current="page">
            {{ $page }}
        </span>
        @else
        <a
            href="{{ $url }}"
            class="pagination-button">
            {{ $page }}
        </a>
        @endif
        @endforeach
        @endif
        @endforeach

        @if ($paginator->hasMorePages())
        <a
            href="{{ $paginator->nextPageUrl() }}"
            class="pagination-button"
            rel="next">
            Berikutnya
        </a>
        @else
        <span
            class="
                    pagination-button
                    pagination-disabled
                ">
            Berikutnya
        </span>
        @endif
    </div>
</nav>
@endif