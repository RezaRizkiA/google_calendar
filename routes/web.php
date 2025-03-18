<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\GoogleCalendarController;
use App\Http\Controllers\RoleController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| 1. GET  '/'                => Menampilkan daftar event (index)
| 2. POST '/'                => Menyimpan event baru (create)
| 3. GET  '/{eventId}/edit'  => Form edit event (edit)
| 4. PUT  '/{eventId}'       => Memperbarui event (update)
| 5. DELETE '/{eventId}'     => Menghapus event (destroy)
|
*/

// OAUTH Client
Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('google.callback');

Route::get('/choose-role', [RoleController::class, 'showRoleForm'])->name('role.showRoleForm');
Route::post('/choose-role', [RoleController::class, 'processRoleForm'])->name('role.processRoleForm');

Route::get('/student-teacher/event', [CalendarController::class, 'showCreateForm'])
    ->name('student-teacher.showCreateForm');

Route::post('/student-teacher/event', [CalendarController::class, 'createEventForStudentAndTeacher'])
    ->name('student-teacher.createEvent');

// delete event
Route::delete('/event/{mappingId}', [CalendarController::class, 'deleteEvent'])->name('student-teacher.deleteEvent');

