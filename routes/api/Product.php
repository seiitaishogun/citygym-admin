<?php
/**
 * @author tmtuan
 * created Date: 22-Dec-20
 */

use App\Domains\Crm\Http\Controllers\Api\Promotion\PromotionController;
use App\Domains\Crm\Http\Controllers\Api\Product\ProductController;

Route::group([
    'prefix' => 'promotion',
    'as' => 'promotion.',
], function () {
    Route::middleware(['middleware' => 'auth.jwt'])->group(function () {
        Route::get('list-promotion', [PromotionController::class, 'listPromotions']);
        Route::get('{promotionId}', [PromotionController::class, 'getPromotion']);

        Route::group(['prefix' => '{promotionId}'], function () {
            Route::get('list-item', [PromotionController::class, 'getPromotionItemOfPromotion'])->name('get_promotion_item');
        });
    });
});

Route::group([
    'prefix' => 'product',
    'as' => 'product.',
], function () {
	Route::get('list', [ProductController::class, 'listProducts'])->name('get_list');
	Route::get('{id}', [ProductController::class, 'get'])->name('get_product');
});
