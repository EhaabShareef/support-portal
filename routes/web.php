<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\AttachmentController;
// Livewire Components
use App\Livewire\Dashboard;
use App\Livewire\ManageOrganizations;
use App\Livewire\ViewOrganization;
use App\Livewire\ManageContracts;
use App\Livewire\ManageHardware;
use App\Livewire\CreateTicket;
use App\Livewire\ManageTickets;
use App\Livewire\ViewTicket;
use App\Livewire\Admin\ManageUsers;
use App\Livewire\Admin\ViewUser;
use App\Livewire\Admin\ManageRoles;
use App\Livewire\Admin\ManageSettings;


Route::get('/', function () {
    return view('welcome');
});


// Authentication
Route::get('login',  [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);

// Requires auth
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    Route::post('logout', [LogoutController::class, 'logout'])->name('logout');
    Route::get('session', [SessionController::class, 'index']);

    Route::get('/organizations', ManageOrganizations::class)->name('organizations.index');
    Route::get('/organizations/{organization}', ViewOrganization::class)->name('organizations.show');

    Route::get('/contracts/manage/{organization}', ManageContracts::class)->name('contracts.manage');
    Route::get('/hardware/manage/{organization}', ManageHardware::class)->name('hardware.manage');

    // Ticket Routes
    Route::get('/tickets/create', CreateTicket::class)->name('tickets.create');
    Route::get('/tickets/manage', ManageTickets::class)->name('tickets.index');
    Route::get('/tickets/{ticket}', ViewTicket::class)->name('tickets.show');

    // Attachment Routes
    Route::get('/attachments/{uuid}/download', [AttachmentController::class, 'download'])->name('attachments.download');
    Route::get('/attachments/{uuid}/view', [AttachmentController::class, 'view'])->name('attachments.view');
    Route::delete('/attachments/{uuid}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

    // Admin Routes (only for Admin role)
    Route::middleware(['role:Admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', ManageUsers::class)->name('users.index');
        Route::get('/users/{user}', ViewUser::class)->name('users.view');
        Route::get('/roles', ManageRoles::class)->name('roles.index');
        Route::get('/settings', ManageSettings::class)->name('settings');
    });

});
