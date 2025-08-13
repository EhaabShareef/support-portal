<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organization_hardware', function (Blueprint $table) {
            $table->foreignId('hardware_type_id')->nullable()->after('contract_id')->constrained('hardware_types');
            $table->integer('quantity')->default(1)->after('model');
            $table->boolean('serial_required')->default(false)->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('organization_hardware', function (Blueprint $table) {
            $table->dropConstrainedForeignId('hardware_type_id');
            $table->dropColumn(['quantity','serial_required']);
        });
    }
};
