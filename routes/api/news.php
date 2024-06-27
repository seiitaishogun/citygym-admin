<?php
/**
 * @author tmtuan
 * created Date: 17-Nov-20
 */

use App\Domains\News\Http\Controllers\Api\NewsController;
use App\Domains\News\Http\Controllers\Api\ManualController;

Route::group([
    'prefix' => 'news',
    'as' => 'news.',
], function () {
    Route::get('list-categories', [NewsController::class, 'listCategories'])->name('list-categories');
    Route::get('get-category', [NewsController::class, 'getCategory'])->name('get-category');
    Route::get('list-news', [NewsController::class, 'listNews'])->name('list-news');
    Route::get('get-new', [NewsController::class, 'getNew'])->name('get-new');

    Route::get('list-banner', [NewsController::class, 'listBanner'])->name('list-banner');
});


Route::group([
    'prefix' => 'faq',
    'as' => 'faq.',
], function () {
    Route::get('list-faqs', [ManualController::class, 'listFaq'])->name('list-faqs');
    Route::get('get-faq/{id}', [ManualController::class, 'getFaq'])->name('get-faq');

});
