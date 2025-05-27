<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            // Building permissions
            'building-create',
            'building-view',
            'building-update',
            'building-delete',
            'building-management',


            'room-create',
            'room-view',
            'room-update',
            'room-delete',




            // Choice permissions
            'choice-view',

            // Course permissions
            'course-create',
            'course-view',
            'course-update',
            'course-delete',

            // EducationalBackground permissions
            'educational-background-create',
            'educational-background-view',
            'educational-background-update',
            'educational-background-delete',

            // Efficiency permissions
            'efficiency-create',
            'efficiency-view',
            'efficiency-update',
            'efficiency-delete',
            'efficiency-report',

            // Field permissions
            'field-create',
            'field-view',
            'field-update',
            'field-delete',

            // GuestInstructor permissions
            'guest-instructor-create',
            'guest-instructor-view',
            'guest-instructor-update',
            'guest-instructor-delete',

            // Instructor permissions
            'instructor-create',
            'instructor-view',
            'instructor-update',
            'instructor-delete',

            // InstructorRole permissions
            'instructor-role-create',
            'instructor-role-view',
            'instructor-role-update',
            'instructor-role-delete',

            // Parameter permissions
            'parameter-create',
            'parameter-view',
            'parameter-update',
            'parameter-delete',


            'assignment-create',
            'assignment-view',
            'assignment-update',
            'assignment-delete',
            'assignment-report',

            // ProfessionalExperience permissions
            'professional-experience-create',
            'professional-experience-view',
            'professional-experience-update',
            'professional-experience-delete',

            // QualityAssuranceEvaluator permissions
            'quality-assurance-create',
            'quality-assurance-view',
            'quality-assurance-report',


            // Research permissions
            'research-create',
            'research-view',
            'research-update',
            'research-delete',

            // Role permissions
            'role-create',
            'role-view',
            'role-update',
            'role-delete',

            // Room permissions
            'room-create',
            'room-view',
            'room-update',
            'room-delete',

            // Schedule permissions
            'schedule-create',
            'schedule-view',
            'schedule-update',
            'schedule-delete',

            // Section permissions
            'section-create',
            'section-view',
            'section-update',
            'section-delete',

            // Semester permissions
            'semester-create',
            'semester-view',
            'semester-update',
            'semester-delete',

            // Timeslot permissions
            'timeslot-create',
            'timeslot-view',
            'timeslot-update',
            'timeslot-delete',

            // User permissions
            'user-create',
            'user-view',
            'user-update',
            'user-delete',

            'assign-mentor',

            // YearSemesterCourse permissions
            'year-semester-course-create',
            'year-semester-course-view',
            'year-semester-course-update',
            'year-semester-course-delete',


            'dashboard-view',



            'load-create',
            'load-view',
            'load-update',
            'load-delete',
            'load-export',


            'choice-view',


            'program-view',
            'program-create',
            'program-update',
            'program-delete',



            'filed-create',
            'filed-view',
            'filed-update',
            'filed-delete',
        ];

        // Seed the permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'sanctum']);
        }

        // Assign all permissions to the Admin role
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'sanctum']);
        $adminRole->syncPermissions($permissions);
    }
}