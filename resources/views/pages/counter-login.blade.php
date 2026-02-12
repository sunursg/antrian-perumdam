@php
  use Illuminate\Support\Facades\Storage;
  $org = $appOrganization ?? null;
  $logoUrl = $org?->logo_path ? Storage::disk('public')->url($org->logo_path) : asset('logo.png');
@endphp
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Login Operator Loket</title>
  @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-900 via-violet-800 to-blue-900 text-white antialiased">
  <main class="min-h-screen px-6 py-10 flex items-center justify-center">
    <section class="w-full max-w-md rounded-3xl border border-white/20 bg-white/10 backdrop-blur-xl shadow-2xl p-7">
      <div class="flex items-center gap-3">
        <div class="h-14 w-14 rounded-xl bg-white p-2 shadow">
          <img src="{{ $logoUrl }}" alt="Logo" class="h-full w-full object-contain">
        </div>
        <div>
          <p class="text-xs uppercase tracking-[0.2em] text-white/70">Operator Loket</p>
          <h1 class="text-xl font-bold">{{ $org?->name ?? 'Sistem Antrian' }}</h1>
        </div>
      </div>

      <p class="mt-6 text-sm text-white/80">Masuk menggunakan email dan password operator yang dibuat oleh superadmin.</p>

      <form action="{{ route('counter.login.submit') }}" method="POST" class="mt-6 space-y-4">
        @csrf
        <div>
          <label for="email" class="block text-sm mb-1 text-white/90">Email</label>
          <input id="email" name="email" type="email" value="{{ old('email') }}" required class="w-full rounded-xl border border-white/25 bg-white/95 px-4 py-3 text-slate-900 focus:border-blue-500 focus:outline-none">
          @error('email')
            <p class="mt-1 text-xs text-rose-200">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label for="password" class="block text-sm mb-1 text-white/90">Password</label>
          <input id="password" name="password" type="password" required class="w-full rounded-xl border border-white/25 bg-white/95 px-4 py-3 text-slate-900 focus:border-blue-500 focus:outline-none">
        </div>
        <label class="flex items-center gap-2 text-sm text-white/85">
          <input type="checkbox" name="remember" value="1" class="rounded border-white/40">
          Ingat sesi login
        </label>
        <button type="submit" class="w-full min-h-[52px] rounded-xl bg-amber-400 text-slate-900 font-bold hover:bg-amber-300 transition">
          Masuk ke Loket
        </button>
      </form>
    </section>
  </main>
</body>
</html>

