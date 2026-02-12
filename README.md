# Sistem Antrian Digital - Perumdam Tirta Perwira

Aplikasi Sistem Manajemen Antrian Modern berbasis Web untuk Perumdam Tirta Perwira (PDAM Purbalingga). Dibangun dengan **Laravel 11**, **React (Inertia.js)**, **Filament PHP**, dan **Tailwind CSS**.

---

## 🚀 Fitur Utama

### 1. 🖥️ Kiosk (Pengambilan Tiket)
- Antarmuka layar sentuh yang responsif.
- Opsi layanan: **Pembayaran Rekening Air** & **Pelayanan Pelanggan (CS)**.
- Integrasi Printer Thermal (opsional/browser print).
- Validasi jam layanan otomatis.

### 2. 📺 Display TV (Digital Signage)
- **Tema:** Deep Midnight Blue + Cyan (Modern Glassmorphism).
- **Layout:** Split Screen (30% Antrian / 70% Video Multimedia).
- **Fitur:**
  - **Panel Panggilan:** Menampilkan nomor antrian yang sedang dipanggil dengan teks besar & glowing.
  - **Grid Loket:** Status real-time loket (CS/Payment).
  - **Running Text:** Informasi berjalan di footer.
  - **Voice Announcement:** Suara pemanggilan otomatis ("Nomor Antrian A-001 Ke Loket 1").

### 3. 🎙️ Operator Panel
- Dashboard khusus petugas loket.
- **Kontrol:**
  - `Panggil` (Call Next).
  - `Panggil Ulang` (Recall).
  - `Lewati` (Skip/No-Show).
  - `Selesai` (Finish Transaction).
- Statistik antrian harian per user.

### 4. 🛠️ Admin Panel (Filament)
- Manajemen User & Role (Super Admin, Operator).
- Manajemen Layanan & Loket.
- Laporan Harian & Bulanan.

---

## 🛠️ Teknologi

- **Backend:** Laravel 12.x
- **Frontend:** React + Inertia.js + TypeScript
- **Styling:** Tailwind CSS + Lucide Icons
- **Admin:** Filament PHP v4
- **Database:** MySQL 8.0+
- **Real-time:** Polling / SSE (Server-Sent Events)

---

## ⚙️ Instalasi

### Prasyarat
- PHP 8.2+
- Composer
- Node.js & NPM
- MySQL

### Langkah-langkah

1. **Clone Repository**
   ```bash
   git clone https://github.com/sunursg/antrian-perumdam.git
   cd antrian-perumdam
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Setup Environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Edit `.env` dan sesuaikan koneksi database (`DB_DATABASE`, `DB_USERNAME`, dll).*

4. **Migrasi & Seeding**
   ```bash
   php artisan migrate:fresh --seed
   ```
   *Seed akan membuat data dummy untuk Layanan, Loket, dan User.*

5. **Jalankan Aplikasi**
   Terminal 1 (Backend):
   ```bash
   php artisan serve
   ```
   Terminal 2 (Frontend):
   ```bash
   npm run dev
   ```

---

## 📖 Cara Penggunaan

### URL Akses

| Halaman | URL | Keterangan |
|---------|-----|------------|
| **Kiosk** | `/kiosk` | Halaman pengambilan tiket untuk pelanggan. |
| **Display** | `/display` | Tampilan TV Ruang Tunggu. |
| **Operator** | `/counter` | petugas loket. |
| **Admin** | `/admin` | Panel manajemen sistem. |

### Akun Demo (Seeder)

- **Super Admin**: `superadmin@pdam.com` / `password`
- **Operator Loket 1**: `operator1@pdam.com` / `password`
- **Operator Loket 2**: `operator2@pdam.com` / `password`

---

## 🔧 Troubleshooting

- **Display tidak update?**
  Pastikan backend (`php artisan serve`) berjalan. Sistem menggunakan polling/SSE. Cek console browser untuk error koneksi.

- **Suara tidak keluar?**
  Pastikan browser mengizinkan *Autoplay Audio*. Biasanya perlu interaksi user (klik di halaman Display) sekali untuk mengaktifkan audio context.

- **Tampilan CSS berantakan?**
  Pastikan `npm run dev` berjalan untuk compile Tailwind assets.

---

**Developed for Perumdam Tirta Perwira Purbalingga**