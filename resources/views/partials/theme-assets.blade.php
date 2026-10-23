@if(($uiTheme ?? null) === 'neon-glass')
  @foreach(['css/theme-neon-glass-recolor.css', 'css/theme-neon-glass.css'] as $themeCss)
    <link rel="stylesheet" href="{{ asset($themeCss) }}?v={{ @filemtime(public_path($themeCss)) ?: 0 }}">
  @endforeach
  <script src="{{ asset('js/theme-neon-glass.js') }}?v={{ @filemtime(public_path('js/theme-neon-glass.js')) ?: 0 }}"></script>
@endif
