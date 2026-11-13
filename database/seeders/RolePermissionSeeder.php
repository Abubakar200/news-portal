<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $rolePermissions = [
            'super-admin' => [
                'admin.access',
                'content.manage',
                'services.manage',
                'users.manage',
                'posts.create',
                'posts.update',
                'posts.update-own',
                'posts.delete',
                'posts.schedule',
                'posts.review',
                'posts.publish',
            ],
            'admin' => [
                'admin.access',
                'content.manage',
                'services.manage',
                'users.manage',
                'posts.create',
                'posts.update',
                'posts.update-own',
                'posts.delete',
                'posts.schedule',
                'posts.review',
                'posts.publish',
            ],
            'editor' => [
                'admin.access',
                'posts.update',
                'posts.schedule',
                'posts.review',
                'posts.publish',
            ],
            'author' => [
                'admin.access',
                'posts.create',
                'posts.update-own',
                'posts.delete-own',
            ],
        ];

        foreach (array_unique(array_merge(...array_values($rolePermissions))) as $permission) {
            Permission::query()->firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($rolePermissions as $role => $permissions) {
            Role::query()->firstOrCreate([
                'name' => $role,
                'guard_name' => 'web',
            ])->syncPermissions($permissions);
        }
    }
}
