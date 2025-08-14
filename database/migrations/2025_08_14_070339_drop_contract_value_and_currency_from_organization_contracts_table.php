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
            $table->dropColumn(['contract_value', 'currency']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_contracts', function (Blueprint $table) {
            $table->decimal('contract_value', 15, 2)->nullable()->after('csi_number');
            $table->string('currency', 3)->default('USD')->after('contract_value');
        });
    }
};
