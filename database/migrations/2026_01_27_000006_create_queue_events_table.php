<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('queue_events', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50);
            $table->string('ticket_no', 20)->nullable();
            $table->string('service_code', 10)->nullable();
            $table->string('loket_code', 10)->nullable();
            $table->string('status', 20)->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_events');
    }
};
