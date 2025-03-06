<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\RequestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Authentication Route
Route::post('/login', [AuthController::class, 'login']);

// Home Route
Route::get('/', function () {
    return view('welcome');
});

// Choose Courses Route with Token Verification
Route::get('/choose-courses/{instructor}', [RequestController::class, 'showChooseCoursesPage'])
    ->name('choose-courses');

// Assignments Route for Course Selection
Route::get('/assignments/choose-courses/{id}', [AssignmentController::class, 'showChooseCoursesPage'])
    ->name('assignments.choose');

// Store Course Choices Route
Route::post('/assignments/store-choices', [RequestController::class, 'storeChoices'])
    ->name('assignments.storeChoices');

    Route::get('/success', function () {
    return view('success'); // Blade file for the success page
})->name('success.page');