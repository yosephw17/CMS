<?php

namespace App\Http\Controllers;

use App\Models\Instructor;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;


class MentorshipController extends Controller
{


    public function index(){
        $students = Student::with(['instructor', 'department','academicYear'])->get();

         return response()->json(['students' => $students]);
    }


    public function update(Request $request, $id)
    {
        // Fetch the student by id
        $student = Student::find($id);

        // Check if the student exists
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        // Validate the incoming request data
        $validatedData = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|nullable|email|max:255',
            'phone_number' => 'sometimes|string|max:20',
            'department_id' => 'sometimes|exists:departments,id',
            'assigned_mentor_id' => 'sometimes|nullable|exists:instructors,id',
            'academic_year_id' => 'sometimes|exists:academic_years,id',
            'hosting_company' => 'sometimes|nullable|string|max:255',
            'location' => 'sometimes|nullable|string|max:255',
        ]);

        // Update the student with the validated data
        $student->update($validatedData);

        // Return a success response with the updated student data
        return response()->json([
            'message' => 'Student updated successfully',
            'student' => $student
        ]);
    }

public function uploadCSV(Request $request)
{
    Log::info('upload', ['request' => $request->all()]);

    $request->validate([
        'file' => 'required|mimes:csv,txt,xlsx,xls',
        'academic_year_id' => 'required|exists:academic_years,id',
        'department_id' => 'nullable|exists:departments,id'
    ]);

    $academicYearId = $request->academic_year_id;
    $requestDepartmentId = $request->department_id;

    $file = $request->file('file');
    $extension = strtolower($file->getClientOriginalExtension());

    $students = [];

    $headerMappings = [
        'full_name' => ['full name', 'fullname', 'name', 'student name'],
        'department' => ['department', 'dept', 'department name'],
        'phone_number' => ['phone number', 'phone', 'contact', 'mobile'],
        'sex' => ['sex', 'gender'],
        'hosting_company' => ['hosting company', 'company', 'host company'],
        'location' => ['location', 'address', 'place']
    ];

    $mapHeader = function ($header) use ($headerMappings) {
        $normalized = Str::slug(strtolower(trim($header)), '_');

        foreach ($headerMappings as $expected => $variations) {
            if ($normalized === $expected || in_array(strtolower($header), array_map('strtolower', $variations))) {
                return $expected;
            }
        }

        return $normalized;
    };

    if (in_array($extension, ['csv', 'txt'])) {
        $csv = Reader::createFromPath($file->getRealPath(), 'r');
        $csv->setHeaderOffset(0);

        $rawHeaders = $csv->getHeader();
        $headers = array_map($mapHeader, $rawHeaders);

        if (!in_array('phone_number', $headers)) {
            return response()->json([
                'message' => 'Missing required phone number column',
                'raw_headers' => $rawHeaders,
                'mapped_headers' => $headers
            ], 400);
        }

        foreach ($csv->getRecords() as $record) {
            $normalized = array_combine($headers, array_values($record));
            if (!$normalized) continue;

            $students[] = [
                'full_name' => $normalized['full_name'] ?? null,
                'department_name' => $normalized['department'] ?? null,
                'phone_number' => $normalized['phone_number'] ?? null,
                'sex' => $normalized['sex'] ?? null,
                'hosting_company' => $normalized['hosting_company'] ?? null,
                'location' => $normalized['location'] ?? null,
            ];
        }
    } elseif (in_array($extension, ['xlsx', 'xls'])) {
        $data = Excel::toArray([], $file)[0];

        if (empty($data) || count($data) < 2) {
            return response()->json(['message' => 'Empty Excel file or invalid format'], 400);
        }

        $rawHeaders = array_shift($data);
        $headers = array_map($mapHeader, $rawHeaders);

        foreach ($data as $row) {
            $row = array_pad($row, count($headers), null);
            $normalized = array_combine($headers, $row);
            if (!$normalized) continue;

            $students[] = [
                'full_name' => $normalized['full_name'] ?? null,
                'department_name' => $normalized['department'] ?? null,
                'phone_number' => $normalized['phone_number'] ?? null,
                'sex' => $normalized['sex'] ?? null,
                'hosting_company' => $normalized['hosting_company'] ?? null,
                'location' => $normalized['location'] ?? null,
            ];
        }
    }

    foreach ($students as $studentData) {
        if (empty($studentData['phone_number'])) continue;

        // Determine department ID
        $departmentId = null;

        if ($requestDepartmentId) {
            $departmentId = $requestDepartmentId;
        } elseif (!empty($studentData['department_name'])) {
            $department = \App\Models\Department::whereRaw('LOWER(name) = ?', [strtolower($studentData['department_name'])])->first();
            if ($department) {
                $departmentId = $department->id;
            }
        }

        if (!$departmentId) continue; // Skip student if department ID is not found

        Student::create([
            'full_name' => $studentData['full_name'],
            'phone_number' => $studentData['phone_number'],
            'department_id' => $departmentId,
            'academic_year_id' => $academicYearId,
            'sex' => $studentData['sex'],
            'hosting_company' => $studentData['hosting_company'],
            'location' => $studentData['location'],
        ]);

    }

    return response()->json(['message' => 'Students uploaded successfully.']);
}






public function assignMentors(Request $request)
{
    $request->validate([
        'academic_year_id' => 'required|integer|exists:academic_years,id',
        'department_id' => 'required|integer|exists:departments,id',
    ]);

    try {
        DB::beginTransaction();

        // Get students based on academic year and department, and no mentor assigned
        $students = Student::where('academic_year_id', $request->academic_year_id)
            ->where('department_id', $request->department_id)
            ->whereNull('assigned_mentor_id')
            ->get();

        if ($students->isEmpty()) {
            return response()->json(['message' => 'No students to assign mentors to.'], 400);
        }

        // Get mentors who are available and in the same department
        $availableMentors = Instructor::where('is_available', true)
            ->where('department_id', $request->department_id)
            ->get();

        if ($availableMentors->isEmpty()) {
            return response()->json(['message' => 'No available mentors.'], 400);
        }

        // Group students by company
        $studentsByCompany = $students->groupBy('hosting_company')->map(function ($companyStudents) {
            return [
                'students' => $companyStudents,
                'count' => $companyStudents->count(),
            ];
        })->sortByDesc('count');

        $totalCompanies = $studentsByCompany->count();
        $totalMentors = $availableMentors->count();

        $companiesPerMentor = floor($totalCompanies / $totalMentors);
        $remainingCompanies = $totalCompanies % $totalMentors;

        $assignments = [];
        $mentors = $availableMentors->shuffle();
        $mentorIndex = 0;
        $companyCountAssigned = array_fill(0, $totalMentors, 0);

        foreach ($studentsByCompany as $company => $companyData) {
            $minCompanies = min($companyCountAssigned);
            $availableMentorIndices = array_keys($companyCountAssigned, $minCompanies);
            $mentorIndex = $availableMentorIndices[array_rand($availableMentorIndices)];
            $mentor = $mentors[$mentorIndex];

            foreach ($companyData['students'] as $student) {
                $assignments[] = [
                    'student_id' => $student->id,
                    'assigned_mentor_id' => $mentor->id
                ];
            }

            $companyCountAssigned[$mentorIndex]++;
            if ($companyCountAssigned[$mentorIndex] >= $companiesPerMentor + ($remainingCompanies > 0 ? 1 : 0)) {
                $remainingCompanies--;
                unset($mentors[$mentorIndex]);
                $mentors = $mentors->values();
                $companyCountAssigned = array_values($companyCountAssigned);
                $totalMentors--;
            }
        }

        foreach ($assignments as $assignment) {
            Student::where('id', $assignment['student_id'])
                ->update(['assigned_mentor_id' => $assignment['assigned_mentor_id']]);
        }

        DB::commit();

        return response()->json([
            'message' => 'Mentors assigned successfully',
            'assigned_count' => count($assignments),
            'companies_assigned' => $studentsByCompany->keys()->toArray()
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Mentor assignment failed', ['error' => $e->getMessage()]);
        return response()->json(['message' => 'Failed to assign mentors', 'error' => $e->getMessage()], 500);
    }
}



}
