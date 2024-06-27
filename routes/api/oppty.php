<?php
/**
 * @author tmtuan
 * created Date: 15-Dec-20
 */

use App\Domains\Crm\Http\Controllers\Api\Oppty\OpptyController;
use \App\Domains\Crm\Http\Controllers\Api\Oppty\EditOpptyController;
use App\Domains\Crm\Http\Controllers\Api\Oppty\ProductOpptyController;
use App\Domains\Crm\Http\Controllers\Api\Oppty\GiftOpptyController;
use App\Domains\Crm\Http\Controllers\Api\Oppty\PromoOpptyController;
use App\Domains\Crm\Http\Controllers\Api\Oppty\ClubAccessController;
use App\Domains\Crm\Http\Controllers\Api\Oppty\OpptySaleController;
use \App\Domains\Crm\Http\Controllers\Api\Oppty\ProductCorporateController;

Route::group([
    'prefix' => 'oppty',
    'as' => 'oppty.',
], function () {
    Route::middleware(['middleware' => 'auth.jwt'])->group(function (){
        Route::get('list-stage-count', [OpptyController::class, 'listStageCount']);
        Route::get('list-stage', [OpptyController::class, 'listStage']);

    	Route::get('list-oppties', [OpptyController::class, 'listOppty'])->name('list-oppties');
        Route::get('list-oppties-paginate', [OpptyController::class, 'listOpptyPaginate'])->name('list-oppties-paginate');
        Route::get('get-oppty/{id}', [OpptyController::class, 'getOppty'])->name('get-oppty');
        Route::post('edit/{id}', [EditOpptyController::class, 'editOppty'])->name('edit-oppty');
        Route::get('convert/{id}', [OpptyController::class, 'convertOppty'])->name('convert-oppty');
        // Route::post('edit', [OpptyController::class, 'updateOppty'])->name('edit_oppty');

        //add product to oppty
        Route::get('get-products', [ProductOpptyController::class, 'getProduct']);
        Route::get('get-pricebook', [ProductOpptyController::class, 'getPriceBookEntry']);
        Route::post('save-product', [ProductOpptyController::class, 'saveProductToOppty']);

        //add product corporate
        Route::post('save-product-corporate', [ProductOpptyController::class, 'saveCorporateProduct']);

        //list corporate pricebook
        Route::group([
            'prefix' => 'corporate',
            'as' => 'corporate.',
        ], function () {
            Route::get('list-data/{id}', [ProductCorporateController::class, 'listCorpData']);
        });


        /**
         * Referral
         * https://beunik.atlassian.net/browse/CIT-649
         */
        Route::group([
            'prefix' => 'referral',
            'as' => 'referral.',
        ], function () {
            Route::get('list-promo', [PromoOpptyController::class, 'listRefPromo']);  //Lấy Promotion có Record Type là Referral
            Route::get('list-promo-item/{id}', [PromoOpptyController::class, 'listRefPromoItem']); //Lấy Promotion Item có Record Type là Referral_Buyer
        });

        //add gift to oppty
        Route::get('product-gifts/{id}', [GiftOpptyController::class, 'getGift']);
        Route::post('save-gift', [GiftOpptyController::class, 'saveGift']);

        //add promotion to oppty
        Route::get('get-promos', [PromoOpptyController::class, 'getPromos']);
        Route::get('get-promo-item/{promo}', [PromoOpptyController::class, 'getPromoItems']);
        Route::post('save-promo', [PromoOpptyController::class, 'savePromo']);
        Route::post('delete-opportunity-line-item/{id}', [OpptyController::class, 'deleteOpportunityLineItem'])->name('delete-opportunity-line-item');

        //club access
        Route::get('get-club-access/{id}', [ClubAccessController::class, 'listClubAccess']);
        Route::post('save-club-access', [ClubAccessController::class, 'saveClubAccess']);

        //add sale to opty
        Route::get('get-oppty-sales', [OpptySaleController::class, 'listOpptySale']);
        Route::post('add-sale', [OpptySaleController::class, 'addSale']);
        Route::post('delete-sale', [OpptySaleController::class, 'deleteOptySale']);

    });
});
