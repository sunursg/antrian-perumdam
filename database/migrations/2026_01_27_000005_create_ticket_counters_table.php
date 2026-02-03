<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ticket_counters', function (Blueprint $table) {
            $table->id();
            $table->string('date_key', 10);
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('last_seq')->default(0);
            $table->timestamps();

            $table->unique(['date_key', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_counters');
    }
};
