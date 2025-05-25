<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            // Building permissions
            'building-create',
            'building-read',
            'building-update',
            'building-delete',

            // Choice permissions
            'choice-read',


            // Course permissions
            'course-create',
            'course-read',
            'course-update',
            'course-delete',






            // EducationalBackground permissions
            'educational-background-create',
            'educational-background-read',
            'educational-background-update',
            'educational-background-delete',










            // efficiency permissions
            'efficiency-create',
            'efficiency-read',
            'efficiency-update',
            'efficiency-delete',

            // Field permissions
            'field-create',
            'field-read',
            'field-update',
            'field-delete',


            // GuestInstructor permissions
            'guest-instructor-create',
            'guest-instructor-read',
            'guest-instructor-update',
            'guest-instructor-delete',


            // Instructor permissions
            'instructor-create',
            'instructor-read',
            'instructor-update',
            'instructor-delete',

            // InstructorRole permissions
            'instructor-role-create',
            'instructor-role-read',
            'instructor-role-update',
            'instructor-role-delete',




            // Parameter permissions
            'parameter-create',
            'parameter-read',
            'parameter-update',
            'parameter-delete',

            // ProfessionalExperience permissions
            'professional-experience-create',
            'professional-experience-read',
            'professional-experience-update',
            'professional-experience-delete',

            // QualityAssuranceEvaluator permissions
            'quality-assurance-create',
            'quality-assurance-read',
            'quality-assurance-update',
            'quality-assurance-delete',






            // Research permissions
            'research-create',
            'research-read',
            'research-update',
            'research-delete',



            // Role permissions
            'role-create',
            'role-read',
            'role-update',
            'role-delete',

            // Room permissions
            'room-create',
            'room-read',
            'room-update',
            'room-delete',

            // Schedule permissions
            'schedule-create',
            'schedule-read',
            'schedule-update',
            'schedule-delete',

            // ScheduleResult permissions




            // Section permissions
            'section-create',
            'section-read',
            'section-update',
            'section-delete',

            // Semester permissions
            'semester-create',
            'semester-read',
            'semester-update',
            'semester-delete',





            // Timeslot permissions
            'timeslot-create',
            'timeslot-read',
            'timeslot-update',
            'timeslot-delete',

            // User permissions
            'user-create',
            'user-read',
            'user-update',
            'user-delete',



            // YearSemesterCourse permissions
            'year-semester-course-create',
            'year-semester-course-read',
            'year-semester-course-update',
            'year-semester-course-delete',
        ];

        // Seed the permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'sanctum']);
        }
    }
}