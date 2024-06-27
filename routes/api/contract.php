<?php
/**
 * @author tmtuan
 * created Date: 22-Dec-20
 */
use App\Domains\Crm\Http\Controllers\Api\Contract\ContractController;
use App\Domains\Crm\Http\Controllers\Api\Contract\ContractSaleController;
use App\Domains\Crm\Http\Controllers\Api\Contract\EditContractController;
use App\Domains\Crm\Http\Controllers\Api\Contract\ContractTrialController;

Route::group([
    'prefix' => 'contract',
    'as' => 'contract.',
], function () {
	Route::get('test', [ContractSaleController::class, 'test']);
    Route::middleware(['middleware' => 'auth.jwt'])->group(function () {
        Route::get('list-contract', [ContractController::class, 'listContract'])->name('list-contract');

        Route::get('detail/{id}', [ContractController::class, 'getContract'])->name('getContract');
        Route::get('detailcms/{id}', [ContractController::class, 'getContractLocal'])->name('getContract');

        Route::get('list-sale-contract', [ContractSaleController::class, 'listSaleContract'])->name('list-saleContract');
        Route::get('list-sale-contract-cms', [ContractSaleController::class, 'listSaleContractLocal'])->name('list-saleContract');

        Route::post('{id}/submit-contract', [ContractController::class, 'submitContract'])->name('submit-contract');
        Route::post('edit/{id}', [EditContractController::class, 'editContract'])->name('edit-contract');

        //contract trial
        Route::get('list-pdt-trial', [ContractTrialController::class, 'listProdTrial']);
        Route::post('create-pdt-trial', [ContractTrialController::class, 'createProdTrial']);

        //add sale to contract
        Route::post('add-sale', [ContractSaleController::class, 'addSale']);

        Route::get('list_member_no_action', [ContractSaleController::class, 'list_member_no_action']);

    });
});
