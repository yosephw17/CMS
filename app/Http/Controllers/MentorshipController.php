<?php

namespace App\Http\Controllers;

use App\Models\Instructor;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;

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

        // Validate the uploaded file to ensure it's a CSV or TXT file
        $request->validate([
            'file' => 'required|mimes:csv,txt'
        ]);
        // Get the uploaded CSV file
        $csvFile = $request->file('file');
        
        // Read the CSV file using League CSV Reader
        $csv = Reader::createFromPath($csvFile->getRealPath(), 'r');
        $csv->setHeaderOffset(0); // Set the header offset to ensure we get the header row

        // Read and process CSV rows
        $students = [];
        foreach ($csv as $row) {
            

            $students[] = [
                'full_name' => $row['full_name'],  // Updated column name
                'department' => $row['department'],
                'phone_number' => $row['phone_number'],
                'sex' => $row['sex'] ?? null, // Optional column, default to null if missing
                'hosting_company' => $row['hosting_company'] ?? null,
                'location' => $row['location'] ?? null,
            ];
        }

        // Save students to the database
        foreach ($students as $studentData) {
            // Use 'updateOrCreate' to prevent duplicate phone numbers and update existing students
            Student::updateOrCreate(
                ['phone_number' => $studentData['phone_number']], // Unique identifier
                [
                    'full_name' => $studentData['full_name'],  // Updated column name
                    'department' => $studentData['department'],
                    'sex' => $studentData['sex'],
                    'hosting_company' => $studentData['hosting_company'],
                    'location' => $studentData['location'],
                ]
            );
        }

        return response()->json(['message' => 'CSV uploaded and students stored successfully']);
    }

    public function assignMentors()
    {
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
    
        $totalStudents = $students->count();
        $totalMentors = $availableMentors->count();
    
        // Calculate the number of students per mentor
        $studentsPerMentor = floor($totalStudents / $totalMentors);
        $remainingStudents = $totalStudents % $totalMentors;
    
        // Assign students to mentors equally (chunk the students)
        $studentsForMentors = $students->chunk($studentsPerMentor);
    
        $index = 0;
        $remaining = collect(); // Store remaining students here
    
        foreach ($availableMentors as $mentor) {
            // Assign the chunk of students to the mentor
            $studentsForThisMentor = $studentsForMentors[$index] ?? collect();
    
            foreach ($studentsForThisMentor as $student) {
                $student->assigned_mentor_id = $mentor->id;
                $student->save();
            }
    
            $index++;
        }
    
        // Assign remaining students to random mentors
        if ($remainingStudents > 0) {
            $remainingStudentsCollection = $students->slice(-$remainingStudents); // Get the remaining students
    
            foreach ($remainingStudentsCollection as $student) {
                $randomMentor = $availableMentors->random(); // Get a random mentor
                $student->assigned_mentor_id = $randomMentor->id;
                $student->save();
            }
        }
    
        return response()->json(['message' => 'Mentors assigned successfully']);
    }
    
     
}
