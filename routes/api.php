<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () {
    // Compte
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', fn (Request $r) => $r->user());
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/password', [ProfileController::class, 'password']);

    // Dashboard et "mes tâches"
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/my-tasks', [TaskController::class, 'mine']);

    // Boards et membres
    Route::apiResource('boards', BoardController::class);
    Route::post('/boards/{board}/members', [BoardController::class, 'addMember']);
    Route::delete('/boards/{board}/members/{user}', [BoardController::class, 'removeMember']);

    // Tâches
    Route::get('/boards/{board}/tasks', [TaskController::class, 'index']);
    Route::post('/boards/{board}/tasks', [TaskController::class, 'store']);
    Route::put('/tasks/{task}', [TaskController::class, 'update']);
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);

    // Notes
    Route::get('/tasks/{task}/notes', [NoteController::class, 'index']);
    Route::post('/tasks/{task}/notes', [NoteController::class, 'store']);
    Route::delete('/notes/{note}', [NoteController::class, 'destroy']);

    // Fichiers
    Route::get('/tasks/{task}/attachments', [AttachmentController::class, 'index']);
    Route::post('/tasks/{task}/attachments', [AttachmentController::class, 'store']);
    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download']);
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy']);

    // Checklist
    Route::get('/tasks/{task}/checklist', [ChecklistController::class, 'index']);
    Route::post('/tasks/{task}/checklist', [ChecklistController::class, 'store']);
    Route::put('/checklist/{item}', [ChecklistController::class, 'update']);
    Route::delete('/checklist/{item}', [ChecklistController::class, 'destroy']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'read']);
});
