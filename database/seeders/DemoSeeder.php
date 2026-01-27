<?php

namespace Database\Seeders;

use App\Models\Loket;
use App\Models\LoketAssignment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['code' => 'CS', 'name' => 'Layanan Pelanggan (CS)', 'daily_quota' => 250, 'avg_service_minutes' => 6, 'open_at' => '08:00:00', 'close_at' => '15:00:00', 'is_active' => true],
            ['code' => 'BAY', 'name' => 'Pembayaran (BAY)', 'daily_quota' => 350, 'avg_service_minutes' => 4, 'open_at' => '08:00:00', 'close_at' => '15:00:00', 'is_active' => true],
            ['code' => 'PENG', 'name' => 'Pengaduan (PENG)', 'daily_quota' => 200, 'avg_service_minutes' => 7, 'open_at' => '08:00:00', 'close_at' => '15:00:00', 'is_active' => true],
            ['code' => 'INF', 'name' => 'Informasi (INF)', 'daily_quota' => 200, 'avg_service_minutes' => 3, 'open_at' => '08:00:00', 'close_at' => '15:00:00', 'is_active' => true],
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

        $admin = User::firstOrCreate(
            ['email' => 'superadmin@perumdam.test'],
            ['name' => 'Super Admin', 'password' => Hash::make('password')]
        );

        $op = User::firstOrCreate(
            ['email' => 'operator@perumdam.test'],
            ['name' => 'Operator Loket', 'password' => Hash::make('password')]
        );

        LoketAssignment::firstOrCreate(['user_id' => $op->id, 'loket_id' => $loket1->id]);

        // Role & Permission (jika spatie/permission sudah terpasang)
        if (class_exists(\Spatie\Permission\Models\Role::class) && class_exists(\Spatie\Permission\Models\Permission::class)) {
            $roleSuper = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'SUPER_ADMIN']);
            $roleOp = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'OPERATOR']);

            $perms = [
                'queue.call-next',
                'queue.recall',
                'queue.skip',
                'queue.serve',
                'display.read',
            ];

            foreach ($perms as $p) {
                \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $p]);
            }

            $roleOp->syncPermissions($perms);

            // SUPER_ADMIN biasanya bypass via Gate::before, tapi tetap aman kalau diberi semua permission.
            $admin->assignRole($roleSuper);
            $op->assignRole($roleOp);
        }

        // Token Sanctum demo (kalau sanctum terpasang)
        if (method_exists($op, 'createToken')) {
            // bikin satu token awal untuk operator (dipakai di /operator via localStorage)
            $op->tokens()->where('name', 'operator-demo')->delete();
            $token = $op->createToken('operator-demo')->plainTextToken;

            $this->command?->info('Token operator-demo (Simpan ke localStorage op_token):');
            $this->command?->line($token);
        }
    }
}
