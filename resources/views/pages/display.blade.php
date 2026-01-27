@extends('app')

@section('content')
<div class="min-h-screen bg-slate-950 text-white">
  <div class="px-8 py-6 flex items-center justify-between">
    <div>
      <p class="text-white/70 text-sm">Perumdam Tirta Perwira Kabupaten Purbalingga</p>
      <h1 class="text-3xl font-semibold tracking-tight mt-1">Display TV Antrian</h1>
    </div>

    <div class="flex items-center gap-3">
      <button id="btnSound" class="px-4 py-2 rounded-2xl bg-white/10 border border-white/15 hover:bg-white/15">
        Aktifkan Suara
      </button>
      <span id="connBadge" class="text-xs px-3 py-1.5 rounded-full bg-emerald-500/15 border border-emerald-400/20 text-emerald-200">
        Terhubung
      </span>
    </div>
  </div>

  <div class="px-8 pb-10">
    <div class="grid lg:grid-cols-3 gap-6" id="displayGrid"></div>
  </div>
</div>

@vite(['resources/js/pages/display.js'])
@endsection
