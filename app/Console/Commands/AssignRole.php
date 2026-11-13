<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class AssignRole extends Command
{
    protected $signature = 'app:assign-role {email} {role}';

    protected $description = 'Assign an existing role to a user by email address';

    public function handle(): int
    {
        $user = User::query()->where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error('No user was found with that email address.');

            return self::FAILURE;
        }

        $role = Role::query()
            ->where('name', $this->argument('role'))
            ->where('guard_name', 'web')
            ->first();

        if (! $role) {
            $this->error('The requested role does not exist. Run the database seeder first.');

            return self::FAILURE;
        }

        $user->assignRole($role);

        $this->info("Assigned the {$role->name} role to {$user->email}.");

        return self::SUCCESS;
    }
}
