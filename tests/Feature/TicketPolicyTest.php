<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Organization;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TicketPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_or_support_can_split()
    {
        $perm = Permission::create(['name' => 'tickets.update']);
        Role::create(['name' => 'admin'])->givePermissionTo($perm);
        Role::create(['name' => 'support'])->givePermissionTo($perm);
        Role::create(['name' => 'client']);

        $org = Organization::create([
            'name' => 'Org',
            'company' => 'Org Co',
            'company_contact' => 'Contact',
            'tin_no' => '123',
            'email' => 'org@example.com',
            'phone' => '123',
        ]);

        $dept = Department::create(['name' => 'Support']);

        $ticket = Ticket::create([
            'subject' => 'Original',
            'status' => 'open',
            'priority' => 'normal',
            'organization_id' => $org->id,
            'client_id' => User::factory()->create()->id,
            'department_id' => $dept->id,
        ]);

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $support = User::factory()->create();
        $support->assignRole('support');
        $client = User::factory()->create();
        $client->assignRole('client');

        $this->assertTrue($admin->can('split', $ticket));
        $this->assertTrue($support->can('split', $ticket));
        $this->assertFalse($client->can('split', $ticket));
    }
}
