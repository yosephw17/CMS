<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;

class CoursesTableSeeder extends Seeder
{
    public function run()
    {
        $courses = [
            ['course_code' => 'CoEng2112', 'name' => 'Intermediate Computer Programming', 'cp' => 5, 'lecture_cp' => 2, 'lab_cp' => 3, 'department_id' => 2],
            ['course_code' => 'CoEng2114', 'name' => 'Computational Methods', 'cp' => 5, 'lecture_cp' => 2, 'lab_cp' => 3, 'department_id' => 2],
            ['course_code' => 'CoEng2122', 'name' => 'Data Structure', 'cp' => 5, 'lecture_cp' => 2, 'lab_cp' => 3, 'department_id' => 2],
            ['course_code' => 'CoEng3121', 'name' => 'Database Management Systems', 'cp' => 5, 'lecture_cp' => 2, 'lab_cp' => 3, 'department_id' => 2],
            ['course_code' => 'CoEng3131', 'name' => 'Computer Networks', 'cp' => 5, 'lecture_cp' => 2, 'lab_cp' => 3, 'department_id' => 2],
            ['course_code' => 'CoEng3092', 'name' => 'Digital Logic Design', 'cp' => 5, 'lecture_cp' => 2, 'lab_cp' => 3, 'department_id' => 2],
            ['course_code' => 'CoEng3122', 'name' => 'Algorithms Analysis and Design', 'cp' => 5, 'lecture_cp' => 2, 'lab_cp' => 3, 'department_id' => 2],
            ['course_code' => 'CoEng3132', 'name' => 'Internet Programming', 'cp' => 5, 'lecture_cp' => 2, 'lab_cp' => 3, 'department_id' => 2],
            ['course_code' => 'CoEng4131', 'name' => 'Computer and Network Security', 'cp' => 5, 'lecture_cp' => 2, 'lab_cp' => 3, 'department_id' => 2],
            ['course_code' => 'CoEng4091', 'name' => 'Computer Architecture and Organization', 'cp' => 5, 'lecture_cp' => 2, 'lab_cp' => 0, 'department_id' => 2],
            ['course_code' => 'CoEng4111', 'name' => 'Advanced Programming', 'cp' => 5, 'lecture_cp' => 2, 'lab_cp' => 3, 'department_id' => 2],
            ['course_code' => 'CoEng4092', 'name' => 'Microprocessors and Interfacing', 'cp' => 5, 'lecture_cp' => 2, 'lab_cp' => 3, 'department_id' => 2],
            ['course_code' => 'CoEng4142', 'name' => 'Software Engineering', 'cp' => 5, 'lecture_cp' => 2, 'lab_cp' => 0, 'department_id' => 2],
            ['course_code' => 'CoEng4164', 'name' => 'Industry Internship', 'cp' => 24, 'lecture_cp' => 0, 'lab_cp' => 32, 'department_id' => 2],
            ['course_code' => 'CoEng5091', 'name' => 'Embedded Systems', 'cp' => 5, 'lecture_cp' => 2, 'lab_cp' => 3, 'department_id' => 2],
            ['course_code' => 'CoEng5131', 'name' => 'Artificial Intelligence', 'cp' => 5, 'lecture_cp' => 2, 'lab_cp' => 3, 'department_id' => 2],
            ['course_code' => 'CoEng5141', 'name' => 'Operating Systems and System Programming', 'cp' => 5, 'lecture_cp' => 2, 'lab_cp' => 3, 'department_id' => 2],
            ['course_code' => 'CoEng5143', 'name' => 'Introduction to Distributed Systems', 'cp' => 5, 'lecture_cp' => 2, 'lab_cp' => 3, 'department_id' => 2],
            ['course_code' => 'CoEng5142', 'name' => 'Computer Graphics', 'cp' => 5, 'lecture_cp' => 2, 'lab_cp' => 3, 'department_id' => 2],
            ['course_code' => 'CoEng5152', 'name' => 'B.Sc. Project', 'cp' => 12, 'lecture_cp' => 0, 'lab_cp' => 18, 'department_id' => 2],
        ];

        foreach ($courses as $course) {
            Course::create($course);
        }
    }
}
