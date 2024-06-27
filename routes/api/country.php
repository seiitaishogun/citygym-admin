<?php
/**
 * @author tmtuan
 * created Date: 17-Nov-20
 */

use App\Domains\Country\Http\Controllers\Api\CountryController;

Route::group([
    'prefix' => 'country',
    'as' => 'country.',
], function () {
	// Route::options('import', [UserController::class, 'handleOptions']);
    Route::post('import', [CountryController::class, 'importData'])->name('import_data');
});
