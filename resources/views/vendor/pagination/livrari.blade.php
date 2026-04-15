@if ($paginator->hasPages())
@php
  $c = (int) $paginator->currentPage();
  $last = (int) $paginator->lastPage();
  $pageKeys = [];
  foreach ([1, $last] as $p) {
      if ($p >= 1 && $p <= $last) {
          $pageKeys[$p] = true;
      }
  }
  for ($i = max(1, $c - 2); $i <= min($last, $c + 2); $i++) {
      $pageKeys[$i] = true;
  }
  ksort($pageKeys);
  $pageList = array_keys($pageKeys);
@endphp
<div class="livrari-pagination">
  <nav class="livrari-pag" aria-label="Paginare livrări">
    <div class="livrari-pag__row">
      @if ($paginator->onFirstPage())
        <span class="livrari-pag__edge livrari-pag__edge--disabled" aria-disabled="true">
          <i class="fas fa-chevron-left" aria-hidden="true"></i>
          <span>Pagina anterioară</span>
        </span>
      @else
        <a href="{{ $paginator->previousPageUrl() }}" class="livrari-pag__edge livrari-pag__edge--link" rel="prev">
          <i class="fas fa-chevron-left" aria-hidden="true"></i>
          <span>Pagina anterioară</span>
        </a>
      @endif

      <ol class="livrari-pag__pages" role="list">
        @foreach ($pageList as $idx => $p)
          @if ($idx > 0 && $p - $pageList[$idx - 1] > 1)
            <li class="livrari-pag__gap" aria-hidden="true"><span>…</span></li>
          @endif
          <li>
            @if ($p === $c)
              <span class="livrari-pag__num livrari-pag__num--current" aria-current="page">{{ $p }}</span>
            @else
              <a href="{{ $paginator->url($p) }}" class="livrari-pag__num livrari-pag__num--link">{{ $p }}</a>
            @endif
          </li>
        @endforeach
      </ol>

      @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" class="livrari-pag__edge livrari-pag__edge--link" rel="next">
          <span>Pagina următoare</span>
          <i class="fas fa-chevron-right" aria-hidden="true"></i>
        </a>
      @else
        <span class="livrari-pag__edge livrari-pag__edge--disabled" aria-disabled="true">
          <span>Pagina următoare</span>
          <i class="fas fa-chevron-right" aria-hidden="true"></i>
        </span>
      @endif
    </div>
    <p class="livrari-pag__meta">
      Pagina <strong>{{ $c }}</strong> din <strong>{{ $last }}</strong>
      @if ($paginator->total() > 0)
        · înregistrări <strong>{{ $paginator->firstItem() }}</strong>–<strong>{{ $paginator->lastItem() }}</strong> din <strong>{{ $paginator->total() }}</strong>
      @endif
    </p>
  </nav>
</div>
@endif
