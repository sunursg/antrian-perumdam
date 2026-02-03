@extends('app')
@section('content')
@php
  $logoUrl = $organization->logo_path
    ? Storage::disk('public')->url($organization->logo_path)
    : asset('logo.png');
  $marqueeText = $organization->general_notice
    ?? ($announcements->first()->body ?? ($organization->tagline ?? $organization->name));
@endphp
<div class="min-h-screen bg-gradient-to-br from-indigo-700 via-purple-700 to-slate-900 text-white flex flex-col">
  {{-- Header --}}
  <div class="bg-gradient-to-r from-slate-900 via-sky-800 to-slate-900 shadow-lg">
    <div class="max-w-7xl mx-auto px-6 py-3 flex items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <div class="h-12 w-12 rounded-xl bg-white/95 shadow-md p-2">
          <img src="{{ $logoUrl }}" alt="Logo" class="h-full w-full object-contain">
        </div>
        <div>
          <p class="text-xl font-bold leading-tight">{{ $organization->name }}</p>
          @if($organization->tagline)
            <p class="text-sky-100 text-xs">{{ $organization->tagline }}</p>
          @endif
        </div>
      </div>
      <div class="text-right">
        <p id="nowClock" class="text-2xl font-semibold leading-none">--:--</p>
        <p id="nowDate" class="text-xs text-sky-100 mt-1">-</p>
      </div>
    </div>
  </div>

  {{-- Content --}}
  <div class="max-w-7xl mx-auto w-full px-6 pt-6 pb-10 flex-1 grid grid-cols-1 lg:grid-cols-4 gap-6">
    <div class="lg:col-span-1">
      <div class="rounded-3xl bg-white text-slate-900 shadow-2xl p-5 flex flex-col gap-4 h-full">
        <div class="bg-gradient-to-r from-amber-400 to-amber-500 text-white rounded-2xl px-4 py-2 text-sm font-semibold text-center">
          MEMANGGIL ANTRIAN
        </div>
        <div class="text-center space-y-1">
          <p class="text-5xl font-black tracking-tight" id="heroTicket">-</p>
          <p class="text-sm text-slate-500" id="heroCounter">Loket -</p>
          <p class="text-xs text-slate-400" id="heroWaiting"></p>
        </div>
        <div class="rounded-2xl bg-slate-50 border border-slate-200 p-3 space-y-2">
          <div class="flex items-center justify-between text-sm">
            <span class="text-slate-600">Status koneksi</span>
            <span id="connBadge" class="px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs">Menyambung...</span>
          </div>
          <div id="announcementArea" class="rounded-xl bg-white border border-slate-200 p-3 min-h-[120px] text-sm text-slate-700">
            <p class="text-sm text-slate-500">Pengumuman / Video akan tampil di sini.</p>
          </div>
        </div>
        <button id="btnSound" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-sm font-medium text-slate-800">
          Aktifkan Suara
        </button>
      </div>
    </div>

    <div class="lg:col-span-3 space-y-4">
      <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-3xl shadow-xl p-6 border border-white/10 flex items-center justify-between">
        <div>
          <p class="text-base font-semibold">Status Antrian</p>
          <p class="text-sm text-white/80">Pantau pemanggilan loket secara realtime</p>
        </div>
        <div class="text-right">
          <p class="text-xs text-white/80">Loket aktif / nonaktif ditandai warna</p>
        </div>
      </div>

      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4" id="displayGrid">
        @foreach($counters as $c)
          <div class="rounded-3xl bg-white/5 border border-white/10 shadow-lg p-5">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-white/60 text-xs">Loket</p>
                <p class="text-xl font-semibold">{{ $c['loket']['name'] }} <span class="text-white/50 text-sm">({{ $c['loket']['code'] }})</span></p>
                <p class="text-white/60 text-xs mt-1">{{ $c['service']['name'] }}</p>
              </div>
              <div class="text-right">
                <p class="text-white/60 text-xs">SEDANG DIPANGGIL</p>
                <p class="text-3xl font-semibold tracking-tight mt-1">{{ $c['sedang_dipanggil'] ?? '-' }}</p>
              </div>
            </div>
            <div class="mt-5 rounded-2xl bg-slate-900/40 border border-white/10 p-4 flex items-center justify-between">
              <p class="text-sm font-semibold {{ $c['is_active'] ? 'text-emerald-200' : 'text-amber-200' }}">
                {{ $c['is_active'] ? 'Aktif' : 'Tidak Aktif' }}
              </p>
              <span class="text-xs text-white/60">Realtime</span>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  {{-- Marquee footer --}}
  <div class="bg-slate-800 text-white py-3">
    <div class="marquee w-full overflow-hidden">
      <div class="marquee__inner whitespace-nowrap" id="marqueeText">
        <span class="mx-8">{{ $marqueeText }}</span>
        <span class="mx-8">{{ $marqueeText }}</span>
        <span class="mx-8">{{ $marqueeText }}</span>
      </div>
    </div>
  </div>
</div>

<style>
.marquee__inner {
  display: inline-block;
  padding-left: 100%;
  animation: marquee 18s linear infinite;
}
@keyframes marquee {
  0%   { transform: translateX(0); }
  100% { transform: translateX(-100%); }
}
</style>

<script type="application/json" id="display-data">
{!! json_encode([
    'organization' => $organization,
    'announcements' => $announcements,
    'counters' => $counters,
    'logoUrl' => $logoUrl,
], JSON_UNESCAPED_UNICODE) !!}
</script>

@vite(['resources/js/pages/display.js'])
@endsection
