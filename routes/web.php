<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ShiftReportController;
use App\Http\Controllers\ShiftController;
use Illuminate\Http\Request;
use App\Http\Controllers\EditCloseShiftController;



// Guest routes (accessible without login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Logout should only be allowed if authenticated
Route::get('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');
Route::get('/shifts/{id}/edit-closed', [ShiftController::class, 'editClosedShift'])->name('shifts.edit-closed');


// Protected routes (need authentication)
// Protected routes (need authentication)
Route::middleware('auth')->group(function () {

    Route::get('/', function () {
        return view('index');
    });
   Route::get('/sale-report', function () {
        return view('sale-report');
    })->middleware('permission:sale-report');
	
	   Route::get('/received-amount', function () {
        return view('received-amount');
    })->middleware('permission:received-amount');

    // Only roles with "view_roles" permission can access
    Route::get('/roles', [RoleController::class, 'index'])
        ->name('roles.index')
        ->middleware('permission:roles');

    // Another example
    Route::get('view-overview1/{id}', function () {
        return view('view-overview1');
    });
 Route::get('audit-reports/{id}', function () {
        return view('audit-report');
    });
	
Route::get('/station-audit/{stationId?}', function ($stationId = null) {
    return view('station-audit', compact('stationId'));
})->name('station.audit')->middleware('auth');

	Route::get('/shift-reports', [ShiftReportController::class, 'index']);
    Route::get('/shift-reports/{shift_id}', [ShiftReportController::class, 'show']);
    Route::post('/shift-reports/generate', [ShiftReportController::class, 'generateReport']);
	Route::get('/edit-close-shift/{shift_id}', [EditCloseShiftController::class, 'index'])->name('edit-close-shift');

	
    // Catch-all route for your dashboard pages
   Route::get('/{any}', [DashboardController::class, 'index'])->where('any', '.*');

});
