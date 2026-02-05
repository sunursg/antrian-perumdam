## Setup Dua Monitor (PC1) untuk Sistem Antrian PDAM

Skema: satu PC memakai dua monitor.
- Monitor 1 (atas / TV besar): ditujukan untuk **Display** (`/display`). Jalankan browser fullscreen (F11) pada URL `http://<ip-pc>:8000/display`. Jika perlu, pin tab dan matikan sleep.
- Monitor 2 (bawah / operator): untuk **panel admin/operator** di `/admin` (Filament).
  - ADMIN: hanya menu Operator Loket (memanggil/recall/skip/serve).
  - SUPER_ADMIN: konfigurasi lengkap (organisasi, loket, layanan, pengumuman, role, audit).

Langkah cepat:
1. Set ekstend display di OS (bukan duplicate). Monitor atas sebagai screen kedua.
2. Buka browser 2 jendela:
   - Jendela A seret ke monitor atas, buka `/display`, tekan F11.
   - Jendela B di monitor bawah untuk operator/admin.
3. Pastikan audio monitor atas aktif jika memakai bunyi bel atau TTS.
4. Jika koneksi pakai IP LAN, gunakan `http://<ip-pc>:8000/display` di monitor atas supaya bebas dari loopback batasan.

Tips:
- Atur auto-start browser + URL dengan task scheduler / startup script agar display selalu menyala.
- Gunakan UPS untuk menghindari mati mendadak yang memutus SSE.
