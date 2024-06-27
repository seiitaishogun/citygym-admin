<?php
/**
 * @author tmtuan
 * created Date: 10-Nov-20
 */

use App\Http\Controllers\Api\AuthController;

Route::group([
    'prefix' => 'auth',
    'as' => 'auth.',
], function () {
    Route::options('login', [AuthController::class, 'handleOptions']);

    Route::post('mb-login', [AuthController::class, 'mbLogin'])->name('mb_login');

    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('reset_password');
    Route::post('mb-change-password', [AuthController::class, 'mbChangePassword'])->name('mb_change_password');

    Route::middleware(['middleware' => 'auth.jwt'])->group(function (){
        Route::get('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('users', [AuthController::class, 'getUser'])->name('users');
        Route::post('change-password', [AuthController::class, 'changePassword'])->name('change_password');
        Route::post('update-profile', [AuthController::class, 'updateProfile'])->name('update_profile');

    });
});

