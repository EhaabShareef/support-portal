<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('organization_contracts', function (Blueprint $table) {
            $table->dropColumn('csi_remarks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_contracts', function (Blueprint $table) {
            $table->text('csi_remarks')->nullable()->after('renewal_months');
        });
    }
};
