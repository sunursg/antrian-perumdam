@php
  use Illuminate\Support\Facades\Storage;
  $org = $appOrganization ?? null;
  $logoUrl = ($org?->logo_path) ? Storage::disk('public')->url($org->logo_path) : asset('logo.png');
  
  $user = auth('operator')->user();
  $userData = [
      'id' => $user->id ?? null,
      'name' => $user->name ?? 'Guest',
      'email' => $user->email ?? '',
      'roles' => $user ? $user->getRoleNames() : [],
  ];
  
  $orgData = [
      'name' => $org?->name ?? 'Sistem Antrian',
      'tagline' => $org?->tagline,
  ];
@endphp
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Operator Loket</title>
  @viteReactRefresh
  @vite(['resources/css/app.css', 'resources/ts/counter.tsx'])
</head>
<body class="antialiased bg-slate-900 text-white">
  <div 
    id="counter-app"
    data-user="{{ json_encode($userData) }}"
    data-organization="{{ json_encode($orgData) }}"
    data-logo-url="{{ $logoUrl }}"
    data-api-base="/counter-api"
  ></div>
</body>
</html>
