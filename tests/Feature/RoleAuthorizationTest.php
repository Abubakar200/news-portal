<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_only_users_with_admin_access_can_access_the_admin_panel(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->canAccessPanel(Filament::getPanel('admin')));

        $user->assignRole('author');

        $this->assertTrue($user->canAccessPanel(Filament::getPanel('admin')));
        $this->assertFalse($user->canAccessPanel(Panel::make()->id('other')));
    }

    public function test_super_admin_bypasses_permission_checks(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->assertTrue($user->can('an.unseeded.permission'));
    }

    public function test_guest_permission_checks_are_denied(): void
    {
        $this->assertFalse(Gate::forUser(null)->allows('an.unseeded.permission'));
    }

    public function test_console_command_assigns_a_seeded_role_to_an_existing_user(): void
    {
        $user = User::factory()->create();

        $this->artisan('app:assign-role', [
            'email' => $user->email,
            'role' => 'super-admin',
        ])
            ->expectsOutput("Assigned the super-admin role to {$user->email}.")
            ->assertExitCode(0);

        $this->assertTrue($user->fresh()->hasRole('super-admin'));
    }
}
