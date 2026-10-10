<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminHostelController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BedController;
use App\Http\Controllers\EsslController;
use App\Http\Controllers\HostelController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PublicPaymentController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomTypeController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public / Marketing Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn() => view('view.home'))->name('home');
Route::get('/about', fn() => view('view.about'))->name('about');
Route::get('/rooms', fn() => view('view.rooms'))->name('rooms');
Route::get('/gallery', fn() => view('view.gallery'))->name('gallery');

Route::get('/contact', fn() => view('view.contact'))->name('contact');
Route::get('/contacts', fn() => view('view.contatct'))->name('contact.submit');

Route::get('/privacy-policy', fn() => view('view.privacy'))->name('privacy');
Route::get('/terms', fn() => view('view.terms'))->name('terms');
Route::get('/refund-policy', fn() => view('view.refund-policy'))->name('refund.policy');

/*
|--------------------------------------------------------------------------
| Public Hostel / Property Routes
|--------------------------------------------------------------------------
*/

Route::get('/hostels', [HostelController::class, 'index'])->name('hostels.index');
Route::get('/hostels/alandur', [HostelController::class, 'alandur'])->name('hostels.alandur');
Route::get('/hostels/perungalathur', [HostelController::class, 'perungalathur'])->name('hostels.perungalathur');

Route::get('/hostels/{area}/{slug}', [HostelController::class, 'property'])
    ->whereIn('area', ['alandur', 'perungalathur'])
    ->name('hostels.property');

/*
|--------------------------------------------------------------------------
| Sitemap
|--------------------------------------------------------------------------
*/

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'submitLogin'])->name('login.submit');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'submitRegister'])->name('register.submit');

    Route::get('/forget', [AuthController::class, 'shownForget'])->name('forgetemail');
    Route::post('/forget', [AuthController::class, 'submitForget'])->name('sendForget');

    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'submitResetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Account (authenticated user) Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('account')->name('account.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // ----- Hostels -----
    Route::prefix('hostels')->name('hostels.')->group(function () {
        Route::get('/',               [AdminHostelController::class, 'index'])->name('index');
        Route::post('/',              [AdminHostelController::class, 'store'])->name('store');
        Route::get('/{id}',           [AdminHostelController::class, 'show'])->name('show');
        Route::put('/{id}',           [AdminHostelController::class, 'update'])->name('update');
        Route::delete('/{id}',        [AdminHostelController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle-status', [AdminHostelController::class, 'toggleStatus'])->name('toggle-status');
    });

    // ----- Room Types -----
    Route::prefix('room-types')->name('room-types.')->group(function () {
        Route::get('/',               [RoomTypeController::class, 'index'])->name('index');
        Route::post('/',              [RoomTypeController::class, 'store'])->name('store');
        Route::get('/{id}',           [RoomTypeController::class, 'show'])->name('show');
        Route::put('/{id}',           [RoomTypeController::class, 'update'])->name('update');
        Route::delete('/{id}',        [RoomTypeController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle-status', [RoomTypeController::class, 'toggleStatus'])->name('toggle-status');
    });

    // ----- Rooms -----
    Route::prefix('rooms')->name('rooms.')->group(function () {
        Route::get('/',      [RoomController::class, 'index'])->name('index');
        Route::post('/',     [RoomController::class, 'store'])->name('store');
        Route::get('/{id}',  [RoomController::class, 'show'])->name('show');
        Route::put('/{id}',  [RoomController::class, 'update'])->name('update');
        Route::delete('/{id}', [RoomController::class, 'destroy'])->name('destroy');
    });

    // ----- Beds -----
    Route::prefix('beds')->name('beds.')->group(function () {
        Route::get('/',      [BedController::class, 'index'])->name('index');
        Route::post('/',     [BedController::class, 'store'])->name('store');
        Route::get('/{id}',  [BedController::class, 'show'])->name('show');
        Route::put('/{id}',  [BedController::class, 'update'])->name('update');
        Route::delete('/{id}', [BedController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle-status', [BedController::class, 'toggleStatus'])->name('toggle-status');
    });

   Route::prefix('residents')->name('residents.')->group(function () {

        // 🔑 SPECIFIC ROUTES FIRST
        Route::patch(
            '/regenerate-all-employee-codes',
            [ResidentController::class, 'regenerateAllEmployeeCodes']
        )->name('regenerate-all-employee-codes');

        Route::get(
            '/vacant-beds/{roomId}',
            [ResidentController::class, 'getVacantBeds']
        )->name('vacant-beds');

        // 🔑 EXPORT ROUTES
        Route::get('/export/excel', [ResidentController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/pdf',   [ResidentController::class, 'exportPdf'])->name('export.pdf');

        // 🏠 VACANCY / ALLOCATION REPORT
        Route::get(
            '/vacancy-report',
            [ResidentController::class, 'vacancyReport']
        )->name('vacancy-report');

        Route::get(
            '/vacancy-report/export/excel',
            [ResidentController::class, 'exportVacancyExcel']
        )->name('vacancy-report.export.excel');

        Route::get(
            '/vacancy-report/export/pdf',
            [ResidentController::class, 'exportVacancyPdf']
        )->name('vacancy-report.export.pdf');

        // 🔑 LIVE FILTER (AJAX endpoint — returns JSON)
        Route::get('/filter', [ResidentController::class, 'filter'])->name('filter');

        // ----- Standard CRUD -----
        Route::get('/',      [ResidentController::class, 'index'])->name('index');
        Route::post('/',     [ResidentController::class, 'store'])->name('store');

        Route::get('/{id}',  [ResidentController::class, 'show'])->name('show');
        Route::put('/{id}',  [ResidentController::class, 'update'])->name('update');
        Route::delete('/{id}', [ResidentController::class, 'destroy'])->name('destroy');

        Route::patch('/{id}/vacate', [ResidentController::class, 'vacate'])->name('vacate');
        Route::patch('/{id}/reactivate', [ResidentController::class, 'reactivate'])->name('reactivate');
        Route::patch('/{id}/regenerate-employee-code', [ResidentController::class, 'regenerateEmployeeCode'])->name('regenerate-employee-code');
    });


    // ----- Payments -----
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/',                 [PaymentController::class, 'index'])->name('index');
        Route::post('/filter',          [PaymentController::class, 'filter'])->name('filter');
        Route::get('/export/csv',       [PaymentController::class, 'exportCsv'])->name('export.csv');
        Route::get('/export/pdf',       [PaymentController::class, 'exportPdf'])->name('export.pdf');

        Route::get('/rooms/{hostelId}',   [PaymentController::class, 'roomsByHostel'])->name('rooms');
        Route::get('/residents/{roomId}', [PaymentController::class, 'residentsByRoom'])->name('residents');

        Route::post('/',     [PaymentController::class, 'store'])->name('store');
        Route::get('/{id}',  [PaymentController::class, 'show'])->name('show');
        Route::put('/{id}',  [PaymentController::class, 'update'])->name('update');
        Route::delete('/{id}', [PaymentController::class, 'destroy'])->name('destroy');
    });

    // ----- Payment Links -----
    Route::prefix('payment-links')->name('payment-links.')->group(function () {
        Route::get('/', [PublicPaymentController::class, 'index'])->name('index');
    });
});

/*
|--------------------------------------------------------------------------
| Public Payment Routes
|--------------------------------------------------------------------------
*/


Route::get('/pay/success', [PublicPaymentController::class, 'success'])
    ->name('public.payment.success');

Route::post('/pay/axis/callback', [PublicPaymentController::class, 'callback'])
    ->name('public.payment.callback')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

Route::get('/pay/{encodedHostelId}', [PublicPaymentController::class, 'show'])
    ->name('public.payment.show');

Route::post('/pay/{encodedHostelId}/lookup', [PublicPaymentController::class, 'lookup'])
    ->name('public.payment.lookup');

Route::post('/pay/{encodedHostelId}/initiate', [PublicPaymentController::class, 'initiate'])
    ->name('public.payment.initiate');
/*
|--------------------------------------------------------------------------
| Essl / Biometric Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])
    ->prefix('admin/essl')
    ->name('admin.essl.')
    ->group(function () {

        Route::get('/residents', [EsslController::class, 'residents'])->name('residents');
        Route::get(
            '/get-residents',
            [EsslController::class, 'getResidents']
        )->name('get-residents');

        Route::get('/command-status', [EsslController::class, 'commandStatus'])->name('command-status');

        Route::post('/resident/sync',      [EsslController::class, 'syncResident'])->name('resident.sync');
        Route::post('/hostel/sync',        [EsslController::class, 'syncHostel'])->name('hostel.sync');
        Route::post('/resident/bulk-sync', [EsslController::class, 'bulkSync'])->name('resident.bulk-sync');

        Route::post('/resident/block',      [EsslController::class, 'blockUser'])->name('resident.block');
        Route::post('/resident/bulk-block', [EsslController::class, 'bulkBlock'])->name('resident.bulk-block');
    });


use App\Http\Controllers\EsslTestController;

Route::middleware(['auth'])->prefix('essl-test')->name('essl-test.')->group(function () {
    Route::get('/',              [EsslTestController::class, 'index'])->name('index');
    Route::post('/add-all',      [EsslTestController::class, 'addAll'])->name('add-all');
    Route::post('/add-one/{id}', [EsslTestController::class, 'addOne'])->name('add-one');
    Route::get('/command/{id}',  [EsslTestController::class, 'command'])->name('command');
});
