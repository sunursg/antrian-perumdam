@extends('app')

@section('content')
@php
  $org = $appOrganization ?? null;
  $logoUrl = $org?->logo_path ? Storage::disk('public')->url($org->logo_path) : asset('logo.png');
@endphp
<div class="max-w-5xl mx-auto px-6 py-10">
  <div class="flex items-start justify-between gap-6">
    <div>
      <div class="flex items-center gap-3">
        <img src="{{ $logoUrl }}" alt="Logo" class="h-10 w-10 rounded-xl border border-slate-200 bg-white p-1">
        <div>
          <p class="text-sm text-slate-500">{{ $org?->name ?? 'Sistem Antrian' }}</p>
          <h1 class="text-2xl md:text-3xl font-semibold mt-1">Panel Admin / Operator</h1>
        </div>
      </div>
      <p class="text-slate-600 mt-2">ADMIN memanggil antrian sesuai loket yang ditugaskan. SUPER_ADMIN mengelola pengaturan.</p>
    </div>

    <div class="flex items-center gap-2">
      <a href="/display" class="px-4 py-2 rounded-xl border border-slate-200 hover:bg-slate-50">Display</a>
      <a href="/ambil-tiket" class="px-4 py-2 rounded-xl border border-slate-200 hover:bg-slate-50">Ambil Tiket</a>
    </div>
  </div>

  @guest
  <div class="mt-8 rounded-2xl bg-white border border-slate-200 p-6 shadow-sm">
    <h2 class="font-semibold">Login Operator</h2>
    <p class="text-sm text-slate-600 mt-1">Masuk untuk mengakses kontrol loket.</p>

    <form method="POST" action="/operator/login" class="mt-5 grid gap-3 max-w-md">
      @csrf
      <input name="email" type="email" value="{{ old('email') }}" placeholder="Email"
        class="px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-200" />
      <input name="password" type="password" placeholder="Password"
        class="px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-200" />

      @if ($errors->any())
      <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
        {{ $errors->first() }}
      </div>
      @endif

      <button class="px-5 py-3 rounded-2xl bg-sky-600 text-white font-semibold hover:bg-sky-700 shadow-sm">
        Masuk
      </button>

      <p class="text-xs text-slate-500">
        Demo: admin@demo.test / password
      </p>
    </form>
  </div>
  @endguest

  @auth
  <div class="min-h-[70vh] flex items-center justify-center">
    <div class="w-[520px] max-w-[92vw] card-light p-8">
      <h2 class="text-center text-xl font-extrabold tracking-tight text-slate-900">PILIH LOKET TUGAS</h2>

      <div id="opLokets" class="mt-7 space-y-4"></div>

      <button id="btnBackHome" class="w-full mt-8 text-xs tracking-[0.25em] text-slate-400 hover:text-slate-600">
        KEMBALI KE BERANDA
      </button>

      <div id="opControls" class="hidden mt-10 border-t border-slate-100 pt-6">
        <div class="flex items-center justify-between">
          <p class="text-sm text-slate-600">Sedang dipanggil</p>
          <p id="opCurrent" class="text-xl font-bold text-slate-900">-</p>
        </div>

        <div class="mt-5 grid grid-cols-2 gap-3">
          <button id="btnNext" class="btn-primary">Panggil Berikutnya</button>
          <button id="btnRecall" class="px-5 py-3 rounded-2xl border border-slate-200 hover:bg-slate-50 font-semibold">Panggil Ulang</button>
          <button id="btnSkip" class="px-5 py-3 rounded-2xl border border-orange-200 bg-orange-50 hover:bg-orange-100 font-semibold text-orange-900">Lewati (No-show)</button>
          <button id="btnServe" class="px-5 py-3 rounded-2xl border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 font-semibold text-emerald-900">Selesai</button>
        </div>
      </div>
    </div>
  </div>

  @vite(['resources/js/pages/operator.js'])
  @endauth
</div>
@endsection
