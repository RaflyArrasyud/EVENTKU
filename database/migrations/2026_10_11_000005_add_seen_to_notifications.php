<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizer_requests', function (Blueprint $table) {
            $table->dateTime('seen_at')->nullable()->after('status');
        });

        Schema::table('help_messages', function (Blueprint $table) {
            $table->dateTime('read_at')->nullable()->after('isi');
        });
    }

    public function down(): void
    {
        Schema::table('help_messages', function (Blueprint $table) {
            $table->dropColumn('read_at');
        });

        Schema::table('organizer_requests', function (Blueprint $table) {
            $table->dropColumn('seen_at');
        });
    }
};
