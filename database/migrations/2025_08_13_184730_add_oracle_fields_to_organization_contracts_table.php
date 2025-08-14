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
            $table->boolean('is_oracle')->default(false)->after('includes_hardware');
            $table->string('csi_number')->nullable()->after('is_oracle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_contracts', function (Blueprint $table) {
            $table->dropColumn(['is_oracle', 'csi_number']);
        });
    }
};
