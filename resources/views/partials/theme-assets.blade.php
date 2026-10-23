@php
  $recolored = in_array($uiTheme ?? null, ['neon-glass', 'dark-red'], true);
  $themeAssets = array_values(array_filter([
    $recolored ? 'css/theme-recolor-'.$uiTheme.'.css' : null,
    'css/theme-glass.css',
    ($uiTheme ?? null) === 'neon-glass' ? 'css/theme-neon-glass.css' : null,
  ]));
@endphp
@foreach($themeAssets as $themeCss)
  <link rel="stylesheet" href="{{ asset($themeCss) }}?v={{ @filemtime(public_path($themeCss)) ?: 0 }}">
@endforeach
@if($recolored)
  <script src="{{ asset('js/theme-recolor.js') }}?v={{ @filemtime(public_path('js/theme-recolor.js')) ?: 0 }}"></script>
@endif
