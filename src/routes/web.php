<?php

use Illuminate\Support\Facades\Route;
use admin\enquiries\Controllers\EnquiryManagerController;

Route::prefix('admin')->name('admin.')->middleware(['web', 'admin.auth'])->group(function () {
    Route::post('enquiries/{enquiry}/close-status', [EnquiryManagerController::class, 'closeStatus'])->name('admin.enquiries.close-status');
    Route::resource('enquiries', EnquiryManagerController::class);
});