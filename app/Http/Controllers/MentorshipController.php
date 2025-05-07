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
        $students = Student::with('instructor')->get();

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
        'assigned_mentor_id' => 'sometimes|nullable|exists:instructors,id',
        // Add other fields you want to allow updating
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
        'file' => 'required|mimes:csv,txt,xlsx,xls'
    ]);

    $file = $request->file('file');
    $extension = strtolower($file->getClientOriginalExtension());

    $students = [];

    // Define header mappings for common variations
    $headerMappings = [
        'full_name' => ['full name', 'fullname', 'name', 'student name', 'Full Name'],
        'department' => ['department', 'dept', 'department name', 'Department'],
        'phone_number' => ['phone number', 'phone', 'contact number', 'mobile', 'phone_number', 'Phone Number', 'Mobile Number', 'Contact', 'Tel', 'Telephone'],
        'sex' => ['sex', 'gender', 'Sex', 'Gender'],
        'hosting_company' => ['hosting company', 'company', 'host company', 'Hosting Company'],
        'location' => ['location', 'address', 'place', 'Location']
    ];

    // Function to map incoming headers to expected keys
    $mapHeader = function ($header) use ($headerMappings) {
        $header = trim($header); // Remove leading/trailing spaces
        $normalized = Str::slug(strtolower($header), '_'); // Normalize to slug (e.g., "phone number" -> "phone_number")
        Log::debug('header_mapping', ['original' => $header, 'normalized' => $normalized]);

        // Check if normalized header or original header matches any variation
        foreach ($headerMappings as $expected => $variations) {
            if ($normalized === $expected || in_array(strtolower($header), array_map('strtolower', $variations))) {
                return $expected;
            }
        }
        return $normalized; // Fallback to normalized header
    };

    if (in_array($extension, ['csv', 'txt'])) {
        // Handle CSV with League\Csv
        $csv = Reader::createFromPath($file->getRealPath(), 'r');
        $csv->setHeaderOffset(0);

        $rawHeaders = $csv->getHeader();
        $headers = array_map($mapHeader, $rawHeaders);
        Log::info('csv_headers', ['raw' => $rawHeaders, 'mapped' => $headers]);

        // Validate required headers
        if (!in_array('phone_number', $headers)) {
            return response()->json([
                'message' => 'Missing required header for phone number. Expected one of: ' . implode(', ', $headerMappings['phone_number']),
                'raw_headers' => $rawHeaders,
                'mapped_headers' => $headers
            ], 400);
        }

        foreach ($csv->getRecords() as $record) {
            $normalized = array_combine($headers, array_values($record));
            if (!$normalized) {
                Log::warning('csv_record_skipped', ['record' => $record]);
                continue;
            }

            $student = [
                'full_name' => $normalized['full_name'] ?? null,
                'department' => $normalized['department'] ?? null,
                'phone_number' => $normalized['phone_number'] ?? null,
                'sex' => $normalized['sex'] ?? null,
                'hosting_company' => $normalized['hosting_company'] ?? null,
                'location' => $normalized['location'] ?? null,
            ];

            if (!array_filter($student)) {
                Log::info('csv_empty_student_skipped', ['student' => $student]);
                continue;
            }

            $students[] = $student;
        }
    } elseif (in_array($extension, ['xlsx', 'xls'])) {
        // Handle Excel with Laravel Excel
        $data = Excel::toArray([], $file)[0]; // First sheet
        Log::info('excel_raw_data', ['rows' => array_slice($data, 0, 5)]); // Log first 5 rows

        if (empty($data) || count($data) < 2) {
            return response()->json(['message' => 'Invalid Excel file format or empty file'], 400);
        }

        $rawHeaders = array_shift($data); // First row is headers
        $headers = array_map($mapHeader, $rawHeaders);
        Log::info('excel_headers', ['raw' => $rawHeaders, 'mapped' => $headers]);

        // Validate required headers


        foreach ($data as $row) {
            // Skip empty rows
            if (empty(array_filter($row, fn($value) => !is_null($value) && trim($value) !== ''))) {
                Log::info('excel_empty_row_skipped', ['row' => $row]);
                continue;
            }

            // Pad row with nulls if shorter than headers
            $row = array_pad($row, count($headers), null);
            $normalized = array_combine($headers, $row);
            if (!$normalized) {
                Log::warning('excel_row_invalid', ['row' => $row]);
                continue;
            }

            $student = [
                'full_name' => $normalized['full_name'] ?? null,
                'department' => $normalized['department'] ?? null,
                'phone_number' => $normalized['phone_number'] ?? null,
                'sex' => $normalized['sex'] ?? null,
                'hosting_company' => $normalized['hosting_company'] ?? null,
                'location' => $normalized['location'] ?? null,
            ];

            if (!array_filter($student)) {
                Log::info('excel_empty_student_skipped', ['student' => $student]);
                continue;
            }

            $students[] = $student;
        }
    }

    Log::info('processed_students', ['students' => $students, 'count' => count($students)]);

    // Save to DB
    foreach ($students as $studentData) {
        if (empty($studentData['phone_number'])) {
            Log::info('student_skipped_no_phone', ['student' => $studentData]);
            continue;
        }

        Student::updateOrCreate(
            ['phone_number' => $studentData['phone_number']],
            [
                'full_name' => $studentData['full_name'],
                'department_id' => 1,
                'sex' => $studentData['sex'],
                'hosting_company' => $studentData['hosting_company'],
                'location' => $studentData['location'],
            ]
        );
    }

    return response()->json(['message' => 'File uploaded and students stored successfully']);
}




public function assignMentors()
{
    try {
        // Start a database transaction
        DB::beginTransaction();

        // Get all students without an assigned mentor
        $students = Student::whereNull('assigned_mentor_id')->get();

        if ($students->isEmpty()) {
            return response()->json(['message' => 'No students to assign mentors to.'], 400);
        }

        // Get all available mentors
        $availableMentors = Instructor::where('is_available', true)->get();

        if ($availableMentors->isEmpty()) {
            return response()->json(['message' => 'No available mentors.'], 400);
        }

        // Group students by hosting_company
        $studentsByCompany = $students->groupBy('hosting_company')->map(function ($companyStudents) {
            return [
                'students' => $companyStudents,
                'count' => $companyStudents->count(),
            ];
        })->sortByDesc('count'); // Sort by student count for larger groups first

        $totalCompanies = $studentsByCompany->count();
        $totalMentors = $availableMentors->count();

        if ($totalMentors === 0) {
            return response()->json(['message' => 'No mentors available to assign companies.'], 400);
        }

        // Calculate companies per mentor for proportional distribution
        $companiesPerMentor = floor($totalCompanies / $totalMentors);
        $remainingCompanies = $totalCompanies % $totalMentors;

        // Initialize assignments
        $assignments = [];
        $mentors = $availableMentors->shuffle(); // Randomize mentor order
        $mentorIndex = 0;
        $companyCountAssigned = array_fill(0, $totalMentors, 0); // Track companies per mentor

        // Assign company groups to mentors
        foreach ($studentsByCompany as $company => $companyData) {
            // Assign to mentor with fewest companies
            $minCompanies = min($companyCountAssigned);
            $availableMentorIndices = array_keys($companyCountAssigned, $minCompanies);
            $mentorIndex = $availableMentorIndices[array_rand($availableMentorIndices)]; // Randomly pick among least-assigned
            $mentor = $mentors[$mentorIndex];

            // Assign all students in this company to the mentor
            foreach ($companyData['students'] as $student) {
                $assignments[] = [
                    'student_id' => $student->id,
                    'assigned_mentor_id' => $mentor->id
                ];
            }

            // Update tracking
            $companyCountAssigned[$mentorIndex]++;
            if ($companyCountAssigned[$mentorIndex] >= $companiesPerMentor + ($remainingCompanies > 0 ? 1 : 0)) {
                $remainingCompanies--;
                // Optionally remove mentor if they have enough companies
                unset($mentors[$mentorIndex]);
                $mentors = $mentors->values();
                $companyCountAssigned = array_values($companyCountAssigned);
                $totalMentors--;
            }
        }

        // Bulk update student assignments
        foreach ($assignments as $assignment) {
            Student::where('id', $assignment['student_id'])
                ->update(['assigned_mentor_id' => $assignment['assigned_mentor_id']]);
        }

        // Commit the transaction
        DB::commit();

        return response()->json([
            'message' => 'Mentors assigned successfully',
            'assigned_count' => count($assignments),
            'companies_assigned' => $studentsByCompany->keys()->toArray()
        ]);

    } catch (\Exception $e) {
        // Roll back the transaction on error
        DB::rollBack();
        Log::error('Mentor assignment failed', ['error' => $e->getMessage()]);
        return response()->json(['message' => 'Failed to assign mentors', 'error' => $e->getMessage()], 500);
    }
}


}
