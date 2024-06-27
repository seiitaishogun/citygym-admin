<?php
/**
 * @author tmtuan
 * created Date: 01-Dec-20
 */
use App\Http\Controllers\Api\ApiAppController;
use App\Domains\Crm\Http\Controllers\Api\Account\AccountController;
use App\Domains\Crm\Http\Controllers\Api\Source\SourceController;
use App\Domains\Crm\Http\Controllers\Api\Notification\SaleNotification;
use App\Domains\Crm\Http\Controllers\Api\Notification\MbNotification;

Route::group([
    'prefix' => 'app',
    'as' => 'app.',
], function () {
//    Route::options('update-profile', [UserController::class, 'handleOptions']);

    Route::middleware(['middleware' => 'auth.jwt'])->group(function (){
        Route::get('get-kpi', [ApiAppController::class, 'getKPI'])->name('get_home');
        Route::get('get-list-lead', [ApiAppController::class, 'getListLead'])->name('get_home');

    });

    Route::get('list-source', [SourceController::class, 'listSource'])->name('list_source');
});

Route::group([
    'prefix' => 'account',
    'as' => 'account.',
], function () {
    Route::middleware(['middleware' => 'auth.jwt'])->group(function () {
        Route::get('list-account', [AccountController::class, 'listAccount'])->name('list-account');
        Route::get('get-account', [AccountController::class, 'getAccount'])->name('get-account');
        Route::get('list-pts', [AccountController::class, 'getPt']);
    });
});

Route::group([
    'prefix' => 'notification',
    'as' => 'notification.',
], function () {
    Route::middleware(['middleware' => 'auth.jwt'])->group(function () {
        Route::get('get-noti/{id}', [SaleNotification::class, 'getNotification']);

        Route::get('list-sale-noti', [SaleNotification::class, 'listNotification']);
        Route::get('list-member-noti', [MbNotification::class, 'listNotification']);

        Route::post('add-sale-noti', [SaleNotification::class, 'createSaleNoti']);

    });
});
