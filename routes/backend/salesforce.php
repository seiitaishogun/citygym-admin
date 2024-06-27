<?php
/**
 * @author tmtuan
 * created Date: 22-Apr-21
 */

use \App\Domains\Crm\Http\Controllers\Backend\SfClass\ClassController;

Route::group([
    'prefix' => 'sf-class',
    'as' => 'sf-class.',
], function () {
    Route::get('order-class', [ClassController::class, 'order'])->name('order');
    Route::post('order-class', [ClassController::class, 'orderAction'])->name('order');

});
