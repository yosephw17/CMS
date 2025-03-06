<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            // Instructor-related permissions
            'instructor-list',
            'instructor-create',
            'instructor-edit',
            'instructor-delete',

            // User management permissions
            'user-list',
            'user-create',
            'user-edit',
            'user-delete',

            // Course management permissions
            'course-list',
            'course-create',
            'course-edit',
            'course-delete',

            // Student management permissions
            'student-list',
            'student-create',
            'student-edit',
            'student-delete',

            // Class management permissions
            'class-list',
            'class-create',
            'class-edit',
            'class-delete',

            // Enrollment management permissions
            'enrollment-list',
            'enrollment-create',
            'enrollment-edit',
            'enrollment-delete',


            // Reports management
            'report-view',
            'report-generate',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
    }
}
