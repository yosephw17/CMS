<?php
use App\Http\Controllers\CourseController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\YearController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\InstructorRoleController;
use App\Http\Controllers\YearSemesterCourseController;

use App\Http\Controllers\InstructorController;

use App\Http\Controllers\CourseController;
use App\Http\Controllers\FieldController;
use App\Http\Controllers\ProfessionalExperienceController;
use App\Http\Controllers\ResearchController;
use App\Http\Controllers\AssignmentController;



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
// Public Routes for Authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes that require Sanctum authentication
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
   
        Route::post('/instructor-roles', [InstructorRoleController::class, 'store']); // Create role
        Route::delete('/instructor-roles/{id}', [InstructorRoleController::class, 'destroy']); // Create role
        Route::get('/instructor-roles', [InstructorRoleController::class, 'index']); // Get all roles
        Route::patch('/instructor-roles/{id}', [InstructorRoleController::class, 'update']); // Get all roles
        Route::get('/years', [YearController::class, 'index']);
        Route::get('/semesters', [SemesterController::class, 'index']);
      
        Route::resource('year-semester-courses', YearSemesterCourseController::class);
        Route::get('/year-semester-courses-with-year-semester', [YearSemesterCourseController::class, 'findByYearAndSemester']);
        
   
    Route::apiResource('instructors', InstructorController::class);
    Route::apiResource('courses', CourseController::class);
    Route::apiResource('fields', FieldController::class);
    Route::apiResource('professional-experiences', ProfessionalExperienceController::class);
    Route::resource('researches', ResearchController::class);
    Route::resource('assignments', AssignmentController::class);
    Route::post('/assignments/{id}/assign-courses', [AssignmentController::class, 'assignCourses']);


});
