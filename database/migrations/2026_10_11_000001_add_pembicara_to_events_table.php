<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('pembicara')->nullable()->after('deskripsi');
            $table->string('peran_pembicara')->nullable()->after('pembicara');
            $table->string('foto_pembicara')->nullable()->after('peran_pembicara');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['pembicara', 'peran_pembicara', 'foto_pembicara']);
        });
    }
};
