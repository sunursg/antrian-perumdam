# Sistem Layanan Antrian - Perumdam Tirta Perwira (Laravel 12)

Ini proyek Laravel polos yang sudah disisipi backend + UI publik (JavaScript) + SSE real-time.

## 1) Setup cepat

```bash
cp .env.example .env
php artisan key:generate

# set DB_* di .env
php artisan migrate
php artisan db:seed
```

## 2) Install paket wajib (role/permission, admin panel, auth token)

> Kalau kamu mau flow lengkap (Operator token + Filament + Shield), install ini:

```bash
composer require laravel/sanctum
php artisan sanctum:install

composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\\Permission\\PermissionServiceProvider"

composer require filament/filament:^4
php artisan filament:install

composer require filament/shield
php artisan shield:install
php artisan shield:generate --all
```

Lalu aktifkan trait di `app/Models/User.php`:

```php
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

use HasApiTokens, HasRoles;
```

## 3) Jalankan aplikasi

Terminal A:
```bash
php artisan serve
```

Terminal B:
```bash
npm install
npm run dev
```

## 4) URL penting

- Landing: `.../`
- Ambil tiket: `.../ambil-tiket`
- Display TV (fullscreen): `.../display`
- Operator: `.../operator`
- Admin Filament: `.../admin`

## 5) Flow demo (manual)

1) Buka `.../ambil-tiket` → ambil tiket (misal `CS-001`).
2) Login Operator di `.../operator`.
3) Jika Sanctum terpasang: buka `.../operator/token` untuk ambil token dan otomatis tersimpan di localStorage.
4) Klik **Panggil Berikutnya** → Display akan update real-time via SSE.

## 6) Postman

Import koleksi:
- `postman/Perumdam_Antrian.postman_collection.json`

Request penting:
- `Public - Take Ticket`
- `Operator - Call Next`
- `Operator - Recall / Skip / Serve`
- `SSE - Stream (browser)`

Catatan: endpoint Operator butuh header:

`Authorization: Bearer {{token}}`

## 7) Catatan penting biar nggak nyalahin komputer terus

- SSE butuh server tidak buffering. Nginx kadang perlu `X-Accel-Buffering: no` (sudah diset).
- Kalau Display nggak update, cek Console browser dan pastikan route `/api/sse/antrian` bisa diakses.
- Nomor tiket reset harian per layanan via `date_key`.