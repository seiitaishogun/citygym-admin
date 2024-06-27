<?php
/**
 * @author tmtuan
 * created Date: 03-Dec-20
 */

use Tabuna\Breadcrumbs\Trail;
use App\Domains\TmtSfObject\Http\Controllers\Backend\SalesforceConnect;
use App\Domains\TmtSfObject\Http\Controllers\Backend\SalesforceGetObject;
use App\Domains\TmtSfObject\Http\Controllers\Api\AccountController;
use App\Domains\TmtSfObject\Http\Controllers\Api\LeadController;

Route::group([
    'prefix' => 'sf-object',
    'as' => 'sf-object.',
], function () {
    Route::get('connect', [SalesforceConnect::class, 'connect'])->name('connect');
    Route::post('connect', [SalesforceConnect::class, 'connectAction'])->name('connectAction');
    Route::get('on-connect', [SalesforceConnect::class, 'onConnect'])->name('onConnect');

    Route::get('get-object', [SalesforceGetObject::class, 'getObjectTable'])->name('getObject');
    Route::post('get-object', [SalesforceGetObject::class, 'getObjectAction'])->name('getObject');

    Route::get('get-record-type', [AccountController::class, 'getRecordType'])->name('getRecordType');

    Route::group([
        'prefix' => 'lead',
        'as' => 'lead.',
    ], function () {
        Route::get('get-stage', [LeadController::class, 'getLeadStage'])->name('getLeadStage');
    });

});

//handle salesforce callback
Route::get('salesforce/oauth2/callback', [SalesforceConnect::class, 'handleOAuthCallback'])->name('handleOAuthCallback');
