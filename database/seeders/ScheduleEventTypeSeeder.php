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
            ['code' => 'PR', 'label' => 'Project Remote', 'color' => 'bg-blue-500', 'sort_order' => 1],
            ['code' => 'PO', 'label' => 'Project Onsite', 'color' => 'bg-green-500', 'sort_order' => 2],
            ['code' => 'HAS', 'label' => 'Afternoon Shift', 'color' => 'bg-yellow-500', 'sort_order' => 3],
            ['code' => 'WFH', 'label' => 'Work From Home', 'color' => 'bg-purple-500', 'sort_order' => 4],
            ['code' => 'DIL', 'label' => 'Day in Leave', 'color' => 'bg-red-500', 'sort_order' => 5],
            ['code' => 'SO', 'label' => 'Office Support', 'color' => 'bg-indigo-500', 'sort_order' => 6], // Default
            ['code' => 'A', 'label' => 'Absence', 'color' => 'bg-gray-500', 'sort_order' => 7],
            ['code' => 'OS', 'label' => 'Onsite Support', 'color' => 'bg-teal-500', 'sort_order' => 8],
            ['code' => 'S', 'label' => 'Sick', 'color' => 'bg-red-600', 'sort_order' => 9],
            ['code' => 'HS', 'label' => 'Hotline Support', 'color' => 'bg-orange-500', 'sort_order' => 10],
            ['code' => 'H', 'label' => 'Public Holidays', 'color' => 'bg-pink-500', 'sort_order' => 11],
            ['code' => 'FRL', 'label' => 'Family Emergency Leave', 'color' => 'bg-red-700', 'sort_order' => 12],
            ['code' => 'L', 'label' => 'Leave', 'color' => 'bg-amber-500', 'sort_order' => 13],
            ['code' => 'TR', 'label' => 'Travelling', 'color' => 'bg-cyan-500', 'sort_order' => 14],
            ['code' => 'D', 'label' => 'Demo', 'color' => 'bg-emerald-500', 'sort_order' => 15],
            ['code' => 'CL', 'label' => 'Compassionate Leave', 'color' => 'bg-rose-500', 'sort_order' => 16],
            ['code' => 'IT', 'label' => 'Internal Training', 'color' => 'bg-violet-500', 'sort_order' => 17],
            ['code' => 'NL', 'label' => 'National Leave', 'color' => 'bg-slate-500', 'sort_order' => 18],
        ];

        foreach ($eventTypes as $eventType) {
            ScheduleEventType::create([
                'code' => $eventType['code'],
                'label' => $eventType['label'],
                'color' => $eventType['color'],
                'sort_order' => $eventType['sort_order'],
                'is_active' => true,
                'created_by' => $admin->id,
            ]);
        }

        $this->command->info('Created ' . count($eventTypes) . ' schedule event types.');
    }
}
