@extends('app')
@section('title', 'Detail Tiket')

@section('content')
@php
  $org = $appOrganization ?? null;
  $logoUrl = $org?->logo_path ? Storage::disk('public')->url($org->logo_path) : asset('logo.png');
@endphp
<div class="min-h-screen bg-slate-50">
  <div class="max-w-2xl mx-auto px-6 py-12">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
      <div class="flex items-center gap-3">
        <img src="{{ $logoUrl }}" alt="Logo" class="h-10 w-10 rounded-xl border border-slate-200 bg-white p-1">
        <div>
          <h1 class="text-2xl font-bold text-slate-900">Detail Tiket</h1>
          <p class="mt-1 text-sm text-slate-600">{{ $org->name ?? 'Sistem Antrian' }}</p>
        </div>
      </div>

      <div class="mt-6 grid gap-3 text-sm text-slate-700">
        <div class="flex items-center justify-between">
          <span class="text-slate-500">Nomor Tiket</span>
          <span class="font-mono text-base font-semibold">{{ $ticket->ticket_no }}</span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-slate-500">Status</span>
          <span class="font-semibold">{{ $ticket->status }}</span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-slate-500">Layanan</span>
          <span>{{ $ticket->service?->name ?? '-' }}</span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-slate-500">Loket</span>
          <span>{{ $ticket->loket?->name ?? '-' }}</span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-slate-500">Waktu Ambil</span>
          <span>{{ optional($ticket->created_at)->format('d M Y H:i') }}</span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-slate-500">Waktu Panggil</span>
          <span>{{ optional($ticket->called_at)->format('d M Y H:i') ?? '-' }}</span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-slate-500">Waktu Selesai</span>
          <span>{{ optional($ticket->served_at)->format('d M Y H:i') ?? '-' }}</span>
        </div>
      </div>

      <div class="mt-6 flex flex-wrap gap-3">
        <a href="/ambil-tiket" class="rounded-xl border border-slate-200 px-4 py-2 text-sm hover:bg-slate-50">
          Ambil Tiket Baru
        </a>
        <a href="/" class="rounded-xl bg-slate-900 px-4 py-2 text-sm text-white hover:bg-slate-800">
          Kembali ke Beranda
        </a>
      </div>
    </div>
  </div>
</div>
@endsection
