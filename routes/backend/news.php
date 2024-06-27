<?php
/**
 * @author tmtuan
 * created Date: 13-Nov-20
 */

use App\Domains\News\Http\Controllers\Backend\News\NewsController;
use App\Domains\News\Http\Controllers\Backend\Manual\CreateManualController;
use App\Domains\News\Http\Controllers\Backend\News\CreateNewsController;
use App\Domains\News\Http\Controllers\Backend\Category\CategoryController;
use App\Domains\News\Http\Controllers\Backend\Category\AddCategoryController;
use App\Domains\News\Http\Controllers\Backend\Category\EditCategoryController;
use App\Domains\News\Http\Controllers\Backend\News\EditNewsController;
use App\Domains\News\Http\Controllers\Backend\Banner\BannerController;
use App\Domains\News\Http\Controllers\Backend\Banner\AddBannerController;
use App\Domains\News\Http\Controllers\Backend\Banner\EditBannerController;
use \App\Domains\News\Http\Controllers\Backend\Manual\ManualController;
use App\Domains\News\Http\Controllers\Backend\Manual\EditManualController;
use App\Http\Controllers\Backend\Attach;

use Tabuna\Breadcrumbs\Trail;

Route::group([
    'prefix' => 'attach',
    'as' => 'attach.',
], function () {
    Route::post('mceUpload', [Attach::class, 'store'])
        ->name('store');
});

Route::group([
    'prefix' => 'news',
    'as' => 'news.',
], function () {
    Route::get('/', [NewsController::class, 'index'])
        ->name('index')
        ->breadcrumbs(function (Trail $trail) {
            $trail->push(__('news.news_title'), route('admin.news.index'));
        });

    Route::post('/', [NewsController::class, 'index'])
        ->name('index');

    Route::get('create', [CreateNewsController::class, 'create'])
        ->name('create')
        ->breadcrumbs(function (Trail $trail) {
            $trail->parent('admin.news.index')
                ->push(__('news.add_new'), route('admin.news.create'));
        });

    Route::post('add-action', [CreateNewsController::class, 'addAction'])
        ->name('add_action')
        ->breadcrumbs(function (Trail $trail) {
            $trail->parent('admin.news.create')
                ->push(__('news.add_new'), route('admin.news.add_action'));
        });

    Route::get('edit/{id}', [EditNewsController::class, 'edit'])
        ->name('edit')
        ->breadcrumbs(function (Trail $trail) {
            $trail->parent('admin.news.index')
                ->push(__('news.edit_new'), route('admin.news.index'));
        });

    Route::post('edit-action/{id}', [EditNewsController::class, 'editAction'])
        ->name('edit_action')
        ->breadcrumbs(function (Trail $trail) {
            $trail->parent('admin.news.index')
                ->push(__('news.edit_new'), route('admin.news.index'));
        });

    Route::get('delete/{id}', [NewsController::class, 'delete'])
        ->name('delete');

    Route::post('delete-all', [NewsController::class, 'deleteAll'])
        ->name('deleteAll');

    Route::post('un-published', [NewsController::class, 'unPublished'])
        ->name('unPublished');

    Route::get('clone-content', [NewsController::class, 'cloneContent'])
        ->name('cloneContent');
});

Route::group([
    'prefix' => 'manual',
    'as' => 'manual.',
], function () {
    Route::get('/', [ManualController::class, 'index'])
        ->name('index')
        ->breadcrumbs(function (Trail $trail) {
            $trail->push(__('news.manual_title'), route('admin.manual.index'));
        });

    Route::get('create', [CreateManualController::class, 'create'])
        ->name('create')
        ->breadcrumbs(function (Trail $trail) {
            $trail->parent('admin.manual.index')
                ->push(__('news.add_manual'), route('admin.manual.create'));
        });

    Route::post('add-action', [CreateManualController::class, 'addAction'])
        ->name('add_action')
        ->breadcrumbs(function (Trail $trail) {
            $trail->parent('admin.manual.create')
                ->push(__('news.add_manual'), route('admin.manual.add_action'));
        });

    Route::get('edit/{id}', [EditManualController::class, 'edit'])
        ->name('edit')
        ->breadcrumbs(function (Trail $trail) {
            $trail->parent('admin.manual.index')
                ->push(__('news.edit_manual'), route('admin.manual.index'));
        });

    Route::post('edit-action/{id}', [EditManualController::class, 'editAction'])
        ->name('edit_action')
        ->breadcrumbs(function (Trail $trail) {
            $trail->parent('admin.manual.index')
                ->push(__('news.edit_manual'), route('admin.manual.index'));
        });

    Route::get('delete/{id}', [ManualController::class, 'delete'])
        ->name('delete');
});

Route::group([
    'prefix' => 'category',
    'as' => 'category.',
], function () {
    Route::get('/', [CategoryController::class, 'index'])
        ->name('index')
        ->breadcrumbs(function (Trail $trail) {
            $trail->push(__('news.cat_title'), route('admin.category.index'));
        });

    Route::get('create', [CategoryController::class, 'create'])
        ->name('create')
        ->breadcrumbs(function (Trail $trail) {
            $trail->parent('admin.category.index')
                ->push(__('news.cat_title'), route('admin.category.create'));
        });
    Route::post('add-action', [AddCategoryController::class, 'addAction'])
        ->name('add_action')
        ->breadcrumbs(function (Trail $trail) {
            $trail->parent('admin.category.create')
                ->push(__('news.add_new'), route('admin.category.add_action'));
        });

    Route::get('edit/{id}', [EditCategoryController::class, 'edit'])
        ->name('edit')
        ->breadcrumbs(function (Trail $trail) {
            $trail->parent('admin.category.index')
                ->push(__('news.edit_category'), route('admin.category.index'));
        });

    Route::post('edit-action/{id}', [EditCategoryController::class, 'editAction'])
        ->name('edit_action')
        ->breadcrumbs(function (Trail $trail) {
            $trail->parent('admin.category.index')
                ->push(__('news.edit_category'), route('admin.category.index'));
        });

    Route::get('delete/{id}', [CategoryController::class, 'delete'])
        ->name('delete');
});


Route::group([
    'prefix' => 'banner',
    'as' => 'banner.',
], function () {
    Route::get('/', [BannerController::class, 'index'])
        ->name('index')
        ->breadcrumbs(function (Trail $trail) {
            $trail->push(__('news.banner_page'), route('admin.banner.index'));
        });

    Route::get('add', [AddBannerController::class, 'add'])
        ->name('add')
        ->breadcrumbs(function (Trail $trail) {
            $trail->parent('admin.banner.index')
                ->push(__('news.add_banner'), route('admin.banner.index'));
        });

    Route::post('add-action', [AddBannerController::class, 'addAction'])
        ->name('add_action')
        ->breadcrumbs(function (Trail $trail) {
            $trail->parent('admin.banner.index')
                ->push(__('news.add_banner'), route('admin.banner.index'));
        });

    Route::get('edit/{id}', [EditBannerController::class, 'edit'])
        ->name('edit')
        ->breadcrumbs(function (Trail $trail) {
            $trail->parent('admin.banner.index')
                ->push(__('news.edit_banner'), route('admin.banner.index'));
        });

    Route::post('edit-action/{id}', [EditBannerController::class, 'editAction'])
        ->name('edit_action')
        ->breadcrumbs(function (Trail $trail) {
            $trail->parent('admin.banner.index')
                ->push(__('news.add_banner'), route('admin.banner.index'));
        });

    Route::get('delete/{id}', [BannerController::class, 'delete'])
        ->name('delete');
});
