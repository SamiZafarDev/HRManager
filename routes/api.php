<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocManagerController;
use App\Http\Controllers\EmailHandlerController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


// Route::post('/uploadProfilePic', [UserController::class, 'uploadProfilePic'])->name('uploadProfilePic');
Route::post('/login', [AuthController::class, 'loginUserApi']);
Route::post('/registerUserapi', [AuthController::class, 'registerUser']);

Route::middleware('auth:sanctum')
    ->group(function () {
        Route::post('/uploadProfilePic', [UserController::class, 'uploadProfilePic'])->name('uploadProfilePic');
        Route::post('/deleteProfilePic', [UserController::class, 'deleteProfilePic'])->name('deleteProfilePic');
        Route::post('/getUrlProfilePic', [UserController::class, 'getUrlProfilePic'])->name('getUrlProfilePic');

        Route::post('/document/upload', [DocManagerController::class, 'upload']);
        Route::post('/document/delete', [DocManagerController::class, 'delete']);

        Route::delete('/document/deleteMyDocuments', [DocManagerController::class, 'deleteMyDocuments']);

        Route::get('/rankDocuments', [DocManagerController::class, 'rankDocuments']);


        // Route::get('/getMyDocuments', [DocManagerController::class, 'getMyDocuments'])->name('getMyDocuments');
        // Route::get('/document-details', [DocManagerController::class, 'getDocumentDetails'])->name('document.details');


    });
Route::post('/chatWithAI', [DocManagerController::class, 'chatWithAI']);

Route::get('/testSortResponseInRanks', [DocManagerController::class, 'testSortResponseInRanks']);

Route::post('/sendEmailscheduleInterview', [DocManagerController::class, 'sendEmailscheduleInterview']);

Route::post('/sendEmailTo', [EmailHandlerController::class, 'sendEmail']);


