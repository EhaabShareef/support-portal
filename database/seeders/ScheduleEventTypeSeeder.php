<?php

namespace Database\Seeders;

use App\Models\ScheduleEventType;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ScheduleEventTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first admin user for created_by
        $admin = User::whereHas('roles', function ($q) {
            $q->where('name', 'admin');
        })->first();

        if (!$admin) {
            $this->command->error('No admin user found. Please create an admin user first.');
            return;
        }

        $eventTypes = [
            ['label' => 'Project Remote', 'description' => 'Remote project work', 'color' => '#3b82f6', 'tailwind_classes' => 'bg-blue-500 text-white border-blue-600', 'sort_order' => 1],
            ['label' => 'Project Onsite', 'description' => 'Onsite project work', 'color' => '#10b981', 'tailwind_classes' => 'bg-green-500 text-white border-green-600', 'sort_order' => 2],
            ['label' => 'Afternoon Shift', 'description' => 'Afternoon shift work', 'color' => '#f59e0b', 'tailwind_classes' => 'bg-yellow-500 text-white border-yellow-600', 'sort_order' => 3],
            ['label' => 'Work From Home', 'description' => 'Remote work from home', 'color' => '#8b5cf6', 'tailwind_classes' => 'bg-purple-500 text-white border-purple-600', 'sort_order' => 4],
            ['label' => 'Day in Leave', 'description' => 'Personal day leave', 'color' => '#ef4444', 'tailwind_classes' => 'bg-red-500 text-white border-red-600', 'sort_order' => 5],
            ['label' => 'Office Support', 'description' => 'General office support work', 'color' => '#6366f1', 'tailwind_classes' => 'bg-indigo-500 text-white border-indigo-600', 'sort_order' => 6],
            ['label' => 'Absence', 'description' => 'Unplanned absence', 'color' => '#6b7280', 'tailwind_classes' => 'bg-gray-500 text-white border-gray-600', 'sort_order' => 7],
            ['label' => 'Onsite Support', 'description' => 'Client onsite support', 'color' => '#14b8a6', 'tailwind_classes' => 'bg-teal-500 text-white border-teal-600', 'sort_order' => 8],
            ['label' => 'Sick Leave', 'description' => 'Medical leave', 'color' => '#dc2626', 'tailwind_classes' => 'bg-red-600 text-white border-red-700', 'sort_order' => 9],
            ['label' => 'Hotline Support', 'description' => 'Phone support duty', 'color' => '#ea580c', 'tailwind_classes' => 'bg-orange-500 text-white border-orange-600', 'sort_order' => 10],
            ['label' => 'Public Holiday', 'description' => 'National public holiday', 'color' => '#ec4899', 'tailwind_classes' => 'bg-pink-500 text-white border-pink-600', 'sort_order' => 11],
            ['label' => 'Family Emergency', 'description' => 'Family emergency leave', 'color' => '#b91c1c', 'tailwind_classes' => 'bg-red-700 text-white border-red-800', 'sort_order' => 12],
            ['label' => 'Annual Leave', 'description' => 'Planned annual leave', 'color' => '#f59e0b', 'tailwind_classes' => 'bg-yellow-500 text-white border-yellow-600', 'sort_order' => 13],
            ['label' => 'Travel', 'description' => 'Business travel', 'color' => '#06b6d4', 'tailwind_classes' => 'bg-cyan-500 text-white border-cyan-600', 'sort_order' => 14],
            ['label' => 'Demo/Presentation', 'description' => 'Product demo or presentation', 'color' => '#059669', 'tailwind_classes' => 'bg-emerald-600 text-white border-emerald-700', 'sort_order' => 15],
            ['label' => 'Compassionate Leave', 'description' => 'Bereavement or compassionate leave', 'color' => '#e11d48', 'tailwind_classes' => 'bg-rose-500 text-white border-rose-600', 'sort_order' => 16],
            ['label' => 'Training', 'description' => 'Internal training session', 'color' => '#7c3aed', 'tailwind_classes' => 'bg-violet-600 text-white border-violet-700', 'sort_order' => 17],
            ['label' => 'National Leave', 'description' => 'National celebration leave', 'color' => '#64748b', 'tailwind_classes' => 'bg-slate-500 text-white border-slate-600', 'sort_order' => 18],
        ];

        foreach ($eventTypes as $eventType) {
            ScheduleEventType::updateOrCreate(
                ['label' => $eventType['label']], // Use label as unique identifier
                [
                    'description' => $eventType['description'],
                    'color' => $eventType['color'],
                    'tailwind_classes' => $eventType['tailwind_classes'],
                    'sort_order' => $eventType['sort_order'],
                    'is_active' => true,
                    'created_by' => $admin->id,
                ]
            );
        }

        $this->command->info('Created ' . count($eventTypes) . ' schedule event types.');
    }
}
