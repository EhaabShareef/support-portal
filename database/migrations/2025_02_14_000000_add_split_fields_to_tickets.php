<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $t) {
            $t->foreignId('split_from_ticket_id')
                ->nullable()
                ->constrained('tickets')
                ->nullOnDelete()
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $t) {
            $t->dropConstrainedForeignId('split_from_ticket_id');
        });
    }
};
