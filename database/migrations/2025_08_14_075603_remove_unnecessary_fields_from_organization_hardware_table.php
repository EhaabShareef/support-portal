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
        Schema::table('organization_hardware', function (Blueprint $table) {
            // Remove fields as specified in plan.txt
            $table->dropColumn([
                'specifications',
                'purchase_price',
                'warranty_start', 
                'warranty_expiration',
                'status',
                'custom_fields'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_hardware', function (Blueprint $table) {
            // Restore removed fields
            $table->text('specifications')->nullable()->after('model');
            $table->decimal('purchase_price', 15, 2)->nullable()->after('purchase_date');
            $table->date('warranty_start')->nullable()->after('purchase_price');
            $table->date('warranty_expiration')->nullable()->after('warranty_start');
            $table->string('status')->default('active')->after('warranty_expiration');
            $table->json('custom_fields')->nullable()->after('remarks');
        });
    }
};
