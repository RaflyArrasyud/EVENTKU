<?php

use App\Http\Controllers\Admin\HelpController as AdminHelpController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\OrganizerRequestController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Organizer\CheckinController;
use App\Http\Controllers\Organizer\EventController as OrganizerEventController;
use App\Http\Controllers\Organizer\OrderController as OrganizerOrderController;
use App\Http\Controllers\Organizer\TicketTypeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
Route::get('/sertifikat/verifikasi/{kode}', [CertificateController::class, 'verify'])->name('sertifikat.verifikasi');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/ticket-types/{ticketType}/order', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/bukti', [OrderController::class, 'proof'])->name('orders.proof');
    Route::post('/orders/{order}/bukti', [OrderController::class, 'uploadBukti'])->name('orders.bukti');
    Route::post('/orders/{order}/payment/start', [PaymentController::class, 'start'])
        ->middleware('throttle:10,1')->name('payments.start');
    Route::post('/orders/{order}/payment/status', [PaymentController::class, 'status'])
        ->middleware('throttle:20,1')->name('payments.status');
    Route::get('/tickets/{ticket}/sertifikat', [CertificateController::class, 'show'])->name('tickets.certificate');
    Route::post('/organizer-request', [OrganizerRequestController::class, 'store'])->name('organizer-request.store');
    Route::post('/bantuan', [HelpController::class, 'store'])->name('bantuan.store');
    Route::get('/bantuan/pesan', [HelpController::class, 'messages'])->name('bantuan.pesan');
});

Route::middleware(['auth', 'role:organizer,admin'])
    ->prefix('organizer')
    ->name('organizer.')
    ->group(function () {
        Route::resource('events', OrganizerEventController::class)->except('show');
        Route::resource('events.ticket-types', TicketTypeController::class)
            ->only(['index', 'store', 'destroy']);
        Route::get('/checkin', [CheckinController::class, 'index'])->name('checkin');
        Route::post('/checkin', [CheckinController::class, 'store'])->name('checkin.store');
        Route::get('/orders', [OrganizerOrderController::class, 'index'])->name('orders.index');
        Route::post('/orders/{order}/approve', [OrganizerOrderController::class, 'approve'])->name('orders.approve');
        Route::post('/orders/{order}/reject', [OrganizerOrderController::class, 'reject'])->name('orders.reject');
    });

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}/role', [AdminUserController::class, 'update'])->name('users.role');
        Route::post('/organizer-requests/{request}/approve', [AdminUserController::class, 'approveRequest'])->name('organizer-requests.approve');
        Route::post('/organizer-requests/{request}/reject', [AdminUserController::class, 'rejectRequest'])->name('organizer-requests.reject');
        Route::get('/bantuan', [AdminHelpController::class, 'index'])->name('help.index');
        Route::post('/bantuan/{ticket}/balas', [AdminHelpController::class, 'reply'])->name('help.reply');
        Route::post('/bantuan/{ticket}/tutup', [AdminHelpController::class, 'close'])->name('help.close');
    });

require __DIR__.'/auth.php';

Route::post('/payments/midtrans/notification', [PaymentController::class, 'notification'])
    ->name('payments.notification');