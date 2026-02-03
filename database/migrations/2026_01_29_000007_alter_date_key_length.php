<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('ticket_counters') && Schema::hasColumn('ticket_counters', 'date_key')) {
            DB::statement('ALTER TABLE `ticket_counters` MODIFY `date_key` VARCHAR(10)');
        }

        if (Schema::hasTable('queue_tickets') && Schema::hasColumn('queue_tickets', 'date_key')) {
            DB::statement('ALTER TABLE `queue_tickets` MODIFY `date_key` VARCHAR(10)');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ticket_counters') && Schema::hasColumn('ticket_counters', 'date_key')) {
            DB::statement('ALTER TABLE `ticket_counters` MODIFY `date_key` VARCHAR(8)');
        }

        if (Schema::hasTable('queue_tickets') && Schema::hasColumn('queue_tickets', 'date_key')) {
            DB::statement('ALTER TABLE `queue_tickets` MODIFY `date_key` VARCHAR(8)');
        }
    }
};
