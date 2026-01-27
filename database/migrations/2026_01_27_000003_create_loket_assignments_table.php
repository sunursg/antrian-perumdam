<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('loket_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('loket_id')->constrained()->cascadeOnDelete();
            $table->unique(['user_id', 'loket_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loket_assignments');
    }
};
