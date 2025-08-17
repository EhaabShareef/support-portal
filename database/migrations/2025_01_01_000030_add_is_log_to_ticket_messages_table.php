<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ticket_messages', function (Blueprint $t) {
            $t->boolean('is_log')->default(false)->index();
        });
    }

    public function down(): void
    {
        Schema::table('ticket_messages', function (Blueprint $t) {
            $t->dropColumn('is_log');
        });
    }
};
