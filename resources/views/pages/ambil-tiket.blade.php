@extends('app')
@section('title', ($organization->name ?? 'Anjungan Antrian') . ' | Ambil Tiket')

@section('content')
@php
  $logoUrl = $organization->logo_path
    ? Storage::disk('public')->url($organization->logo_path)
    : asset('logo.png');
@endphp
<div class="min-h-screen bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-700 text-white">
  <div class="max-w-5xl lg:max-w-6xl mx-auto px-5 lg:px-8 py-8 space-y-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div class="flex items-center gap-4">
        <div class="h-16 w-16 rounded-2xl bg-white/95 shadow-lg p-2">
          <img src="{{ $logoUrl }}" alt="Logo" class="h-full w-full object-contain">
        </div>
        <div>
          <p class="text-sm uppercase tracking-[0.18em] text-white/80">Sistem Antrian Digital</p>
          <h1 class="text-2xl md:text-3xl font-bold leading-tight">{{ $organization->name }}</h1>
          @if($organization->tagline)
            <p class="text-white/80 text-sm">{{ $organization->tagline }}</p>
          @endif
        </div>
      </div>
      <div class="text-right text-sm text-white/80">
        @if($organization->service_hours)
          <p><span class="font-semibold text-white">Jam Layanan:</span> {{ $organization->service_hours }}</p>
        @endif
        @if($organization->contact_phone || $organization->contact_email)
          <p class="mt-1">
            Kontak: {{ $organization->contact_phone }} @if($organization->contact_email) | {{ $organization->contact_email }} @endif
          </p>
        @endif
        @if($organization->address)
          <p class="mt-1">{{ $organization->address }}</p>
        @endif
      </div>
    </div>

    <div class="bg-white/10 rounded-3xl border border-white/15 shadow-2xl backdrop-blur-sm p-6 space-y-6">
      <div class="text-center space-y-1">
        <p class="text-sm text-white/80">Sentuh tombol di bawah untuk mengambil nomor antrian</p>
        <p class="text-3xl font-bold text-white">Pilih Jenis Layanan</p>
      </div>
      <div class="flex justify-center">
        <div id="serviceButtons" class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-4xl w-full"></div>
      </div>
      <div class="text-center">
        <a href="/display" class="inline-flex items-center gap-2 text-sm px-4 py-2 rounded-full bg-white/15 border border-white/20 hover:bg-white/25 transition">
          Lihat Display
        </a>
      </div>
    </div>

    <div class="grid md:grid-cols-3 gap-4">
      <div class="md:col-span-2">
        <div class="rounded-2xl bg-white/15 border border-white/15 p-4 text-sm text-white/85">
          <p class="font-semibold text-white">Pengingat</p>
          <p class="mt-1">Harap hadir 10 menit sebelum dipanggil. Simpan kode tiket untuk mengecek status.</p>
        </div>
      </div>
      <div class="space-y-3">
        @if($announcements->count())
          <div class="rounded-2xl bg-white text-slate-900 p-4 space-y-3 shadow-lg">
            <div class="flex items-center gap-2">
              <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
              <p class="text-sm font-semibold">Pengumuman</p>
            </div>
            <div class="space-y-3">
              @foreach($announcements as $item)
                <div class="rounded-xl bg-slate-50 border border-slate-200 p-3">
                  <p class="text-sm font-semibold text-slate-900">{{ $item->title }}</p>
                  @if($item->type === 'TEXT')
                    <p class="text-xs text-slate-600 mt-1">{{ $item->body }}</p>
                  @else
                    <p class="text-xs text-slate-500 mt-1">Konten video aktif.</p>
                  @endif
                </div>
              @endforeach
            </div>
          </div>
        @endif

        <div class="rounded-2xl border border-amber-200 bg-amber-50/95 p-4 text-sm text-amber-900 shadow-lg">
          <p class="font-semibold">Aturan Layanan Singkat</p>
          <p class="mt-1">{{ $organization->general_notice ?? 'Gunakan tiket sesuai kebutuhan layanan Anda. Hormati antrian pelanggan lain.' }}</p>
        </div>
      </div>
    </div>

    <div class="mt-2" id="ticketResult"></div>
  </div>
</div>

<script type="application/json" id="app-data">
{!! json_encode([
    'services' => $services,
    'organization' => $organization,
    'announcements' => $announcements,
], JSON_UNESCAPED_UNICODE) !!}
</script>

@vite(['resources/js/pages/ambil-tiket.js'])
@endsection

