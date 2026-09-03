@if ($paginator->hasPages())
    <style>
        .admin-pagination{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap}
        .admin-pagination-info{color:#475569;font-size:14px}
        .admin-pagination-links{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
        .admin-page-link,.admin-page-current{min-width:38px;min-height:38px;border-radius:6px;border:1px solid #dbe3ee;display:inline-flex;align-items:center;justify-content:center;padding:8px 12px;font-weight:800;text-decoration:none}
        .admin-page-link{background:#fff;color:#0f172a}
        .admin-page-link:hover{background:#eff6ff}
        .admin-page-current{background:#2563eb;color:#fff;border-color:#2563eb}
        .admin-page-disabled{color:#94a3b8;background:#f8fafc}
    </style>
    <nav class="admin-pagination" role="navigation" aria-label="Paginacion">
        <div class="admin-pagination-info">
            Mostrando {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} de {{ $paginator->total() }}
        </div>
        <div class="admin-pagination-links">
            @if ($paginator->onFirstPage())
                <span class="admin-page-link admin-page-disabled">Anterior</span>
            @else
                <a class="admin-page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">Anterior</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="admin-page-link admin-page-disabled">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="admin-page-current">{{ $page }}</span>
                        @else
                            <a class="admin-page-link" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="admin-page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Siguiente</a>
            @else
                <span class="admin-page-link admin-page-disabled">Siguiente</span>
            @endif
        </div>
    </nav>
@endif
