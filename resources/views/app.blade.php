<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Perumdam Tirta Perwira | Sistem Antrian')</title>
  @hasSection('content')
    @vite(['resources/css/app.css'])
  @else
    @viteReactRefresh
    @vite(['resources/ts/app.tsx'])
  @endif
</head>
<body class="@yield('bodyClass', 'bg-slate-50 text-slate-900') antialiased">
  @hasSection('content')
    @yield('content')
  @else
    <div id="app"></div>
  @endif
</body>
</html>
