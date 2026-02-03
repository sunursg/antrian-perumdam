<?php

use App\Enums\TicketStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('queue_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('date_key', 10);
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('seq');
            $table->string('ticket_no', 20)->unique();
            $table->enum('status', array_map(fn($e) => $e->value, TicketStatus::cases()))
                ->default(TicketStatus::MENUNGGU->value);

            $table->foreignId('loket_id')->nullable()->constrained('lokets')->nullOnDelete();
            $table->timestamp('called_at')->nullable();
            $table->timestamp('served_at')->nullable();
            $table->timestamp('noshow_at')->nullable();

            $table->timestamps();

            $table->index(['date_key', 'service_id', 'status']);
            $table->unique(['date_key', 'service_id', 'seq']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_tickets');
    }
};
