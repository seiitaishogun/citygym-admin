<?php
/**
 * @author tmtuan
 * created Date: 29-Mar-21
 */

use App\Domains\Acp\Http\Controllers\Backend\PushNotification\Notification;

Route::group([
    'prefix' => 'noti',
    'as' => 'noti.',
], function () {
    Route::get('/{app_type}', [Notification::class, 'showNotiForm'])->name('add');
    Route::post('send-noti', [Notification::class, 'sendPush'])->name('send');

});
