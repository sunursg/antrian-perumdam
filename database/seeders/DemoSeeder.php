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
                'service_hours' => 'Senin-Jumat 08.00-15.00',
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
                'name' => 'Pelayanan Pelanggan',
                'description' => 'Konsultasi, pengaduan, dan layanan umum',
                'requires_confirmation' => true,
                'daily_quota' => 250,
                'avg_service_minutes' => 6,
                'open_at' => '00:00:00',
                'close_at' => '23:59:59',
                'is_active' => true,
                'theme' => 'green',
            ],
            [
                'code' => 'BAY',
                'name' => 'Pembayaran Rekening Air',
                'description' => 'Transaksi pembayaran dan informasi rekening.',
                'requires_confirmation' => false,
                'daily_quota' => 350,
                'avg_service_minutes' => 4,
                'open_at' => '00:00:00',
                'close_at' => '23:59:59',
                'is_active' => true,
                'theme' => 'blue',
            ],
        ];

        foreach ($services as $s) {
             // Handle theme_color mapping if needed or just use as is
            Service::firstOrCreate(['code' => $s['code']], [
                'name' => $s['name'],
                'description' => $s['description'],
                'requires_confirmation' => $s['requires_confirmation'],
                'daily_quota' => $s['daily_quota'],
                'avg_service_minutes' => $s['avg_service_minutes'],
                'open_at' => $s['open_at'],
                'close_at' => $s['close_at'],
                'is_active' => $s['is_active'],
            ]);
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
            ['name' => 'Loket 3', 'service_id' => Service::where('code', 'BAY')->first()->id, 'is_active' => true]
        );

        // Nonaktifkan loket lain saat demo awal agar default hanya 2 loket aktif.
        Loket::query()
            ->whereNotIn('code', ['L1', 'L2'])
            ->update(['is_active' => false]);

        $admin = User::firstOrCreate(
            ['email' => 'superadmin@demo.test'],
            ['name' => 'Super Admin', 'password' => Hash::make('password')]
        );

        $op = User::firstOrCreate(
            ['email' => 'operator1@demo.test'],
            ['name' => 'Operator Loket 1', 'password' => Hash::make('password')]
        );

        $op2 = User::firstOrCreate(
            ['email' => 'operator2@demo.test'],
            ['name' => 'Operator Loket 2', 'password' => Hash::make('password')]
        );

        LoketAssignment::firstOrCreate(['user_id' => $op->id, 'loket_id' => $loket1->id]);
        LoketAssignment::firstOrCreate(['user_id' => $op2->id, 'loket_id' => $loket2->id]);

        // Role & Permission (jika spatie/permission sudah terpasang)
        if (class_exists(\Spatie\Permission\Models\Role::class) && class_exists(\Spatie\Permission\Models\Permission::class)) {
            $guard = config('auth.defaults.guard', 'web');
            $roleSuper = \Spatie\Permission\Models\Role::findOrCreate('SUPER_ADMIN', $guard);
            $roleAdmin = \Spatie\Permission\Models\Role::findOrCreate('ADMIN', $guard);

            $perms = [
                // Dashboard
                'dashboard.view',
                'dashboard.kpi-widget',
                'dashboard.chart-widget',

                // Layanan (Service)
                'service.view',
                'service.create',
                'service.edit',
                'service.delete',

                // Loket
                'loket.view',
                'loket.create',
                'loket.edit',
                'loket.delete',

                // Penugasan Loket
                'loket-assignment.view',
                'loket-assignment.create',
                'loket-assignment.edit',
                'loket-assignment.delete',

                // Organisasi
                'organization.view',
                'organization.edit',

                // Pengumuman
                'announcement.view',
                'announcement.create',
                'announcement.edit',
                'announcement.delete',

                // Tiket Antrian
                'queue-ticket.view',
                'queue-ticket.detail',

                // Event Antrian
                'queue-event.view',

                // Operasi Antrian (Operator)
                'queue.call-next',
                'queue.recall',
                'queue.skip',
                'queue.serve',
                'queue.reset',

                // Display & Kiosk
                'display.view',
                'kiosk.view',

                // Pengguna
                'user.view',
                'user.create',
                'user.edit',
                'user.delete',

                // Role
                'role.view',
                'role.create',
                'role.edit',
                'role.delete',

                // Permission
                'permission.view',
                'permission.create',
                'permission.edit',
                'permission.delete',

                // Activity Log
                'activity-log.view',

                // Debug Log
                'debug-log.view',

                // Sistem
                'settings.manage',
            ];

            foreach ($perms as $p) {
                \Spatie\Permission\Models\Permission::findOrCreate($p, $guard);
            }

            // ADMIN gets operator-related permissions by default
            $adminPerms = array_filter($perms, fn ($p) =>
                str_starts_with($p, 'queue.') ||
                str_starts_with($p, 'display.') ||
                str_starts_with($p, 'kiosk.') ||
                str_starts_with($p, 'dashboard.')
            );
            $roleAdmin->syncPermissions($adminPerms);

            // SUPER_ADMIN biasanya bypass via Gate::before, tapi tetap aman kalau diberi semua permission.
            $admin->assignRole($roleSuper);
            $op->assignRole($roleAdmin);
            $op2->assignRole($roleAdmin);
        }

        // Token Sanctum demo (kalau sanctum terpasang)
        if (method_exists($op, 'createToken')) {
            // Optional API token untuk integrasi eksternal operator.
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
