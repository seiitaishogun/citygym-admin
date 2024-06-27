<?php
/**
 * @author tmtuan
 * created Date: 01-Dec-20
 */

use App\Domains\Crm\Http\Controllers\Api\Lead\LeadController;
use App\Domains\Crm\Http\Controllers\Api\Lead\CreateLeadController;
use App\Domains\Crm\Http\Controllers\Api\Lead\EditLeadController;

Route::group([
    'prefix' => 'lead',
    'as' => 'lead.',
], function () {
    Route::get('list-lead-status', [EditLeadController::class, 'listStatus']);
    Route::get('list-lost-reason', [LeadController::class, 'listLostReason']);

    Route::middleware(['middleware' => 'auth.jwt'])->group(function (){
        Route::get('list-lead', [LeadController::class, 'listLead'])->name('get_lead');
        Route::get('list-lead-paginate', [LeadController::class, 'listLeadWithPaginate'])->name('get_lead_paginate');
        Route::get('count-by-stage', [LeadController::class, 'countStatusLead'])->name('count_stage');
        Route::post('create-lead', [CreateLeadController::class, 'addLead'])->name('create_lead');
        Route::post('edit-lead/{id}', [EditLeadController::class, 'editLead'])->name('edit_lead');

        Route::group(['prefix' => '{lead}'], function () {
            Route::post('edit-lead/{id}', [EditLeadController::class, 'editLead'])->name('create_lead');
        });

        Route::post('create-business-lead', [CreateLeadController::class, 'addBusinessLead']); //tạo lead Corporate
        Route::get('convert-lead/{id}', [LeadController::class, 'convertLead']);

    });
});
