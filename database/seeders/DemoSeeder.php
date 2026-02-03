<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Loket;
use App\Models\LoketAssignment;
use App\Models\Organization;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::firstOrCreate(
            ['name' => 'PERUMDAM Tirta Perwira'],
            [
                'tagline' => 'Melayani dengan Hati, Mengalirkan Manfaat',
                'service_hours' => 'Senin–Jumat 08.00–15.00',
                'contact_phone' => '0281-123456',
                'contact_email' => 'layanan@perumdam.test',
                'address' => 'Jl. Contoh No. 1, Purbalingga',
                'general_notice' => 'Gunakan tiket sesuai kebutuhan layanan. Mohon antri dengan tertib.',
            ]
        );

        // copy default logo into storage/public if not exists
        if (!$org->logo_path) {
            $target = 'organizations/default-logo.png';
            if (!Storage::disk('public')->exists($target) && file_exists(public_path('logo.png'))) {
                Storage::disk('public')->put($target, file_get_contents(public_path('logo.png')));
            }
            $org->update(['logo_path' => $target]);
        }

        $services = [
            [
                'code' => 'CS',
                'name' => 'Layanan Pelanggan (CS)',
                'description' => 'Pengajuan sambungan baru, perubahan data, dan administrasi pelanggan.',
                'requires_confirmation' => true,
                'daily_quota' => 250,
                'avg_service_minutes' => 6,
                'open_at' => '08:00:00',
                'close_at' => '15:00:00',
                'is_active' => true,
            ],
            [
                'code' => 'BAY',
                'name' => 'Pembayaran (BAY)',
                'description' => 'Transaksi pembayaran dan informasi rekening.',
                'requires_confirmation' => false,
                'daily_quota' => 350,
                'avg_service_minutes' => 4,
                'open_at' => '08:00:00',
                'close_at' => '15:00:00',
                'is_active' => true,
            ],
            [
                'code' => 'PENG',
                'name' => 'Pengaduan (PENG)',
                'description' => 'Laporan gangguan, kebocoran, atau kualitas air.',
                'requires_confirmation' => false,
                'daily_quota' => 200,
                'avg_service_minutes' => 7,
                'open_at' => '08:00:00',
                'close_at' => '15:00:00',
                'is_active' => true,
            ],
            [
                'code' => 'INF',
                'name' => 'Informasi (INF)',
                'description' => 'Pertanyaan umum dan informasi prosedur.',
                'requires_confirmation' => false,
                'daily_quota' => 200,
                'avg_service_minutes' => 3,
                'open_at' => '08:00:00',
                'close_at' => '15:00:00',
                'is_active' => true,
            ],
        ];

        foreach ($services as $s) {
            Service::firstOrCreate(['code' => $s['code']], $s);
        }

        $loket1 = Loket::firstOrCreate(
            ['code' => 'L1'],
            ['name' => 'Loket 1', 'service_id' => Service::where('code', 'CS')->first()->id, 'is_active' => true]
        );

        $loket2 = Loket::firstOrCreate(
            ['code' => 'L2'],
            ['name' => 'Loket 2', 'service_id' => Service::where('code', 'BAY')->first()->id, 'is_active' => true]
        );

        $loket3 = Loket::firstOrCreate(
            ['code' => 'L3'],
            ['name' => 'Loket 3', 'service_id' => Service::where('code', 'PENG')->first()->id, 'is_active' => true]
        );

        $admin = User::firstOrCreate(
            ['email' => 'superadmin@demo.test'],
            ['name' => 'Super Admin', 'password' => Hash::make('password')]
        );

        $op = User::firstOrCreate(
            ['email' => 'admin@demo.test'],
            ['name' => 'Admin Loket', 'password' => Hash::make('password')]
        );

        LoketAssignment::firstOrCreate(['user_id' => $op->id, 'loket_id' => $loket1->id]);

        // Role & Permission (jika spatie/permission sudah terpasang)
        if (class_exists(\Spatie\Permission\Models\Role::class) && class_exists(\Spatie\Permission\Models\Permission::class)) {
            $guard = config('auth.defaults.guard', 'web');
            $roleSuper = \Spatie\Permission\Models\Role::findOrCreate('SUPER_ADMIN', $guard);
            $roleAdmin = \Spatie\Permission\Models\Role::findOrCreate('ADMIN', $guard);

            $perms = [
                'queue.call-next',
                'queue.recall',
                'queue.skip',
                'queue.serve',
                'display.read',
                'manage.settings',
            ];

            foreach ($perms as $p) {
                \Spatie\Permission\Models\Permission::findOrCreate($p, $guard);
            }

            $roleAdmin->syncPermissions($perms);

            // SUPER_ADMIN biasanya bypass via Gate::before, tapi tetap aman kalau diberi semua permission.
            $admin->assignRole($roleSuper);
            $op->assignRole($roleAdmin);
        }

        // Token Sanctum demo (kalau sanctum terpasang)
        if (method_exists($op, 'createToken')) {
            // bikin satu token awal untuk operator (dipakai di /operator via localStorage)
            $op->tokens()->where('name', 'operator-demo')->delete();
            $token = $op->createToken('operator-demo')->plainTextToken;

            $this->command?->info('Token operator-demo (Simpan ke localStorage op_token):');
            $this->command?->line($token);
        }

        Announcement::firstOrCreate(
            ['title' => 'Selamat Datang'],
            [
                'organization_id' => $org->id,
                'type' => 'TEXT',
                'body' => 'Mohon antri dengan tertib. Pastikan data Anda lengkap sebelum ke loket.',
                'is_active' => true,
                'priority' => 10,
            ]
        );

        Announcement::firstOrCreate(
            ['title' => 'Video Profil'],
            [
                'organization_id' => $org->id,
                'type' => 'VIDEO',
                'video_url' => 'https://www.w3schools.com/html/mov_bbb.mp4',
                'is_active' => true,
                'priority' => 5,
            ]
        );
    }
}
