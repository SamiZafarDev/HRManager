<?php

use App\Http\Controllers\AISettingsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocManagerController;
use App\Http\Controllers\EmailHandlerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InterviewDetailsController;
use App\Http\Controllers\InterviewScheduleController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/user', [UserController::class, 'index']);

// Auth
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/loginUser', [AuthController::class, 'loginUser'])->name('loginUser');
Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::post('/registerUser', [AuthController::class, 'registerUser'])->name('registerUser');

// Home
Route::get('/home', [HomeController::class, 'home'])->name('home');

// Document Management
Route::post('/document/uploadForm', [DocManagerController::class, 'upload'])->name('uploadDocumentForm');
Route::get('/document/preview/{filename}', [DocManagerController::class, 'previewDocument'])->name('document.preview');
Route::get('/documents', function () {
    return view('documentsListing');
})->name('documents.list');

// AI Settings
Route::get('/ai-settings', [AISettingsController::class, 'index'])->name('ai.settings.index');
Route::post('/ai-settings-store', [AISettingsController::class, 'store'])->name('ai.settings.store');
Route::get('/ai-settings-get', [AISettingsController::class, 'get'])->name('ai.settings.get');
Route::get('/promptInput', function () {
    return view('promptInput');
})->name('promptInput');

// Ranked Documents
Route::get('/rankDocuments', [DocManagerController::class, 'rankDocuments'])->name('rankDocuments');
Route::get('/rankedDocuments', function () {
    return view('/rankedDocuments/rankDocumentsDisplayView');
})->name('rankedDocuments');

// Interview Details and Schedules
Route::resource('interviewDetails', InterviewDetailsController::class)->except(['show']);
Route::resource('interviewSchedules', InterviewScheduleController::class);

Route::middleware(['auth'])->group(function () {
    Route::get('/document-details', [DocManagerController::class, 'getDocumentDetails'])->name('document.details');
    Route::get('/getMyDocuments', [DocManagerController::class, 'getMyDocuments'])->name('getMyDocuments');
    Route::delete('/document/delete', [DocManagerController::class, 'delete'])->name('document.delete');
    Route::post('/sendEmailscheduleInterview', [DocManagerController::class, 'sendEmailscheduleInterview'])->name('sendEmailscheduleInterview');
});

// Email
Route::middleware(['auth'])->group(function () {
    Route::get('/email-handler', [EmailHandlerController::class, 'index'])->name('emailHandler.index');
    Route::post('/email-handler-store', [EmailHandlerController::class, 'store'])->name('emailHandler.store');
    Route::get('/email-handler-get', [EmailHandlerController::class, 'get'])->name('emailHandler.get');
});
