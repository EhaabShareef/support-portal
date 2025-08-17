<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $t) {
            $t->boolean('is_merged')->default(false)->index();
            $t->foreignId('merged_into_ticket_id')->nullable()->constrained('tickets')->nullOnDelete()->index();
            $t->boolean('is_merged_master')->default(false)->index();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $t) {
            $t->dropColumn(['is_merged', 'is_merged_master']);
            $t->dropConstrainedForeignId('merged_into_ticket_id');
        });
    }
};
