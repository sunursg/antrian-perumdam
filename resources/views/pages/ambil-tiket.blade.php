@extends('app')
@section('title', 'Anjungan Antrian Mandiri')

@section('content')
<div class="min-h-screen bg-slate-50">
  <div class="max-w-5xl mx-auto px-6 py-12">
    <div class="flex flex-col items-center text-center">
      <div class="h-28 w-28 rounded-3xl bg-white shadow-soft p-4">
        <img src="/logo.png" alt="Logo" class="h-full w-full object-contain">
      </div>
      <h1 class="mt-7 text-4xl font-extrabold tracking-tight text-slate-900">
        Anjungan Antrian Mandiri
      </h1>
      <p class="mt-3 text-slate-600 max-w-2xl">
        Selamat datang di Perumdam Tirta Perwira. Silakan pilih layanan yang Anda butuhkan.
      </p>
    </div>

    <div class="mt-12 grid grid-cols-1 md:grid-cols-2 gap-6">
      <button data-svc="CS" class="card-light p-7 text-left hover:shadow-lg transition active:scale-[0.995]">
        <div class="flex items-center gap-5">
          <div class="h-16 w-16 rounded-2xl bg-sky-50 border border-sky-100 flex items-center justify-center">
            <svg class="h-9 w-9 text-sky-600" viewBox="0 0 24 24" fill="none">
              <path d="M16 11c1.7 0 3-1.8 3-4s-1.3-4-3-4-3 1.8-3 4 1.3 4 3 4Zm-8 0c1.7 0 3-1.8 3-4S9.7 3 8 3 5 4.8 5 7s1.3 4 3 4Zm8 2c-2.4 0-7 1.2-7 3.6V20h14v-3.4C23 14.2 18.4 13 16 13Zm-8 0C5.6 13 1 14.2 1 16.6V20h6v-3.4C7 15.3 7.4 14 8 13Z" fill="currentColor"/>
            </svg>
          </div>
          <div>
            <p class="text-xl font-bold text-slate-900">Layanan Pelanggan</p>
            <p class="mt-1 text-slate-600">Sambungan baru, perubahan data, dan administrasi</p>
          </div>
        </div>
      </button>

      <button data-svc="BAY" class="card-light p-7 text-left hover:shadow-lg transition active:scale-[0.995]">
        <div class="flex items-center gap-5">
          <div class="h-16 w-16 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center">
            <svg class="h-9 w-9 text-emerald-600" viewBox="0 0 24 24" fill="none">
              <path d="M4 7h16v10H4V7Zm2-2h12a2 2 0 0 1 2 2v1H4V7a2 2 0 0 1 2-2Zm0 14h12a2 2 0 0 0 2-2v-1H4v1a2 2 0 0 0 2 2Z" fill="currentColor"/>
            </svg>
          </div>
          <div>
            <p class="text-xl font-bold text-slate-900">Pembayaran & Rekening</p>
            <p class="mt-1 text-slate-600">Pembayaran tagihan dan informasi rekening</p>
          </div>
        </div>
      </button>

      <button data-svc="PENG" class="card-light p-7 text-left hover:shadow-lg transition active:scale-[0.995]">
        <div class="flex items-center gap-5">
          <div class="h-16 w-16 rounded-2xl bg-orange-50 border border-orange-100 flex items-center justify-center">
            <svg class="h-9 w-9 text-orange-600" viewBox="0 0 24 24" fill="none">
              <path d="M12 2 1 21h22L12 2Zm0 14a1 1 0 0 1 1 1 1 1 0 0 1-2 0 1 1 0 0 1 1-1Zm-1-6h2v5h-2V10Z" fill="currentColor"/>
            </svg>
          </div>
          <div>
            <p class="text-xl font-bold text-slate-900">Pengaduan & Gangguan</p>
            <p class="mt-1 text-slate-600">Laporan kebocoran, air mati, atau kualitas air</p>
          </div>
        </div>
      </button>

      <button data-svc="INF" class="card-light p-7 text-left hover:shadow-lg transition active:scale-[0.995]">
        <div class="flex items-center gap-5">
          <div class="h-16 w-16 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center">
            <svg class="h-9 w-9 text-indigo-600" viewBox="0 0 24 24" fill="none">
              <path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2Zm1 15h-2v-6h2v6Zm0-8h-2V7h2v2Z" fill="currentColor"/>
            </svg>
          </div>
          <div>
            <p class="text-xl font-bold text-slate-900">Pusat Informasi</p>
            <p class="mt-1 text-slate-600">Informasi umum dan layanan lainnya</p>
          </div>
        </div>
      </button>
    </div>

    <div class="mt-10 text-center text-sm text-slate-600">
      Harap hadir <span class="font-semibold text-slate-900">10 menit</span> sebelum perkiraan dipanggil.
      Simpan kode tiket untuk melihat status.
    </div>

    <div class="mt-8" id="ticketResult"></div>
  </div>
</div>

@vite(['resources/js/pages/ambil-tiket.js'])
@endsection