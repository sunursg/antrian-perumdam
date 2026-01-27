@extends('app')
@section('title', 'Perumdam Tirta Perwira | Portal')

@section('bodyClass', 'bg-slate-950 text-white')
@section('content')
<div class="min-h-screen relative overflow-hidden">
  <!-- Background glow -->
  <div class="absolute inset-0">
    <div class="absolute -top-40 -left-40 h-[520px] w-[520px] rounded-full bg-sky-600/25 blur-3xl"></div>
    <div class="absolute -bottom-40 -right-40 h-[520px] w-[520px] rounded-full bg-cyan-400/20 blur-3xl"></div>
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,rgba(255,255,255,.08),transparent_60%)]"></div>
  </div>

  <div class="relative max-w-6xl mx-auto px-6 py-14">
    <div class="flex flex-col items-center text-center">
      <div class="h-24 w-24 rounded-3xl bg-white shadow-glass p-4">
        <img src="/logo.png" alt="Logo" class="h-full w-full object-contain">
      </div>

      <h1 class="mt-6 text-4xl md:text-5xl font-extrabold tracking-tight">
        Perumdam Tirta Perwira
      </h1>
      <p class="mt-2 text-sm tracking-[0.35em] text-emerald-300 font-semibold">
        KABUPATEN PURBALINGGA
      </p>

      <p class="mt-6 text-white/75 max-w-2xl">
        Sistem Manajemen Antrian Mandiri Terintegrasi. Silakan pilih modul akses sesuai kebutuhan operasional.
      </p>
    </div>

    <div class="mt-14 grid grid-cols-1 md:grid-cols-4 gap-6">
      <!-- Ambil Tiket -->
      <a href="/ambil-tiket" class="card-dark p-6 hover:bg-white/10 transition">
        <div class="h-14 w-14 rounded-2xl bg-sky-500/20 border border-sky-400/25 flex items-center justify-center">
          <!-- icon grid -->
          <svg class="h-7 w-7 text-sky-300" viewBox="0 0 24 24" fill="none">
            <path d="M4 4h7v7H4V4Zm9 0h7v7h-7V4ZM4 13h7v7H4v-7Zm9 0h7v7h-7v-7Z" stroke="currentColor" stroke-width="1.8"/>
          </svg>
        </div>
        <h3 class="mt-5 text-lg font-bold">Anjungan Antrian</h3>
        <p class="mt-2 text-sm text-white/70">Modul pengambilan nomor tiket mandiri bagi pelanggan.</p>
      </a>

      <!-- Display TV -->
      <a href="/display" class="card-dark p-6 hover:bg-white/10 transition">
        <div class="h-14 w-14 rounded-2xl bg-emerald-500/20 border border-emerald-400/25 flex items-center justify-center">
          <svg class="h-7 w-7 text-emerald-300" viewBox="0 0 24 24" fill="none">
            <path d="M4 6h16v10H4V6Zm6 14h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
          </svg>
        </div>
        <h3 class="mt-5 text-lg font-bold">Layar TV</h3>
        <p class="mt-2 text-sm text-white/70">Display informasi antrian untuk area ruang tunggu.</p>
      </a>

      <!-- Operator -->
      <a href="/operator" class="card-dark p-6 hover:bg-white/10 transition">
        <div class="h-14 w-14 rounded-2xl bg-indigo-500/20 border border-indigo-400/25 flex items-center justify-center">
          <svg class="h-7 w-7 text-indigo-300" viewBox="0 0 24 24" fill="none">
            <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm-7 9a7 7 0 0 1 14 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
          </svg>
        </div>
        <h3 class="mt-5 text-lg font-bold">Operator</h3>
        <p class="mt-2 text-sm text-white/70">Panel pemanggilan pelanggan untuk petugas loket.</p>
      </a>

      <!-- Admin (Filament) -->
      <a href="/admin" class="card-dark p-6 hover:bg-white/10 transition">
        <div class="h-14 w-14 rounded-2xl bg-amber-500/20 border border-amber-400/25 flex items-center justify-center">
          <svg class="h-7 w-7 text-amber-300" viewBox="0 0 24 24" fill="none">
            <path d="M12 15a3 3 0 1 0-3-3 3 3 0 0 0 3 3Zm8-3h2M2 12H0m18.36-6.36 1.42-1.42M4.22 19.78 2.8 21.2M18.36 18.36l1.42 1.42M4.22 4.22 2.8 2.8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
          </svg>
        </div>
        <h3 class="mt-5 text-lg font-bold">Admin</h3>
        <p class="mt-2 text-sm text-white/70">Analitik, laporan, dan konfigurasi sistem admin.</p>
      </a>
    </div>

    <div class="mt-14 text-center text-xs tracking-[0.35em] text-white/40">
      SISTEM ANTRIAN • PDAM PURBALINGGA
    </div>
  </div>
</div>
@endsection