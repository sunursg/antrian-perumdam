@php
  use Illuminate\Support\Facades\Storage;
  $org = $appOrganization ?? null;
  $logoUrl = $org?->logo_path ? Storage::disk('public')->url($org->logo_path) : asset('logo.png');
@endphp

<x-filament::page>
  @vite(['resources/css/app.css','resources/js/pages/operator.js'])

  <div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div class="flex items-center gap-3">
        <div class="h-12 w-12 rounded-xl bg-white shadow-md p-2">
          <img src="{{ $logoUrl }}" alt="Logo" class="h-full w-full object-contain">
        </div>
        <div>
          <p class="text-sm uppercase tracking-[0.2em] text-slate-500">Panel Operator</p>
          <h1 class="text-2xl md:text-3xl font-bold leading-tight text-slate-900">{{ $org?->name ?? 'Sistem Antrian' }}</h1>
          @if($org?->tagline)
            <p class="text-slate-500 text-sm">{{ $org->tagline }}</p>
          @endif
        </div>
      </div>
      <div class="flex items-center gap-2">
        <a href="/display" class="px-4 py-2 rounded-xl bg-slate-100 border border-slate-200 hover:bg-slate-200 text-sm text-slate-700">Display</a>
        <a href="/kiosk" class="px-4 py-2 rounded-xl bg-slate-100 border border-slate-200 hover:bg-slate-200 text-sm text-slate-700">Kiosk</a>
      </div>
    </div>

    <div class="grid lg:grid-cols-[0.95fr_1.75fr] gap-6 items-start">
      <div class="space-y-4">
        <div class="rounded-2xl bg-white border border-slate-200 p-6 shadow-sm">
          <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Status Operator</p>
          <h3 class="text-2xl font-bold mt-2 text-slate-900">{{ auth()->user()->name }}</h3>
          <p class="text-sm text-slate-500">{{ auth()->user()->email }}</p>
          <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
            <div class="rounded-xl bg-slate-50 p-3 border border-slate-200">
              <p class="text-xs text-slate-500">Peran</p>
              <p class="font-semibold text-slate-800">{{ implode(', ', auth()->user()->getRoleNames()->toArray()) }}</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-3 border border-slate-200">
              <p class="text-xs text-slate-500">Loket aktif</p>
              <p class="font-semibold text-lg text-slate-800" id="opCurrentLoket">-</p>
            </div>
          </div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 p-6 shadow-sm">
          <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Menu cepat</p>
          <div class="mt-3 flex flex-wrap gap-2">
            <a href="/display" class="px-3 py-2 rounded-xl bg-slate-100 border border-slate-200 hover:bg-slate-200 text-sm text-slate-700">Buka Display</a>
            <a href="/kiosk" class="px-3 py-2 rounded-xl bg-slate-100 border border-slate-200 hover:bg-slate-200 text-sm text-slate-700">Kiosk</a>
            <button id="btnBackHome" class="px-3 py-2 rounded-xl bg-slate-100 border border-slate-200 hover:bg-slate-200 text-sm text-slate-700">Beranda</button>
          </div>
        </div>
      </div>

      <div class="rounded-3xl bg-white text-slate-900 shadow-sm border border-slate-200 p-7">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div>
            <p class="text-xs text-slate-500">Langkah 1</p>
            <h2 class="text-xl font-bold">Pilih Loket Tugas</h2>
          </div>
          <div class="text-xs text-slate-500">Realtime, tersinkron ke SSE</div>
        </div>

        <div id="opLokets" class="mt-6 grid sm:grid-cols-2 gap-4"></div>

        <div id="opControls" class="hidden mt-8 border-t border-slate-200 pt-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
              <p class="text-xs text-slate-500">Sedang dipanggil</p>
              <p id="opCurrent" class="text-4xl font-black text-slate-900">-</p>
            </div>
            <div class="rounded-full px-3 py-1 text-xs bg-slate-100 text-slate-600">Kontrol Loket</div>
          </div>

          <div class="mt-6 grid sm:grid-cols-2 gap-3">
            <button id="btnNext" class="px-5 py-3 rounded-2xl bg-emerald-600 text-white font-semibold shadow hover:bg-emerald-700">Panggil Berikutnya</button>
            <button id="btnRecall" class="px-5 py-3 rounded-2xl border border-slate-200 hover:bg-slate-50 font-semibold">Panggil Ulang</button>
            <button id="btnSkip" class="px-5 py-3 rounded-2xl border border-amber-200 bg-amber-50 hover:bg-amber-100 font-semibold text-amber-900">Lewati (No-show)</button>
            <button id="btnServe" class="px-5 py-3 rounded-2xl border border-blue-200 bg-blue-50 hover:bg-blue-100 font-semibold text-blue-900">Selesai</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</x-filament::page>
