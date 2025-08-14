<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('settings')->where('key', 'support_hotlines')->update(['group' => 'general']);
        DB::table('settings')->where('key', 'default_organization')->update(['group' => 'users']);
        DB::table('settings')->whereIn('key', ['ticket_status_colors', 'ticket_priority_colors'])->update(['group' => 'ticket']);
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'support_hotlines')->update(['group' => 'support']);
        DB::table('settings')->where('key', 'default_organization')->update(['group' => 'user_management']);
        DB::table('settings')->whereIn('key', ['ticket_status_colors', 'ticket_priority_colors'])->update(['group' => 'ticket_colors']);
    }
};
