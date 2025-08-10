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
use App\Livewire\ManageUsers;
use App\Livewire\CreateTicket;
use App\Livewire\ManageTickets;
use App\Livewire\ViewTicket;
use App\Livewire\UserProfile;
use App\Livewire\Admin\ManageUsers as AdminManageUsers;
use App\Livewire\Admin\ViewUser;
use App\Livewire\Admin\ManageRoles;
use App\Livewire\Admin\ManageSettings;
use App\Livewire\Admin\UsersRoles;
use App\Livewire\ScheduleCalendar;
use App\Livewire\Admin\Reports\ReportsDashboard;
use App\Livewire\Admin\Reports\OrganizationSummaryReport;
use App\Livewire\Admin\Reports\TicketVolumeReport;


Route::get('/', function () {
    return view('welcome');
});


// Authentication
Route::get('login',  [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);

// Requires auth
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    
    // Test widget system
    Route::get('/test-widgets', function () {
        $user = auth()->user();
        $widgets = \App\Models\DashboardWidget::all();
        $userSettings = \App\Models\UserWidgetSetting::where('user_id', $user->id)->with('widget')->get();
        $visibleWidgets = $user->getVisibleWidgets();
        
        return response()->json([
            'widget_system_enabled' => config('dashboard.widgets_enabled'),
            'total_widgets' => $widgets->count(),
            'user_settings_count' => $userSettings->count(),
            'visible_widgets_count' => $visibleWidgets->count(),
            'user_role' => $user->roles->first()?->name,
            'widgets' => $widgets->map(fn($w) => [
                'key' => $w->key,
                'name' => $w->name,
                'permission' => $w->permission,
                'can_view' => $w->isVisibleForUser($user)
            ])
        ]);
    });

    Route::post('logout', [LogoutController::class, 'logout'])->name('logout');
    Route::get('session', [SessionController::class, 'index']);

    Route::get('/organizations', ManageOrganizations::class)->name('organizations.index');
    Route::get('/organizations/{organization}', ViewOrganization::class)->name('organizations.show');

    // Contract Routes
    Route::get('/contracts', function() { return redirect()->route('organizations.index'); });
    Route::get('/contracts/{contract}', function() { return redirect()->route('organizations.index'); })->name('contracts.show');
    Route::get('/contracts/manage/{organization}', ManageContracts::class)->name('contracts.manage');

    // Hardware Routes
    Route::get('/hardware', function() { return redirect()->route('organizations.index'); });
    Route::get('/hardware/{hardware}', function() { return redirect()->route('organizations.index'); })->name('hardware.show');
    Route::get('/hardware/manage/{organization}', ManageHardware::class)->name('hardware.manage');

    // User Routes (Organization-specific)
    Route::get('/users', function() { return redirect()->route('organizations.index'); });
    Route::get('/users/manage/{organization}', ManageUsers::class)->name('users.manage');

    // Ticket Routes (Protected by permissions)
    Route::middleware(['can:tickets.read'])->group(function () {
        Route::get('/tickets/manage', ManageTickets::class)->name('tickets.index');
        Route::get('/tickets/{ticket}', ViewTicket::class)->name('tickets.show');
    });
    
    Route::middleware(['can:tickets.create'])->group(function () {
        Route::get('/tickets/create', CreateTicket::class)->name('tickets.create');
    });

    // Profile Routes
    Route::get('/profile', UserProfile::class)->name('profile');
    
    // Loading overlay flag clear
    Route::post('/clear-loading-flag', function () {
        session()->forget('show_loading_overlay');
        return response()->json(['status' => 'ok']);
    })->name('clear.loading.flag');

    // Schedule Routes (Admin and Client only)
    Route::middleware(['role:admin|client'])->group(function () {
        Route::get('/schedule', ScheduleCalendar::class)->name('schedule.index');
    });

    // Attachment Routes
    Route::get('/attachments/{uuid}/download', [AttachmentController::class, 'download'])->name('attachments.download');
    Route::get('/attachments/{uuid}/view', [AttachmentController::class, 'view'])->name('attachments.view');
    Route::delete('/attachments/{uuid}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

    // Admin Routes (only for admin role)
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        // Combined Users & Roles page
        Route::get('/users-roles', UsersRoles::class)->name('users-roles.index');
        Route::get('/users/{user}', ViewUser::class)->name('users.view');
        
        // Legacy redirects for backward compatibility
        Route::get('/users', fn() => redirect()->route('admin.users-roles.index', ['tab' => 'users']))
            ->name('users.index');
        Route::get('/roles', fn() => redirect()->route('admin.users-roles.index', ['tab' => 'roles']))
            ->name('roles.index');
        
        Route::get('/settings', ManageSettings::class)->name('settings');
        Route::get('/reports', ReportsDashboard::class)->name('reports.dashboard');
        Route::get('/reports/ticket-volume', TicketVolumeReport::class)->name('reports.ticket-volume');
        Route::get('/reports/organization-summary', OrganizationSummaryReport::class)->name('reports.organization-summary');
    });

});
