<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\TaskExportController;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelPdf\Facades\Pdf;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::inertia('/', 'Welcome')->name('welcome');

/*
|--------------------------------------------------------------------------
| Team Routes
|--------------------------------------------------------------------------
|
| All team-specific resources are scoped to the current team.
|
| Examples:
|   /pulses-team/dashboard
|   /pulses-team/lists
|   /pulses-team/tasks
|
*/

Route::prefix('{current_team}')
    ->middleware([
        'auth',
        'verified',
        EnsureTeamMembership::class,
    ])
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get(
            'dashboard',
            DashboardController::class
        )->name('dashboard');
    });

/*
|--------------------------------------------------------------------------
| Team Invitations
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get(
        'invitations/{invitation}/accept',
        [TeamInvitationController::class, 'accept']
    )->name('invitations.accept');

    Route::delete(
        'invitations/{invitation}',
        [TeamInvitationController::class, 'decline']
    )->name('invitations.decline');
});

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| These routes are authenticated but are not team-scoped.
|
*/

Route::middleware([
    'auth',
    'verified',
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | About
    |--------------------------------------------------------------------------
    */

    Route::resource('about', AboutController::class)
        ->only([
            'index',
            'store',
            'update',
            'destroy',
        ])
        ->names([
            'index'   => 'about.index',
            'store'   => 'about.store',
            'update'  => 'about.update',
            'destroy' => 'about.destroy',
        ]);

    /*
    |--------------------------------------------------------------------------
    | Courses
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/courses',
        [CourseController::class, 'index']
    )->name('courses');

    /*
    |--------------------------------------------------------------------------
    | Contact
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/contact',
        [ContactController::class, 'index']
    )->name('contact');

    Route::post(
        '/contact',
        [ContactController::class, 'store']
    )->name('contact.store');

    /*
    |--------------------------------------------------------------------------
    | Properties
    |--------------------------------------------------------------------------
    */

    Route::resource('property', PropertyController::class)
        ->only([
            'index',
            'store',
            'update',
            'destroy',
        ])
        ->names([
            'index'   => 'property.index',
            'store'   => 'property.store',
            'update'  => 'property.update',
            'destroy' => 'property.destroy',
        ]);

    /*
    |--------------------------------------------------------------------------
    | Categories
    |--------------------------------------------------------------------------
    */

    Route::resource('category', CategoryController::class)
        ->only([
            'index',
            'store',
            'update',
            'destroy',
        ])
        ->names([
            'index'   => 'category.index',
            'store'   => 'category.store',
            'update'  => 'category.update',
            'destroy' => 'category.destroy',
        ]);

    /*
    |--------------------------------------------------------------------------
    | Books
    |--------------------------------------------------------------------------
    */

    Route::resource('book', BookController::class)
        ->only([
            'index',
            'store',
            'update',
            'destroy',
        ])
        ->names([
            'index'   => 'book.index',
            'store'   => 'book.store',
            'update'  => 'book.update',
            'destroy' => 'book.destroy',
        ]);

    /*
        |--------------------------------------------------------------------------
        | Lists
        |--------------------------------------------------------------------------
        */

    Route::resource('lists', ListController::class)
        ->only([
            'index',
            'store',
            'update',
            'destroy',
        ])
        ->names([
            'index'   => 'lists.index',
            'store'   => 'lists.store',
            'update'  => 'lists.update',
            'destroy' => 'lists.destroy',
        ]);

    /*
        |--------------------------------------------------------------------------
        | Tasks
        |--------------------------------------------------------------------------
        */

    Route::resource('tasks', TaskController::class)
        ->only([
            'index',
            'update',
            'destroy',
        ])
        ->names([
            'index'   => 'tasks.index',
            'update'  => 'tasks.update',
            'destroy' => 'tasks.destroy',
        ]);
    Route::post('/lists/tasks', [TaskController::class, 'store'])->name('tasks.store');
});

/*
|--------------------------------------------------------------------------
| Task export
|--------------------------------------------------------------------------
*/


Route::get('/test-pdf', function () {
    return Pdf::view('exports.test-pdf')
        ->format('a4')
        ->withBrowsershot(function ($browsershot) {
            $browsershot
                ->showBackground()
                ->timeout(120);
        })
        ->download('browsershot-test.pdf');
});



Route::get('/tasks/export', [TaskExportController::class, 'export'])
    ->name('tasks.export');
/*
|--------------------------------------------------------------------------
| Settings
|--------------------------------------------------------------------------
*/

require __DIR__ . '/settings.php';
