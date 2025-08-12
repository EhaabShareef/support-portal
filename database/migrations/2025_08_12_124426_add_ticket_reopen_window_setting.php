<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert the ticket reopen window setting
        DB::table('settings')->insertOrIgnore([
            'key' => 'tickets.reopen_window_days',
            'value' => '3',
            'type' => 'integer',
            'description' => 'Number of days after closure within which clients can reopen tickets',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the ticket reopen window setting
        DB::table('settings')->where('key', 'tickets.reopen_window_days')->delete();
    }
};
