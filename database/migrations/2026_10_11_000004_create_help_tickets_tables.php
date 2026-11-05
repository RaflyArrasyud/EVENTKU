<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('help_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('topik');
            $table->enum('status', ['terbuka', 'ditutup'])->default('terbuka');
            $table->timestamps();
        });

        Schema::create('help_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('help_tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('isi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('help_messages');
        Schema::dropIfExists('help_tickets');
    }
};
