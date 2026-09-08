<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\BedController;
use App\Http\Controllers\Admin\HostelController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ResidentController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\RoomTypeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\AdvanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GuestHostelController;
use App\Http\Controllers\BiometricController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\FaceController;
use App\Http\Controllers\GuestPaymentController;
use App\Http\Controllers\PhonePeController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\UPIController;
use Illuminate\Support\Facades\Http;

Route::get('/test', function () {
    return view('biometric.dashboard');
});

// ============================================================
// BIOMETRIC API ROUTES
// ============================================================
Route::prefix('api/test')->group(function () {
    // Resident Sync
    Route::post('/sync-single', [BiometricController::class, 'syncSingle']);
    Route::post('/sync-all', [BiometricController::class, 'syncAll']);

    // Door Access
    Route::post('/punch', [BiometricController::class, 'punch']);
    Route::get('/check-payment/{id}', [BiometricController::class, 'checkPayment']);
    Route::get('/daily-check', [BiometricController::class, 'dailyCheck']);

    // Attendance
    Route::get('/attendance', [BiometricController::class, 'attendance']);
    Route::get('/employee-punch-logs', [BiometricController::class, 'employeePunchLogs']);

    // Device Management
    Route::get('/device', [BiometricController::class, 'deviceStatus']);
    Route::get('/devices', [BiometricController::class, 'deviceList']);
    Route::post('/unlock-door', [BiometricController::class, 'unlockDoor']);
    Route::post('/block-user', [BiometricController::class, 'blockUser']);

    // Stats & Listings
    Route::get('/stats', [BiometricController::class, 'stats']);
    Route::get('/residents', [BiometricController::class, 'residentsList']);

    // eBioServer Direct
    Route::get('/connection', [BiometricController::class, 'testConnection']);
    Route::get('/employee-codes', [BiometricController::class, 'getEmployeeCodes']);
    Route::get('/employee-details', [BiometricController::class, 'getEmployeeDetails']);
    Route::post('/delete-employee', [BiometricController::class, 'deleteEmployee']);

    // Visitor
    Route::post('/validate-visitor', [BiometricController::class, 'validateVisitor']);
});

// ============================================================
// FRONTEND ROUTES
// ============================================================
Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/about', function () {
    return view('about');
})->name('about');

Route::get('/rooms', function () {
    return view('rooms');
})->name('rooms');

Route::get('/gallery', function () {
    return view('gallery');
})->name('gallery');

Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::get('/privacy-policy', function () {
    return view('privacy');
})->name('privacy');
Route::get('/terms', function () {
    return view('terms');
})->name('terms');
Route::get('/refund-policy', function () {
    return view('refund-policy');
})->name('refund.policy');

Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');

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

// ============================================================
// ADMIN ROUTES
// ============================================================
Route::middleware(['auth'])->group(function () {
    Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    });

    Route::middleware(['auth', 'role:account'])->prefix('account')->name('account.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    });

    Route::prefix('admin')->name('admin.')->group(function () {
        // ============================================================
        // HOSTEL BIOMETRIC MANAGEMENT
        // ============================================================
        Route::prefix('hostels')->name('hostels.')->group(function () {
            // Biometric Configuration
            Route::get('/biometric-config', [HostelController::class, 'biometricConfig'])->name('biometric-config');
            Route::get('/{id}/biometric-config', [HostelController::class, 'getBiometricConfig'])->name('get-biometric-config');
            Route::post('/{id}/biometric-config', [HostelController::class, 'saveBiometricConfig'])->name('save-biometric-config');

            // Biometric Sync
            Route::post('/{id}/sync-biometric', [HostelController::class, 'syncHostelBiometric'])->name('sync-biometric');
            Route::post('/sync-all-biometric', [HostelController::class, 'syncAllHostelsBiometric'])->name('sync-all-biometric');

            // Test Connection
            Route::get('/{id}/test-connection', [HostelController::class, 'testBiometricConnection'])->name('test-connection');

            // Biometric Stats
            Route::get('/biometric-stats', [HostelController::class, 'getBiometricStats'])->name('biometric-stats');
        });

        // ============================================================
        // 1. HOSTEL MANAGEMENT
        // ============================================================
        Route::get('/hostels', [HostelController::class, 'index'])->name('hostels.index');
        Route::post('/hostels', [HostelController::class, 'store'])->name('hostels.store');
        Route::get('/hostels/{id}/edit', [HostelController::class, 'edit'])->name('hostels.edit');
        Route::put('/hostels/{id}', [HostelController::class, 'update'])->name('hostels.update');
        Route::delete('/hostels/{id}', [HostelController::class, 'destroy'])->name('hostels.destroy');
        Route::patch('/hostels/{id}/toggle-status', [HostelController::class, 'toggleStatus'])->name('hostels.toggle-status');

        // ============================================================
        // 2. ROOM TYPE MANAGEMENT
        // ============================================================
        Route::prefix('room-types')->name('room-types.')->group(function () {
            Route::get('/', [RoomTypeController::class, 'index'])->name('index');
            Route::post('/', [RoomTypeController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [RoomTypeController::class, 'edit'])->name('edit');
            Route::put('/{id}', [RoomTypeController::class, 'update'])->name('update');
            Route::delete('/{id}', [RoomTypeController::class, 'destroy'])->name('destroy');
            Route::patch('/{id}/toggle-status', [RoomTypeController::class, 'toggleStatus'])->name('toggle-status');
            Route::get('/hostel/{hostelId}', [RoomTypeController::class, 'getRoomTypesByHostel'])->name('by-hostel');
            Route::post('/bulk-delete', [RoomTypeController::class, 'bulkDelete'])->name('bulk-delete');
            Route::post('/bulk-status', [RoomTypeController::class, 'bulkStatus'])->name('bulk-status');
            Route::get('/statistics', [RoomTypeController::class, 'getStatistics'])->name('statistics');
            Route::get('/export', [RoomTypeController::class, 'export'])->name('export');
        });

        // ============================================================
        // 3. ROOM MANAGEMENT
        // ============================================================
        Route::prefix('rooms')->name('rooms.')->group(function () {
            Route::get('/', [RoomController::class, 'index'])->name('index');
            Route::post('/', [RoomController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [RoomController::class, 'edit'])->name('edit');
            Route::put('/{id}', [RoomController::class, 'update'])->name('update');
            Route::delete('/{id}', [RoomController::class, 'destroy'])->name('destroy');
            Route::patch('/{id}/toggle-status', [RoomController::class, 'toggleStatus'])->name('toggle-status');
            Route::get('/hostel/{hostelId}/types', [RoomController::class, 'getRoomTypes'])->name('types');
            Route::get('/hostel/{hostelId}/rooms', [RoomController::class, 'getRoomsByHostel'])->name('by-hostel');
            Route::post('/bulk-delete', [RoomController::class, 'bulkDelete'])->name('bulk-delete');
            Route::post('/bulk-status', [RoomController::class, 'bulkStatus'])->name('bulk-status');
            Route::get('/statistics', [RoomController::class, 'getStatistics'])->name('statistics');
            Route::get('/export', [RoomController::class, 'export'])->name('export');
        });

        // ============================================================
        // 4. BED MANAGEMENT
        // ============================================================
        Route::prefix('beds')->name('beds.')->group(function () {
            Route::get('/', [BedController::class, 'index'])->name('index');
            Route::post('/', [BedController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [BedController::class, 'edit'])->name('edit');
            Route::put('/{id}', [BedController::class, 'update'])->name('update');
            Route::delete('/{id}', [BedController::class, 'destroy'])->name('destroy');
            Route::patch('/{id}/toggle-status', [BedController::class, 'toggleStatus'])->name('toggle-status');
            Route::get('/room/{roomId}', [BedController::class, 'getBedsByRoom'])->name('by-room');
            Route::get('/room/{roomId}/available', [BedController::class, 'getAvailableBeds'])->name('available');
            Route::post('/bulk-create', [BedController::class, 'bulkCreate'])->name('bulk-create');
            Route::post('/bulk-delete', [BedController::class, 'bulkDelete'])->name('bulk-delete');
            Route::post('/bulk-status', [BedController::class, 'bulkStatus'])->name('bulk-status');
            Route::get('/statistics', [BedController::class, 'getStatistics'])->name('statistics');
            Route::get('/export', [BedController::class, 'export'])->name('export');
        });

        // ============================================================
        // 5. RESIDENT MANAGEMENT
        // ============================================================
        Route::prefix('residents')->name('residents.')->group(function () {
            Route::get('/', [ResidentController::class, 'index'])->name('index');
            Route::post('/', [ResidentController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [ResidentController::class, 'edit'])->name('edit');
            Route::put('/{id}', [ResidentController::class, 'update'])->name('update');
            Route::delete('/{id}', [ResidentController::class, 'destroy'])->name('destroy');
            Route::patch('/{id}/toggle-status', [ResidentController::class, 'toggleStatus'])->name('toggle-status');

            // AJAX Routes
            Route::post('/get-rooms', [ResidentController::class, 'getHostelRooms'])->name('get-rooms');
            Route::get('/room/{id}/beds', [ResidentController::class, 'getBeds'])->name('get-beds');
            Route::get('/room/{id}/details', [ResidentController::class, 'getRoomDetails'])->name('room-details');
            Route::get('/{id}/documents', [ResidentController::class, 'getResidentDocuments'])->name('documents');

            // Bulk Operations
            Route::post('/bulk-delete', [ResidentController::class, 'bulkDelete'])->name('bulk-delete');
            Route::post('/bulk-status', [ResidentController::class, 'bulkStatus'])->name('bulk-status');

            // Biometric Routes
            Route::post('/sync-all-biometric', [ResidentController::class, 'syncAllToBiometric'])->name('sync-all-biometric');
            Route::post('/{id}/sync-to-biometric', [ResidentController::class, 'syncToBiometric'])->name('sync-to-biometric');
            Route::post('/{id}/toggle-biometric', [ResidentController::class, 'toggleBiometricAccess'])->name('toggle-biometric');
            Route::get('/biometric-list', [ResidentController::class, 'biometricList'])->name('biometric-list');
            Route::get('/{id}/biometric-status', [ResidentController::class, 'biometricStatus'])->name('biometric-status');

            // Export
            Route::get('/export', [ResidentController::class, 'export'])->name('export');

            // Details API
            Route::get('/{id}/details', [ResidentController::class, 'getResidentDetails'])->name('details');
        });

        // ============================================================
        // RESIDENT HELPER ROUTES
        // ============================================================
        Route::get('/resident/{residentId}/rent', [PaymentController::class, 'getResidentRent'])->name('resident-rent');
        Route::get('/resident/{residentId}/check-pending/{month}/{year}', [PaymentController::class, 'checkPreviousPending'])->name('check-pending');
        Route::post('/residents/{id}/profile-image', [ResidentController::class, 'updateProfileImage'])->name('residents.update-profile-image');
        Route::delete('/residents/{id}/profile-image', [ResidentController::class, 'removeProfileImage'])->name('residents.remove-profile-image');
        Route::get('/resident/{residentId}/partial-details/{month}/{year}', [PaymentController::class, 'getPartialPaymentDetails'])->name('partial-details');

        // ============================================================
// 6. PAYMENT MANAGEMENT - COMPLETE
// ============================================================
Route::prefix('payments')->name('payments.')->group(function () {
    // ---------- MAIN CRUD ROUTES ----------
    Route::get('/', [PaymentController::class, 'index'])->name('index');
    Route::post('/', [PaymentController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [PaymentController::class, 'edit'])->name('edit');
    Route::put('/{id}', [PaymentController::class, 'update'])->name('update');
    Route::delete('/{id}', [PaymentController::class, 'destroy'])->name('destroy');

    // ---------- RESIDENT-SPECIFIC ROUTES ----------
    Route::get('/resident/{residentId}/rent', [PaymentController::class, 'getResidentRent'])->name('resident-rent');
    Route::get('/resident/{residentId}/check-paid/{month}/{year}', [PaymentController::class, 'checkAlreadyPaid'])->name('check-paid');
    Route::get('/resident/{residentId}/check-pending/{month}/{year}', [PaymentController::class, 'checkPreviousPending'])->name('check-pending');
    Route::post('/resident/{residentId}/payment-details', [PaymentController::class, 'getPaymentDetails'])->name('payment-details');

    // ---------- HELPER ROUTES ----------
    Route::get('/room/{roomId}/residents', [PaymentController::class, 'getResidentsByRoom'])->name('room.residents');

    // ---------- STATUS UPDATE ROUTES ----------
    Route::post('/{id}/mark-paid', [PaymentController::class, 'markAsPaid'])->name('mark-paid');

    // ---------- BULK OPERATIONS ----------
    Route::post('/bulk', [PaymentController::class, 'bulkPayment'])->name('bulk');
    Route::post('/bulk-status', [PaymentController::class, 'bulkStatus'])->name('bulk-status');
    Route::post('/bulk-delete', [PaymentController::class, 'bulkDelete'])->name('bulk-delete');

    // ---------- EXPORT ROUTES ----------
    Route::get('/export/filtered', [PaymentController::class, 'exportFiltered'])->name('export.filtered');
    Route::get('/export/pdf', [PaymentController::class, 'exportPdf'])->name('export.pdf');

    // ---------- ROOMS BY HOSTEL ----------
    Route::get('/rooms/hostel/{hostelId}/rooms', [PaymentController::class, 'getRoomsByHostel'])->name('rooms.by.hostel');
});

        // ============================================================
        // 7. USER MANAGEMENT
        // ============================================================
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/{id}', [UserController::class, 'update'])->name('update');
            Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
            Route::patch('/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
            Route::get('/{id}/hostels', [UserController::class, 'getHostels'])->name('hostels');
            Route::get('/hostel/{hostelId}', [UserController::class, 'getUsersByHostel'])->name('by-hostel');
            Route::get('/role/{role}', [UserController::class, 'getUsersByRole'])->name('by-role');
            Route::get('/assigned-hostels', [UserController::class, 'getAssignedHostels'])->name('assigned-hostels');
            Route::post('/profile/update', [UserController::class, 'updateProfile'])->name('profile.update');
        });

        // ============================================================
        // 8. EMPLOYEE MANAGEMENT
        // ============================================================
        Route::prefix('employees')->name('employees.')->group(function () {
            Route::get('/', [EmployeeController::class, 'index'])->name('index');
            Route::get('/export', [EmployeeController::class, 'export'])->name('export');
            Route::post('/bulk-status', [EmployeeController::class, 'bulkStatus'])->name('bulk-status');
            Route::post('/bulk-delete', [EmployeeController::class, 'bulkDelete'])->name('bulk-delete');
            Route::post('/store', [EmployeeController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [EmployeeController::class, 'edit'])->name('edit');
            Route::get('/{id}', [EmployeeController::class, 'show'])->name('show');
            Route::put('/{id}', [EmployeeController::class, 'update'])->name('update');
            Route::delete('/{id}', [EmployeeController::class, 'destroy'])->name('destroy');
            Route::patch('/{id}/toggle-status', [EmployeeController::class, 'toggleStatus'])->name('toggle-status');
        });

        // ============================================================
        // 9. ATTENDANCE MANAGEMENT
        // ============================================================
        Route::prefix('attendances')->name('attendances.')->group(function () {
            Route::get('/', [AttendanceController::class, 'index'])->name('index');
            Route::get('/create', [AttendanceController::class, 'create'])->name('create');
            Route::post('/store', [AttendanceController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [AttendanceController::class, 'edit'])->name('edit');
            Route::put('/{id}', [AttendanceController::class, 'update'])->name('update');
            Route::delete('/{id}', [AttendanceController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-delete', [AttendanceController::class, 'bulkDelete'])->name('bulk-delete');
            Route::post('/bulk-mark', [AttendanceController::class, 'markBulkAttendance'])->name('bulk-mark');
            Route::get('/report', [AttendanceController::class, 'report'])->name('report');
        });

        // ============================================================
        // 10. ADVANCE MANAGEMENT
        // ============================================================
        Route::prefix('advances')->name('advances.')->group(function () {
            Route::get('/', [AdvanceController::class, 'index'])->name('index');
            Route::get('/monthly', [AdvanceController::class, 'processMonthly'])->name('monthly');
            Route::get('/{id}/history', [AdvanceController::class, 'history'])->name('history');
            Route::post('/take', [AdvanceController::class, 'takeAdvance'])->name('take');
            Route::post('/deduct', [AdvanceController::class, 'deductAdvance'])->name('deduct');
        });
    });
});

// ============================================================
// FACE DETECTION ROUTES
// ============================================================
Route::get('/web', [FaceController::class, 'index']);
Route::post('/detect-face', [FaceController::class, 'detect'])->name('face.detect');

// ============================================================
// CACHE CLEAR ROUTE
// ============================================================
Route::get('/clear-cache', function () {
    Artisan::call('optimize:clear');
    return nl2br(Artisan::output()) . "<br><br>✅ All cache cleared successfully.";
});

// ============================================================
// GUEST PAYMENT ROUTES
// ============================================================
Route::prefix('guest/payment')->name('guest.payment.')->group(function () {
    // Main page
    Route::get('/{encodedId?}', [GuestPaymentController::class, 'index'])->name('index');
    
    // API endpoints
    Route::post('/resident', [GuestPaymentController::class, 'getResident'])->name('resident');
    Route::post('/create-order', [GuestPaymentController::class, 'createOrder'])->name('create-order');
    Route::post('/verify', [GuestPaymentController::class, 'verifyPayment'])->name('verify');
    Route::get('/callback', [GuestPaymentController::class, 'callback'])->name('callback');
    Route::get('/cancel', [GuestPaymentController::class, 'cancel'])->name('cancel');
    Route::get('/status', [GuestPaymentController::class, 'status'])->name('status');
    Route::post('/webhook', [GuestPaymentController::class, 'webhook'])->name('webhook');
    
    // Utility endpoints
    Route::get('/generate-link/{hostelId}', [GuestPaymentController::class, 'generateLink'])->name('generate-link');
    Route::get('/encode/{hostelId}', [GuestPaymentController::class, 'encodeId'])->name('encode');
    Route::get('/decode/{encodedId}', [GuestPaymentController::class, 'decodeId'])->name('decode');
    Route::get('/history/{residentId}', [GuestPaymentController::class, 'getPaymentHistory'])->name('history');
    Route::get('/resident-due', [GuestPaymentController::class, 'getResidentDue'])->name('resident-due');
});

// ============================================================
// PAYMENT LINKS GENERATOR
// ============================================================
Route::get('/payment-links', function () {
    $hostels = \App\Models\Hostel::where('status', 'ACTIVE')->get();
    $encodedLinks = [];
    foreach ($hostels as $hostel) {
        $encodedLinks[$hostel->id] = url('/guest/payment/' . \Illuminate\Support\Facades\Crypt::encryptString($hostel->id));
    }
    return view('admin.payment-links', compact('hostels', 'encodedLinks'));
})->name('admin.payment-links');

// ============================================================
// DEVICE CHECK ROUTE
// ============================================================
Route::get('/check-device-service', [BiometricController::class, 'testConnection']);

// ============================================================
// GUEST HOSTEL ROUTES
// ============================================================
Route::prefix('guest')->name('guest.')->group(function () {
    // Hostel view
    Route::get('/hostel/{encodedId}', [GuestHostelController::class, 'show'])->name('hostel.show');
    
    // Payment routes
    Route::post('/payment/details', [GuestHostelController::class, 'getResidentDetails'])->name('payment.details');
    Route::post('/payment/manual', [GuestHostelController::class, 'manualPayment'])->name('payment.manual');
    Route::get('/payment/history/{residentId}', [GuestHostelController::class, 'getPaymentHistory'])->name('payment.history');
    
    // Profile Image routes
    Route::post('/resident/profile-image', [GuestHostelController::class, 'updateProfileImage'])->name('resident.update-profile-image');
    Route::post('/resident/profile-image/remove', [GuestHostelController::class, 'removeProfileImage'])->name('resident.remove-profile-image');
    
    // DOB Update route
    Route::post('/resident/update-dob', [GuestHostelController::class, 'updateDob'])->name('resident.update-dob');
});