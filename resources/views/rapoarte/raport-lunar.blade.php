@extends('layouts.app')

@section('title', 'Raport lunar call center – VOLTA')

@section('content')
<div class="rapoarte-page raport-lunar-page">
  <header class="raport-lunar-hero">
    <div class="raport-lunar-hero__badge" aria-hidden="true">
      <i class="fas fa-file-contract"></i>
    </div>
    <div class="raport-lunar-hero__text">
      <h1 class="raport-lunar-title">Raport lunar call center</h1>
      <p class="raport-lunar-intro">
        Aceeași logică ca în Excelul analizat: <strong>pondere chaturi</strong> = chaturi operator / Σ chaturi;
        <strong>pondere apeluri</strong> = apeluri / Σ apeluri;
        <strong>aport activitate</strong> (coloana „Aport în %” din TOTAL) = (chaturi + apeluri operator) / (Σ chaturi + Σ apeluri);
        <strong>aport vânzări</strong> = vânzări fără TVA operator / Σ vânzări (doar operatorii afișați).
        În raport intră <strong>doar operatorii marcați activi</strong> în pagina Operatori și prezenți în KPI 1C pentru luna aleasă.
      </p>
    </div>
  </header>

  <div class="raport-lunar-toolbar-card">
    <form class="raport-lunar-toolbar" method="get" action="{{ route('rapoarte.raport-lunar') }}">
      <div class="raport-lunar-toolbar__field">
        <label for="raportLunarMonth" class="raport-lunar-toolbar__label">
          <i class="fas fa-calendar-alt" aria-hidden="true"></i> Luna raportului
        </label>
        <input type="month" id="raportLunarMonth" name="month" value="{{ $ym }}" class="raport-lunar-month-input">
      </div>
      <div class="raport-lunar-toolbar__actions">
        <button type="submit" class="btn secondary raport-lunar-btn-icon">
          <i class="fas fa-sync" aria-hidden="true"></i><span>Actualizează</span>
        </button>
        <button type="button" id="raportLunarExportBtn" class="btn raport-lunar-btn-icon">
          <i class="fas fa-file-excel" aria-hidden="true"></i><span>Descarcă Excel</span>
        </button>
      </div>
    </form>
  </div>

  @if (session('status'))
    <div class="raport-lunar-flash raport-lunar-flash--ok" role="status">{{ session('status') }}</div>
  @endif
  @if ($errors->any())
    <div class="raport-lunar-flash raport-lunar-flash--err" role="alert">
      <ul class="raport-lunar-flash-list">
        @foreach ($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @if(!$has_sync && count($operator_rows) === 0)
    <div class="raport-lunar-empty" role="status">
      <span class="raport-lunar-empty__icon" aria-hidden="true"><i class="fas fa-database"></i></span>
      <div>
        <strong class="raport-lunar-empty__title">Fără date 1C pentru {{ $luna_label }}</strong>
        <p class="raport-lunar-empty__text">După sincronizarea KPI, reîncarcă pagina.</p>
      </div>
    </div>
  @endif

  @if($has_sync && count($operator_rows) === 0)
    <div class="raport-lunar-empty raport-lunar-empty--info" role="status">
      <span class="raport-lunar-empty__icon" aria-hidden="true"><i class="fas fa-user-slash"></i></span>
      <div>
        <strong class="raport-lunar-empty__title">Niciun operator activ cu KPI pentru {{ $luna_label }}</strong>
        <p class="raport-lunar-empty__text">Marchează operatorii ca activi în <a href="{{ route('operatori') }}">Operatori</a> și asigură-te că numele din 1C se potrivește (fără diferențe majore de ortografie).</p>
      </div>
    </div>
  @endif

  <div class="raport-lunar-stats" role="group" aria-label="Rezumat lună selectată">
    <div class="raport-lunar-stat">
      <span class="raport-lunar-stat__label">Perioadă</span>
      <span class="raport-lunar-stat__value">{{ $luna_label }}</span>
    </div>
    <div class="raport-lunar-stat">
      <span class="raport-lunar-stat__label">Plan lunar</span>
      <span class="raport-lunar-stat__value">{{ $plan_lunar !== null ? number_format($plan_lunar, 2, ',', ' ') . ' MDL' : '—' }}</span>
    </div>
    @if($has_sync)
      <div class="raport-lunar-stat raport-lunar-stat--accent">
        <span class="raport-lunar-stat__label">Vânzări fără TVA (1C)</span>
        <span class="raport-lunar-stat__value">{{ $sync_vanzari_fara_tva !== null ? number_format($sync_vanzari_fara_tva, 2, ',', ' ') . ' MDL' : '—' }}</span>
      </div>
    @endif
    @if(count($operator_rows) > 0)
      <div class="raport-lunar-stat">
        <span class="raport-lunar-stat__label">Σ Chaturi (introduse)</span>
        <span class="raport-lunar-stat__value">{{ $footer_total['chaturi'] ?? '0' }}</span>
      </div>
      <div class="raport-lunar-stat">
        <span class="raport-lunar-stat__label">Σ Apeluri (introduse)</span>
        <span class="raport-lunar-stat__value">{{ $footer_total['apeluri'] ?? '0' }}</span>
      </div>
    @endif
  </div>

  @if(count($operator_rows) > 0)
  <section class="raport-lunar-panel" aria-labelledby="raport-total-h">
    <div class="raport-lunar-panel__head">
      <span class="raport-lunar-panel__icon" aria-hidden="true"><i class="fas fa-table"></i></span>
      <h2 id="raport-total-h" class="raport-lunar-panel__title">TOTAL</h2>
      <span class="raport-lunar-panel__tag">Call center</span>
    </div>
    <div class="raport-lunar-table-shell">
      <div class="raport-lunar-table-scroll">
        <table class="raport-lunar-table">
          <thead>
            <tr>
              <th>NP</th>
              <th>Chaturi</th>
              <th>Apeluri</th>
              <th title="(chaturi+apeluri operator) / (Σ chaturi + Σ apeluri)">Aport activitate</th>
              <th class="text-right">Vânzări (fără TVA, lei)</th>
              <th title="Vânzări operator / Σ vânzări (operatori activi)">Aport vânzări</th>
              <th>Plan individual</th>
              <th>Îndeplinirea plan</th>
            </tr>
          </thead>
          <tbody>
            @foreach($operator_rows as $r)
              <tr>
                <td class="text-left raport-lunar-col-np">{{ $r['np'] }}</td>
                <td class="text-center">{{ $r['chaturi'] }}</td>
                <td class="text-center">{{ $r['apeluri'] }}</td>
                <td class="text-center raport-lunar-mono">{{ $r['aport_activitate'] !== '' ? $r['aport_activitate'] : '—' }}</td>
                <td class="text-right raport-lunar-num">{{ $r['vanzari_fara_tva'] }}</td>
                <td class="text-center raport-lunar-mono">{{ $r['aport_vanzari'] !== '' ? $r['aport_vanzari'] : '—' }}</td>
                <td class="text-center raport-lunar-col-muted">—</td>
                <td class="text-center raport-lunar-col-muted">—</td>
              </tr>
            @endforeach
            <tr class="raport-lunar-total-row">
              <td class="text-left"><strong>TOTAL</strong></td>
              <td class="text-center"><strong>{{ $footer_total['chaturi'] ?? '0' }}</strong></td>
              <td class="text-center"><strong>{{ $footer_total['apeluri'] ?? '0' }}</strong></td>
              <td class="text-center raport-lunar-col-muted">—</td>
              <td class="text-right raport-lunar-num"><strong>{{ $footer_total['vanzari_fara_tva'] !== '' ? $footer_total['vanzari_fara_tva'] : '—' }}</strong></td>
              <td class="text-center raport-lunar-col-muted">—</td>
              <td class="text-center raport-lunar-num"><strong>{{ $footer_total['plan_lunar'] !== '' ? $footer_total['plan_lunar'] : '—' }}</strong></td>
              <td class="text-center raport-lunar-num">
                @if($plan_lunar !== null && $plan_lunar > 0 && $footer_total['vanzari_fara_tva'] !== '')
                  @php $v = (float) $footer_total['vanzari_fara_tva']; @endphp
                  <strong>{{ number_format($v / $plan_lunar, 4, '.', '') }}</strong>
                @else
                  —
                @endif
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <form method="post" action="{{ route('rapoarte.raport-lunar.inputs') }}" class="raport-lunar-inputs-form">
    @csrf
    <input type="hidden" name="month" value="{{ $ym }}">
    @foreach($operator_rows as $r)
      <input type="hidden" name="operators[]" value="{{ $r['np'] }}">
    @endforeach

    <div class="raport-lunar-split">
      <section class="raport-lunar-panel raport-lunar-panel--compact" aria-labelledby="raport-chat-h">
        <div class="raport-lunar-panel__head">
          <span class="raport-lunar-panel__icon raport-lunar-panel__icon--chat" aria-hidden="true"><i class="fas fa-comments"></i></span>
          <h2 id="raport-chat-h" class="raport-lunar-panel__title">Chaturi</h2>
          <span class="raport-lunar-panel__tag raport-lunar-panel__tag--soft" title="chaturi / Σ chaturi">Pondere</span>
        </div>
        <div class="raport-lunar-table-shell">
          <div class="raport-lunar-table-scroll raport-lunar-table-scroll--narrow">
            <table class="raport-lunar-table">
              <thead>
                <tr>
                  <th>NP</th>
                  <th class="text-center">Chaturi</th>
                  <th class="text-center">Pondere</th>
                </tr>
              </thead>
              <tbody>
                @foreach($operator_rows as $r)
                  <tr>
                    <td class="text-left raport-lunar-col-np">{{ $r['np'] }}</td>
                    <td class="text-center">
                      <input type="number" name="chaturi[]" class="raport-lunar-num-input" min="0" max="99999999" step="1" value="{{ (int) $r['chaturi_int'] }}" inputmode="numeric" aria-label="Chaturi {{ $r['np'] }}">
                    </td>
                    <td class="text-center raport-lunar-mono raport-lunar-col-muted">{{ $r['pondere_chaturi'] !== '' ? $r['pondere_chaturi'] : '—' }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <section class="raport-lunar-panel raport-lunar-panel--compact" aria-labelledby="raport-apel-h">
        <div class="raport-lunar-panel__head">
          <span class="raport-lunar-panel__icon raport-lunar-panel__icon--phone" aria-hidden="true"><i class="fas fa-phone-alt"></i></span>
          <h2 id="raport-apel-h" class="raport-lunar-panel__title">Apeluri</h2>
          <span class="raport-lunar-panel__tag raport-lunar-panel__tag--soft" title="apeluri / Σ apeluri">Pondere</span>
        </div>
        <div class="raport-lunar-table-shell">
          <div class="raport-lunar-table-scroll raport-lunar-table-scroll--narrow">
            <table class="raport-lunar-table">
              <thead>
                <tr>
                  <th>NP</th>
                  <th class="text-center">Apeluri</th>
                  <th class="text-center">Pondere</th>
                </tr>
              </thead>
              <tbody>
                @foreach($operator_rows as $r)
                  <tr>
                    <td class="text-left raport-lunar-col-np">{{ $r['np'] }}</td>
                    <td class="text-center">
                      <input type="number" name="apeluri[]" class="raport-lunar-num-input" min="0" max="99999999" step="1" value="{{ (int) $r['apeluri_int'] }}" inputmode="numeric" aria-label="Apeluri {{ $r['np'] }}">
                    </td>
                    <td class="text-center raport-lunar-mono raport-lunar-col-muted">{{ $r['pondere_apeluri'] !== '' ? $r['pondere_apeluri'] : '—' }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </div>

    <div class="raport-lunar-form-actions">
      <button type="submit" class="btn raport-lunar-btn-icon">
        <i class="fas fa-save" aria-hidden="true"></i><span>Salvează chaturi și apeluri</span>
      </button>
      <p class="raport-lunar-form-hint">După salvare, procentele din TOTAL și foile Excel se recalculează automat. Valorile sunt stocate per lună și per nume operator (din 1C).</p>
    </div>
  </form>
  @endif

  @if(count($vanzari_rows) > 0)
  <section class="raport-lunar-panel" aria-labelledby="raport-vanzari-h">
    <div class="raport-lunar-panel__head">
      <span class="raport-lunar-panel__icon raport-lunar-panel__icon--money" aria-hidden="true"><i class="fas fa-coins"></i></span>
      <h2 id="raport-vanzari-h" class="raport-lunar-panel__title">Vânzări</h2>
      <span class="raport-lunar-panel__tag raport-lunar-panel__tag--soft">1C</span>
    </div>
    <div class="raport-lunar-table-shell">
      <div class="raport-lunar-table-scroll">
        <table class="raport-lunar-table">
          <thead>
            <tr>
              <th>Manager</th>
              <th class="text-right">Vânzări fără TVA</th>
              <th class="text-right">Vânzări cu TVA</th>
              <th class="text-right">Profit brut</th>
            </tr>
          </thead>
          <tbody>
            @foreach($vanzari_rows as $vr)
              <tr @class(['raport-lunar-row--callcenter' => strtoupper(trim($vr['manager'])) === 'CALL-CENTER'])>
                <td class="text-left raport-lunar-col-np">{{ $vr['manager'] }}</td>
                <td class="text-right raport-lunar-num">{{ $vr['fara_tva'] !== '' ? $vr['fara_tva'] : '—' }}</td>
                <td class="text-right raport-lunar-num">{{ $vr['cu_tva'] !== '' ? $vr['cu_tva'] : '—' }}</td>
                <td class="text-right raport-lunar-num">{{ $vr['profit'] !== '' ? $vr['profit'] : '—' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </section>
  @endif
</div>

@push('scripts')
<script>
(function () {
  var btn = document.getElementById('raportLunarExportBtn');
  if (!btn || !window.VoltaExcelExport || typeof window.VoltaExcelExport.exportSheets !== 'function') return;
  var sheets = @json($excel_sheets);
  var ym = @json($ym);
  btn.addEventListener('click', function () {
    try {
      var slug = String(ym || '').replace(/-/g, '_');
      window.VoltaExcelExport.exportSheets(sheets, 'call-center_statistica_' + slug);
    } catch (e) {
      console.error(e);
      alert('Nu s-a putut genera fișierul Excel.');
    }
  });
})();
</script>
@endpush
@endsection
