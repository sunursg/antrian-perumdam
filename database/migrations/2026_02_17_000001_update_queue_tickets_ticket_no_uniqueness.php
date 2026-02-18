<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('queue_tickets', function (Blueprint $table) {
            // Drop existing unique constraint
            // In Laravel/MySQL, the unique constraint name usually matches the column name if created as $table->string(...)->unique()
            // or queue_tickets_ticket_no_unique if created as $table->unique('ticket_no')
            $table->dropUnique(['ticket_no']);

            // Create new unique constraint scoped by date_key
            $table->unique(['date_key', 'ticket_no']);
        });
    }

    public function down(): void
    {
        Schema::table('queue_tickets', function (Blueprint $table) {
            $table->dropUnique(['date_key', 'ticket_no']);
            $table->unique('ticket_no');
        });
    }
};
