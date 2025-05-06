<?php

use App\Http\Controllers\AISettingsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardManagerController;
use App\Http\Controllers\DocManagerController;
use App\Http\Controllers\EmailHandlerController;
use App\Http\Controllers\InterviewDetailsController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


// Route::post('/uploadProfilePic', [UserController::class, 'uploadProfilePic'])->name('uploadProfilePic');
Route::post('/login', [AuthController::class, 'loginUserApi']);
Route::post('/register', [AuthController::class, 'registerUser']);

Route::middleware('auth:sanctum')
    ->group(function () {
        Route::post('/uploadProfilePic', [UserController::class, 'uploadProfilePic'])->name('uploadProfilePic');
        Route::post('/deleteProfilePic', [UserController::class, 'deleteProfilePic'])->name('deleteProfilePic');
        Route::post('/getUrlProfilePic', [UserController::class, 'getUrlProfilePic'])->name('getUrlProfilePic');
        Route::get('/get-user', [UserController::class, 'getUserProfile'])->name('getUser'); // Get user details
        Route::put('/edit-user', [UserController::class, 'updateUserProfile'])->name('editUser'); // Edit user details

        // Document Management Routes
        Route::post('/document/upload', [DocManagerController::class, 'upload']);
        Route::post('/document/delete', [DocManagerController::class, 'delete']);
        Route::get('/document/my', [DocManagerController::class, 'getMyDocuments']);
        Route::get('/document-details', [DocManagerController::class, 'getDocumentDetails']);
        Route::delete('/document/delete-my-documents', [DocManagerController::class, 'deleteMyDocuments']);
        Route::get('/document/preview/{filename}', [DocManagerController::class, 'previewDocument']);
        Route::get('/document/user/{user_id}', [DocManagerController::class, 'getDocumentsOfUser']);
        Route::delete('/document/delete', [DocManagerController::class, 'deleteDocuments']);

        // Document Ranking and AI Routes
        Route::get('/rankDocuments', [DocManagerController::class, 'rankDocuments']);
        Route::post('/send-email-schedule-interview', [DocManagerController::class, 'sendEmailscheduleInterview']);
        Route::post('/send-to-ai', [DocManagerController::class, 'sendToAI']);

        // Interview Details and Schedules
        Route::get('/interview-details-get', [InterviewDetailsController::class, 'get']);
        Route::get('/interview-details-store', [InterviewDetailsController::class, 'store']);
        Route::get('/interview-details-update', [InterviewDetailsController::class, 'update']);
        Route::get('/interview-details-delete', [InterviewDetailsController::class, 'destroy']);
        // Route::resource('/interviewSchedules', InterviewScheduleController::class);

        // AI Settings
        Route::post('/ai-settings-store', [AISettingsController::class, 'store']);
        Route::get('/ai-settings-get', [AISettingsController::class, 'get']);

        // Dashboard
        Route::get('/get-dashboard-data', [DashboardManagerController::class, 'getDashboardData']);

        // Edit user details
        Route::post('/password/change', [AuthController::class, 'changePassword'])->middleware('auth:sanctum')->name('password.change'); // Change password for authenticated users
    });
Route::post('/chatWithAI', [DocManagerController::class, 'chatWithAI']);
Route::get('/testSortResponseInRanks', [DocManagerController::class, 'testSortResponseInRanks']);
// Route::post('/sendEmailscheduleInterview', [DocManagerController::class, 'sendEmailscheduleInterview']);
Route::post('/sendEmailTo', [EmailHandlerController::class, 'sendEmail']);


// Reset Password APIs
Route::post('/password/request-reset', [AuthController::class, 'requestPasswordReset'])->name('password.requestReset'); // Request password reset
Route::post('/password/reset', [AuthController::class, 'resetPassword'])->name('password.reset'); // Reset password
