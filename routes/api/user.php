<?php
/**
 * @author tmtuan
 * created Date: 01-Dec-20
 */

use App\Domains\Crm\Http\Controllers\Api\Users\UserController;
use App\Domains\Crm\Http\Controllers\Api\Users\UserKpiController;

Route::group([
    'prefix' => 'user',
    'as' => 'user.',
], function () {
    Route::options('update-profile', [UserController::class, 'handleOptions']);
    Route::options('get-user-kpi', [UserController::class, 'handleOptions']);

    Route::post('forgot-password', [UserController::class, 'forgotPass']);
    Route::post('generate-otp', [UserController::class, 'generateOTP'])->name('generate_otp');
    Route::post('verified-otp', [UserController::class, 'verifiedOTP'])->name('verified_otp');
    Route::middleware(['middleware' => 'auth.jwt'])->group(function (){
        Route::post('update-profile', [UserController::class, 'updateProfile'])->name('update_profile');

        //KPI API
        Route::get('get-pt-session', [UserKpiController::class, 'getPTSession']);
        Route::get('get-session-in-period', [UserKpiController::class, 'getPTSessionInPeriod']);
        Route::get('get-sale-br', [UserKpiController::class, 'getSaleBR']);
        Route::get('get-package-size', [UserKpiController::class, 'getPackageSize']);


        Route::post('add-user-device', [UserController::class, 'updateDevice'])->name('get_user_kpi');

        Route::get('list-sales', [UserController::class, 'listSales']);
    });
});
