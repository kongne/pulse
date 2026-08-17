<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ListController;


Route::inertia('/', 'Welcome')->name('home');

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');
    });

Route::middleware(['auth'])->group(function () {
    Route::get('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
    Route::delete('invitations/{invitation}', [TeamInvitationController::class, 'decline'])->name('invitations.decline');
});

Route::get('/about', function () {
    return Inertia::render('About');
})->name('about')->middleware(['auth', 'verified']);

//route courses
Route::get('/courses', [CourseController::class, 'index'])->name('courses');

//route contact
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

//route properties
Route::resource('property', PropertyController::class)->only(['index', 'store', 'update', 'destroy'])->names([
    'index' => 'property.index',
    'store' => 'property.store',
    'update' => 'property.update',
    'destroy' => 'property.destroy',
]);
//Route::post('/property', [PropertyController::class, 'store'])->name('property.store');
//category route

Route::resource('category', CategoryController::class)->only(['index', 'store', 'update', 'destroy'])->names([
    'index' => 'category.index',
    'store' => 'category.store',
    'update' => 'category.update',
    'destroy' => 'category.destroy',
]);

//route books
Route::resource('book', BookController::class)->only(['index', 'store', 'update', 'destroy'])->names([
    'index' => 'book.index',
    'store' => 'book.store',
    'update' => 'book.update',
    'destroy' => 'book.destroy',
]);

//route lists

Route::resource('lists', ListController::class)->only(['index', 'store', 'update', 'destroy'])->names([
    'index' => 'lists.index',
    'store' => 'lists.store',
    'update' => 'lists.update',
    'destroy' => 'lists.destroy',
]);

//route tasks
Route::resource('tasks', TaskController::class)->only(['index', 'update', 'destroy'])->names([
    'index' => 'tasks.index',
    'update' => 'tasks.update',
    'destroy' => 'tasks.destroy',
]);

Route::post('/lists/tasks', [TaskController::class, 'store'])
    ->name('lists.tasks.store');




require __DIR__ . '/settings.php';
