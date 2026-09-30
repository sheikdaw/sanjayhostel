<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BedController;
use App\Http\Controllers\HostelController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\RoomTypeController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/about', function () {
    return view('view.about');
})->name('about');

Route::get('/rooms', function () {
    return view('view.rooms');
})->name('rooms');
Route::get('/gallery', function () {
    return view('view.gallery');
})->name('gallery');
Route::get('/contacts', function () {
    return view('view.contatct');
})->name('contact');
Route::get('/contact', function () {
    return view('view.contatct');
})->name('contact.submit');
Route::get('/privacy-policy', function () {
    return view('view.privacy');
})->name('privacy');
Route::get('/terms', function () {
    return view('view.terms');
})->name('terms');
Route::get('/refund-policy', function () {
    return view('view.refund-policy');
})->name('refund.policy');


// ============================================================
// AUTH ROUTES
// ============================================================
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

Route::middleware(['auth'])->prefix('account')->name('account.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
});
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    Route::prefix('hostels')->name('hostels.')->group(function () {
        Route::get('/', [HostelController::class, 'index'])->name('index');
        Route::post('/', [HostelController::class, 'store'])->name('store');
        Route::get('/{id}', [HostelController::class, 'show'])->name('show');
        Route::put('/{id}', [HostelController::class, 'update'])->name('update');
        Route::delete('/{id}', [HostelController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle-status', [HostelController::class, 'toggleStatus'])->name('toggle-status');
    });


    Route::prefix('room-types')->name('room-types.')->group(function () {
        Route::get('/', [RoomTypeController::class, 'index'])->name('index');
        Route::post('/', [RoomTypeController::class, 'store'])->name('store');
        Route::get('/{id}', [RoomTypeController::class, 'show'])->name('show');
        Route::put('/{id}', [RoomTypeController::class, 'update'])->name('update');
        Route::delete('/{id}', [RoomTypeController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle-status', [RoomTypeController::class, 'toggleStatus'])->name('toggle-status');
    });




    // Rooms
    Route::prefix('rooms')->name('rooms.')->group(function () {
        Route::get('/', [RoomController::class, 'index'])->name('index');
        Route::post('/', [RoomController::class, 'store'])->name('store');
        Route::get('/{id}', [RoomController::class, 'show'])->name('show');
        Route::put('/{id}', [RoomController::class, 'update'])->name('update');
        Route::delete('/{id}', [RoomController::class, 'destroy'])->name('destroy');
    });

    // Beds
    Route::prefix('beds')->name('beds.')->group(function () {
        Route::get('/', [BedController::class, 'index'])->name('index');
        Route::post('/', [BedController::class, 'store'])->name('store');
        Route::get('/{id}', [BedController::class, 'show'])->name('show');
        Route::put('/{id}', [BedController::class, 'update'])->name('update');
        Route::delete('/{id}', [BedController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle-status', [BedController::class, 'toggleStatus'])->name('toggle-status');
    });


    Route::prefix('residents')->name('residents.')->group(function () {
        Route::get('/', [ResidentController::class, 'index'])->name('index');
        Route::post('/', [ResidentController::class, 'store'])->name('store');
        Route::get('/{id}', [ResidentController::class, 'show'])->name('show');
        Route::put('/{id}', [ResidentController::class, 'update'])->name('update');
        Route::delete('/{id}', [ResidentController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/vacate', [ResidentController::class, 'vacate'])->name('vacate');
        Route::patch('/{id}/reactivate', [ResidentController::class, 'reactivate'])->name('reactivate');
        Route::get('/vacant-beds/{roomId}', [ResidentController::class, 'getVacantBeds'])->name('vacant-beds');
    });


    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::post('/filter', [PaymentController::class, 'filter'])->name('filter');
        Route::get('/export/csv', [PaymentController::class, 'exportCsv'])->name('export.csv');
        Route::get('/export/pdf', [PaymentController::class, 'exportPdf'])->name('export.pdf');

        Route::post('/', [PaymentController::class, 'store'])->name('store');
        Route::get('/{id}', [PaymentController::class, 'show'])->name('show');
        Route::put('/{id}', [PaymentController::class, 'update'])->name('update');
        Route::delete('/{id}', [PaymentController::class, 'destroy'])->name('destroy');
    });
});
use App\Http\Controllers\PublicPaymentController;

// Public payment lookup (anyone with encoded link can access)
Route::get('/pay/{encodedHostelId}', [PublicPaymentController::class, 'show'])
    ->name('public.payment.show');

Route::post('/pay/{encodedHostelId}/lookup', [PublicPaymentController::class, 'lookup'])
    ->name('public.payment.lookup');

Route::get('/pay/success', [PublicPaymentController::class, 'success'])
    ->name('public.payment.success');
Route::prefix('payment-links')->name('payment-links.')->group(function () {
    Route::get('/', [PublicPaymentController::class, 'index'])->name('index');
});
