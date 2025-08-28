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
                ->after('owner_id')
                ->constrained('tickets', 'id')
                ->nullOnDelete();
            
            $t->index('split_from_ticket_id');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $t) {
            $t->dropConstrainedForeignId('split_from_ticket_id');
        });
    }
};
