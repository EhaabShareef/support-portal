<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, let's add the new columns
        Schema::table('schedule_event_types', function (Blueprint $table) {
            $table->string('label')->after('id')->nullable(); // Will be populated from 'name'
            $table->string('tailwind_classes')->after('color')->nullable(); // New field for styling
        });

        // Backfill data: move 'name' to 'label' and generate tailwind_classes from color
        $eventTypes = DB::table('schedule_event_types')->get();
        foreach ($eventTypes as $eventType) {
            $tailwindClasses = $this->generateTailwindClasses($eventType->color);
            
            DB::table('schedule_event_types')
                ->where('id', $eventType->id)
                ->update([
                    'label' => $eventType->name, // Move name to label
                    'tailwind_classes' => $tailwindClasses
                ]);
        }

        // Now make label required and drop the name column
        Schema::table('schedule_event_types', function (Blueprint $table) {
            $table->string('label')->nullable(false)->change(); // Make required
            $table->dropIndex(['name']); // Drop index on name
            $table->dropColumn('name'); // Remove old name column
            $table->unique('label'); // Add unique constraint on label
            $table->index('label'); // Add index on label
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back the name column
        Schema::table('schedule_event_types', function (Blueprint $table) {
            $table->string('name')->after('id')->nullable();
            $table->dropIndex(['label']);
            $table->dropUnique(['label']);
        });

        // Backfill name from label
        $eventTypes = DB::table('schedule_event_types')->get();
        foreach ($eventTypes as $eventType) {
            DB::table('schedule_event_types')
                ->where('id', $eventType->id)
                ->update(['name' => $eventType->label]);
        }

        // Complete the rollback
        Schema::table('schedule_event_types', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
            $table->unique('name');
            $table->index('name');
            $table->dropColumn(['label', 'tailwind_classes']);
        });
    }

    /**
     * Generate Tailwind classes based on hex color
     */
    private function generateTailwindClasses(string $color): string
    {
        // Convert hex colors to appropriate Tailwind classes
        $colorMap = [
            '#3b82f6' => 'bg-blue-500 text-white border-blue-600',
            '#10b981' => 'bg-green-500 text-white border-green-600',
            '#f59e0b' => 'bg-yellow-500 text-white border-yellow-600',
            '#8b5cf6' => 'bg-purple-500 text-white border-purple-600',
            '#ef4444' => 'bg-red-500 text-white border-red-600',
            '#6366f1' => 'bg-indigo-500 text-white border-indigo-600',
            '#6b7280' => 'bg-gray-500 text-white border-gray-600',
            '#14b8a6' => 'bg-teal-500 text-white border-teal-600',
            '#dc2626' => 'bg-red-600 text-white border-red-700',
            '#ea580c' => 'bg-orange-500 text-white border-orange-600',
            '#ec4899' => 'bg-pink-500 text-white border-pink-600',
            '#b91c1c' => 'bg-red-700 text-white border-red-800',
            '#06b6d4' => 'bg-cyan-500 text-white border-cyan-600',
            '#059669' => 'bg-emerald-600 text-white border-emerald-700',
            '#e11d48' => 'bg-rose-500 text-white border-rose-600',
            '#7c3aed' => 'bg-violet-600 text-white border-violet-700',
            '#64748b' => 'bg-slate-500 text-white border-slate-600',
        ];

        return $colorMap[$color] ?? 'bg-blue-500 text-white border-blue-600';
    }
};