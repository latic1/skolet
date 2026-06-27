<?php

use App\Http\Controllers\Central\HealthController;
use App\Http\Controllers\Central\ImpersonationController;
use App\Http\Controllers\Central\SchoolRegistrationController;
use App\Http\Controllers\Central\SuperAdminAuthController;
use App\Http\Controllers\Central\SuperAdminController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Central Web Routes (skolet.com)
|--------------------------------------------------------------------------
*/

// Health check & ping — no auth, no CSRF, no tenant middleware (uptime monitors)
Route::withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])->group(function () {
    Route::get('/health', [HealthController::class, 'check'])->name('health');
    Route::get('/ping', [HealthController::class, 'ping'])->name('ping');
});

Route::get('/', fn () => view('central.landing'))->name('home');
Route::get('/pricing', fn () => view('central.pricing'))->name('pricing');

// Domain-constrained so accra.skolet.test/login hits the tenant route instead.
Route::domain(preg_replace('/^www\./i', '', parse_url(config('app.url'), PHP_URL_HOST) ?? 'skolet.com'))
    ->get('/login', fn () => view('central.login'))
    ->name('login');

Route::get('/register-school', [SchoolRegistrationController::class, 'create'])->name('register-school');
Route::post('/register-school', [SchoolRegistrationController::class, 'store'])->middleware('throttle:3,60')->name('register-school.store');

Route::get('/sitemap.xml', function () {
    $content = view('central.sitemap');
    return response($content, 200)->header('Content-Type', 'application/xml');
})->name('sitemap');

Route::get('/robots.txt', function () {
    $content = "User-agent: *\nAllow: /\nDisallow: /super-admin\nSitemap: " . url('/sitemap.xml');
    return response($content, 200)->header('Content-Type', 'text/plain');
})->name('robots');

// Super Admin auth (central DB, super_admin guard)
Route::get('/super-admin/login', [SuperAdminAuthController::class, 'showLogin'])->name('super-admin.login');
Route::post('/super-admin/login', [SuperAdminAuthController::class, 'login'])->name('super-admin.login.post');
Route::post('/super-admin/logout', [SuperAdminAuthController::class, 'logout'])->name('super-admin.logout');

// Super Admin protected routes
Route::middleware(['auth:super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/', [SuperAdminController::class, 'index'])->name('dashboard');
    Route::post('/sync-students', [SuperAdminController::class, 'syncStudentCounts'])->name('sync-students');
    Route::post('/tenants/{tenant}/impersonate', [ImpersonationController::class, 'start'])->name('tenants.impersonate');
    Route::patch('/tenants/{tenant}/toggle', [SuperAdminController::class, 'toggleStatus'])->name('tenants.toggle');
    Route::patch('/tenants/{tenant}/rate', [SuperAdminController::class, 'updateRate'])->name('tenants.rate');
    Route::patch('/tenants/{tenant}/mark-paid', [SuperAdminController::class, 'markPaid'])->name('tenants.mark-paid');
    Route::patch('/tenants/{tenant}/mark-unpaid', [SuperAdminController::class, 'markUnpaid'])->name('tenants.mark-unpaid');
    Route::delete('/tenants/{tenant}', [SuperAdminController::class, 'destroyTenant'])->name('tenants.destroy');
    Route::get('/broadcasts', [SuperAdminController::class, 'broadcasts'])->name('broadcasts');
    Route::post('/broadcasts', [SuperAdminController::class, 'storeBroadcast'])->name('broadcasts.store');
    Route::get('/audit-log', [SuperAdminController::class, 'auditLog'])->name('audit-log');
    Route::get('/audit-log/export', [SuperAdminController::class, 'exportAuditLog'])->name('audit-log.export');
    Route::get('/tenants/{tenant}', [SuperAdminController::class, 'tenantDetail'])->name('tenants.detail');
    Route::get('/tenants/{tenant}/invoices/{payment}', [SuperAdminController::class, 'downloadInvoice'])->name('tenants.invoices.download');
    Route::post('/analytics/rebuild', [SuperAdminController::class, 'rebuildAnalytics'])->name('analytics.rebuild');
});
