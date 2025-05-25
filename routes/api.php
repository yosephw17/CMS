<?php

use App\Http\Controllers\ChoiceController;
use App\Http\Controllers\CourseController;
use Illuminate\Http\Request;

use App\Http\Controllers\MentorController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\YearController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\InstructorRoleController;
use App\Http\Controllers\YearSemesterCourseController;
use App\Http\Controllers\EvaluationLinkController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\TimetableController;
use App\Http\Controllers\QualityQuestionController;
use App\Http\Controllers\QualityLinkController;
use App\Http\Controllers\QualityResponseController;
use App\Http\Controllers\EvaluatorController;
use App\Http\Controllers\AuditSessionController;
use App\Http\Controllers\StudentController;


use App\Http\Controllers\InstructorTimeSlotController;

use App\Http\Controllers\InstructorController;

use App\Http\Controllers\FieldController;
use App\Http\Controllers\ProfessionalExperienceController;
use App\Http\Controllers\ResearchController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\EducationalBackgroundController;
use App\Http\Controllers\ParameterController;
use App\Http\Controllers\EvaluationCategoryController;

use App\Http\Controllers\MentorshipController;
use App\Http\Controllers\AcademicYearController;

use App\Http\Controllers\RequestController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\SectionController;

use App\Models\EducationalBackground;

use App\Http\Controllers\UserController;
use App\Http\Controllers\ScheduleController;

use App\Http\Controllers\GuestInstructorController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\StreamController;
use App\Http\Controllers\QualityAssuranceEvaluatorController;



/*
|----------------------------------------------------------------------
| API Routes
|----------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider and are assigned
| to the "api" middleware group. You can register additional routes
| here that require authentication.
|
*/

Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['message' => 'Sanctum active']);
});
 // Public evaluation endpoints
 Route::prefix('evaluate')->group(function () {
    Route::get('/{hash}', [EvaluationController::class, 'getForm']);
    Route::post('/{hash}', [EvaluationController::class, 'submit']);
});
Route::prefix('quality-form')->group(function () {
    Route::get('/{hash}', [QualityResponseController::class, 'getForm']);
    Route::post('/{hash}', [QualityResponseController::class, 'submit']);
});
Route::get('/audit-sessions', [AuditSessionController::class, 'index']);
Route::get('/get-all-quality-responses', [QualityResponseController::class, 'getAllResponses']);
Route::get('/get-grouped-responses', [QualityResponseController::class, 'getGroupedResponses']);

Route::post('/timetable/generate', [TimetableController::class, 'generate']);
Route::get('/time-slots', [TimetableController::class, 'fetch']);
Route::patch('/time-slots/{timeSlot}/toggle-break', [TimetableController::class, 'toggleBreak']);

// Public Routes for Authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::apiResource('courses', CourseController::class);
Route::post('/choices/bulk', [ChoiceController::class, 'bulkStore']);
Route::apiResource('instructors', InstructorController::class)->middleware('permission:instructor-list');
Route::get('assignments/latest', [AssignmentController::class, 'latest']);
Route::post('/request', [RequestController::class,'store']);
Route::post('/send-message', [MentorController::class,'store']);
Route::get('/choice-assignment/{id}', [ChoiceController::class,'fetchAssignmentChoice']);
Route::get('/quality-questions', [QualityQuestionController::class,'index']);
Route::post('/quality-questions', [QualityQuestionController::class,'store']);
Route::put('quality-questions/{qualityQuestion}', [QualityQuestionController::class, 'update']);
Route::delete('quality-questions/{qualityQuestion}', [QualityQuestionController::class, 'destroy']);

Route::post('/quality-links', [QualityLinkController::class,'store']);
Route::get('/quality-links',[QualityLinkController::class,'index']);
Route::post('/quality-responses', [QualityResponseController::class, 'store']);
Route::get('/quality-responses/{hash}', [QualityResponseController::class, 'show']);
Route::get('/quality-responses/status/{hash}', [QualityResponseController::class, 'checkLinkStatus']);
Route::apiResource('results', ResultController::class);

// Protected Routes that require Sanctum authentication
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::resource('evaluators', EvaluatorController::class);

    Route::get('quality-assurance-evaluators', [QualityAssuranceEvaluatorController::class,'index']);
    Route::post('quality-assurance-evaluators', [QualityAssuranceEvaluatorController::class,'store']);
    Route::put('/quality-assurance-evaluators/{qualityAssuranceEvaluator}', [QualityAssuranceEvaluatorController::class, 'update']);
    Route::delete('/quality-assurance-evaluators/{qualityAssuranceEvaluator}', [QualityAssuranceEvaluatorController::class, 'destroy']);
    Route::get('/choices', [ChoiceController::class,'index']);
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('users', UserController::class);
    Route::post('/evaluation-links', [EvaluationLinkController::class, 'generate']);
    Route::get('/get-evaluation-links', [EvaluationLinkController::class,'groupedEvaluationLinks']);
    Route::get('/get-evaluation-links-without-grouping', [EvaluationLinkController::class,'getLink']);

    Route::get('/get-response',[EvaluationController::class,'getGroupResponses']);
    Route::get('/get-questions',[EvaluationCategoryController::class, 'getCategoriesWithQuestions']);


    Route::apiResource('instructor-time-slots', InstructorTimeSlotController::class);
    Route::delete('/instructor-time-slots', [InstructorTimeSlotController::class, 'destroy']);


    Route::post('/upload-csv', [MentorshipController::class, 'uploadCSV']);
Route::post('/assign-mentors', [MentorshipController::class, 'assignMentors']);
Route::put('/assign-mentors/{id}', [MentorshipController::class, 'update']);
Route::get('/students', [MentorshipController::class, 'index']);
Route::post('/students', [StudentController::class, 'store']);

// Route::apiResource('academic-years', AcademicYearController::class);
Route::get('/academic-years', [AcademicYearController::class, 'index']);


        Route::post('/instructor-roles', [InstructorRoleController::class, 'store']); // Create role
        Route::delete('/instructor-roles/{id}', [InstructorRoleController::class, 'destroy']); // Create role
        Route::get('/instructor-roles', [InstructorRoleController::class, 'index']); // Get all roles
        Route::patch('/instructor-roles/{id}', [InstructorRoleController::class, 'update']); // Get all roles
        Route::get('/years', [YearController::class, 'index']);
        Route::get('/semesters', [SemesterController::class, 'index']);

        Route::resource('year-semester-courses', YearSemesterCourseController::class);
        Route::get('/year-semester-courses-with-year-semester', [YearSemesterCourseController::class, 'findByYearAndSemester']);


    Route::apiResource('fields', FieldController::class);
    Route::apiResource('professional-experiences', ProfessionalExperienceController::class);
    Route::apiResource('educational-backgrounds', EducationalBackgroundController::class);
    Route::resource('researches', ResearchController::class);
    Route::resource('assignments', AssignmentController::class);
    Route::resource('parameters', ParameterController::class);
    Route::post('/assignments/{id}/assign-courses', [AssignmentController::class, 'assignCourses']);
    Route::get('/assignments/{id}', [AssignmentController::class, 'show'])->name('assignments.show');
    Route::put('/assignments/edit/{id}', [AssignmentController::class, 'assignmentUpdate']);

    Route::apiResource('departments', DepartmentController::class);
    Route::apiResource('sections', SectionController::class);
    Route::apiResource('schedules', ScheduleController::class);
    Route::apiResource('guest-instructors', GuestInstructorController::class);

    Route::post('/schedules/generate', [ScheduleController::class, 'generateSchedule']);
    Route::get('/schedules/{scheduleId}/results', [ScheduleController::class, 'getScheduleResults']);
    Route::get('/time_slots', [ScheduleController::class, 'getTimeSlots']);
    Route::get('/days', [ScheduleController::class, 'getDays']);
    Route::get('/rooms', [RoomController::class, 'index']);
    Route::patch('/year-semester-course/{id}', [YearSemesterCourseController::class, 'updatePreferredRooms']);
    Route::apiResource('streams', StreamController::class);
});
