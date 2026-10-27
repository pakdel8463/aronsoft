<?php

use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

/*
| سیستم تیکتینگ — ناحیهٔ مشتری. (پنل مدیریت: routes/admin.php → panel.tickets.*)
| create بدون middleware auth است تا مهمان با پیام مناسب به ورود هدایت شود.
*/
Route::pattern('ticket', 'TKT-[0-9]+');

Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');

Route::middleware('auth')->prefix('tickets')->name('tickets.')->group(function () {
    Route::get('/', [TicketController::class, 'index'])->name('index');
    Route::post('/', [TicketController::class, 'store'])->middleware('throttle:5,10')->name('store');
    Route::get('/{ticket}', [TicketController::class, 'show'])->name('show');
    Route::post('/{ticket}/reply', [TicketController::class, 'reply'])->middleware('throttle:12,10')->name('reply');
    Route::post('/{ticket}/close', [TicketController::class, 'close'])->middleware('throttle:20,10')->name('close');
    Route::post('/{ticket}/reopen', [TicketController::class, 'reopen'])->middleware('throttle:10,10')->name('reopen');
    Route::post('/{ticket}/rate', [TicketController::class, 'rate'])->middleware('throttle:10,10')->name('rate');
    Route::get('/{ticket}/attachments/{attachment}', [TicketController::class, 'attachment'])
        ->whereNumber('attachment')->middleware('throttle:60,1')->name('attachment');
});
