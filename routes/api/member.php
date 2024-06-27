<?php
/**
 * @author tmtuan
 * created Date: 18-Dec-20
 */

use App\Domains\Crm\Http\Controllers\Api\SupportCase\CaseController;
use App\Domains\Crm\Http\Controllers\Api\SfClass\mbClassController;
use App\Domains\Crm\Http\Controllers\Api\Search\SearchController;
use App\Domains\Acp\Http\Controllers\Api\Settings\MemberSettingController;

Route::group([
    'prefix' => 'case',
    'as' => 'case.',
], function () {
    Route::middleware(['middleware' => 'auth.jwt'])->group(function () {
        Route::get('list-cases', [CaseController::class, 'listCases'])->name('list-cases');
        Route::get('{id}', [CaseController::class, 'getCase'])->name('get-case');
    });
});

Route::group([
    'prefix' => 'class',
    'as' => 'class.',
], function () {
    Route::get('list-groups', [mbClassController::class, 'listClassGroup']);

    Route::get('list-class', [mbClassController::class, 'listClass'])->name('list-class');
    Route::get('{id}', [mbClassController::class, 'getClass'])->name('get-class');
});

Route::group([
    'prefix' => 'search',
    'as' => 'search.',
], function () {
    Route::middleware(['middleware' => 'auth.jwt'])->group(function () {
        Route::get('/', [SearchController::class, 'search']);
    });
//    Route::get('opty', [OpptyController::class, 'listOppty']);
});


Route::group([
    'prefix' => 'mb-app',
    'as' => 'mb-app.',
], function () {
    Route::get('list-btn-iframe', [MemberSettingController::class, 'listBtnSettings']);
    Route::get('list-memo', [MemberSettingController::class, 'listMemoSettings']);
    Route::get('list-contact', [MemberSettingController::class, 'listContactSettings']);
});
