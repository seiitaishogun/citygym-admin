
<?php
/**
 * @author tmtuan
 * created Date: 01-Dec-20
 */

use App\Domains\Crm\Http\Controllers\Api\PriceBook\PriceBookController;

Route::group([
    'prefix' => 'pricebook',
    'as' => 'pricebook.',
], function () {
	Route::options('list', [PriceBookController::class, 'handleOptions']);
	Route::get('list', [PriceBookController::class, 'list'])->name('get_list');

	Route::get('list-pricebooks', [PriceBookController::class, 'listPricebooks']);
    Route::get('{pricebookId}', [PriceBookController::class, 'getPricebook']);
});
