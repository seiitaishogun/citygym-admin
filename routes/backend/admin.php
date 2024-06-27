<?php

use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\ActivityLogController;
use Tabuna\Breadcrumbs\Trail;
use App\Domains\Acp\Http\Controllers\Backend\Settings\ButtonSettingController;
use App\Domains\Acp\Http\Controllers\Backend\Settings\MemberSettingController;
use App\Domains\Acp\Http\Controllers\Backend\Settings\EmailSettingController;
use App\Domains\Acp\Http\Controllers\Backend\Settings\GreetingMemoController;
use App\Domains\Acp\Http\Controllers\Backend\Manage\ManageUser;
use \App\Domains\Acp\Http\Controllers\Backend\Tmt;
use \App\Domains\Acp\Http\Controllers\Backend\Manage\RemoteCommand;
use \App\Domains\Acp\Http\Controllers\Backend\Import\CsvFiles;
use \App\Domains\Acp\Http\Controllers\Backend\Settings\ContactController;

// All route names are prefixed with 'admin.'.
Route::redirect('/', '/admin/dashboard', 301);
Route::get('dashboard', [DashboardController::class, 'index'])
    ->name('dashboard')
    ->breadcrumbs(function (Trail $trail) {
        $trail->push(__('Home'), route('admin.dashboard'));
    });

Route::get('migrate', [DashboardController::class, 'migrate'])
    ->name('migrate');

Route::group([
    'prefix' => 'log',
    'as' => 'log.',
], function () {
    Route::get('list-log', [ActivityLogController::class, 'index'])->name('list-log');
    Route::get('view/{id}', [ActivityLogController::class, 'view'])->name('view-log');
});

Route::get('clear-cache', function() {
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    Artisan::call('config:clear');
    return "Cache is cleared";
})->name('clear_cache');

Route::get('test-cron', function() {
    Artisan::call('sync:schedule-hv');

    return date('d/m/Y h:m:s')." - Cron Done!";
});

Route::group([
    'prefix' => 'member-app-config',
    'as' => 'member-app-config.',
], function () {
    //cấu hình button hiển thị trên trang home của app member
    Route::get('button-iframe', [ButtonSettingController::class, 'ButtonAppSettings'])
        ->name('mbAppCf');
    Route::get('button-iframe/add-new', [ButtonSettingController::class, 'AddNewBtnIframe'])
        ->middleware('permission:admin')
        ->name('addNewBtnIframe');
    Route::post('button-iframe/add-new', [ButtonSettingController::class, 'AddNewBtnIframeAction'])
        ->middleware('permission:admin')
        ->name('addNewBtnIframe');

    Route::get('button-iframe/edit/{id}', [ButtonSettingController::class, 'editBtnIframe'])
        ->name('editBtnIframe');
    Route::post('button-iframe/edit/{id}', [ButtonSettingController::class, 'EditBtnIframeAction'])
        ->name('editBtnIframe');

    Route::get('button-iframe/delete/{id}', [ButtonSettingController::class, 'deleteBtnIframe'])
        ->name('deleteBtnIframe');

    // cấu hình app member
    Route::get('app-settings', [MemberSettingController::class, 'MemberAppSettings'])
        ->name('mbAppSetting');

    Route::get('app-settings/add-new', [MemberSettingController::class, 'AddNewSetting'])
        ->middleware('permission:admin')
        ->name('addNewSettings');
    Route::post('app-settings/add-new', [MemberSettingController::class, 'AddNewSettingAction'])
        ->middleware('permission:admin')
        ->name('addNewSettings');

    Route::get('app-settings/edit/{id}', [MemberSettingController::class, 'editSetting'])
        ->name('editSetting');
    Route::post('app-settings/edit/{id}', [MemberSettingController::class, 'editSettingAction'])
        ->name('editSetting');

    //member app greeting memo
    Route::get('greeting-memo', [GreetingMemoController::class, 'memoSettings'])
        ->name('memoSetting');

    Route::get('greeting-memo/add', [GreetingMemoController::class, 'AddNewMemo'])
        ->middleware('permission:admin')
        ->name('addMemo');
    Route::post('greeting-memo/add', [GreetingMemoController::class, 'AddNewMemoAction'])
        ->middleware('permission:admin');

    Route::get('greeting-memo/edit/{id}', [GreetingMemoController::class, 'editMemo'])
        ->middleware('permission:admin')
        ->name('editMemo');

    Route::post('greeting-memo/edit/{id}', [GreetingMemoController::class, 'editMemoAction'])
        ->middleware('permission:admin');

    Route::get('greeting-memo/delete/{id}', [GreetingMemoController::class, 'deleteMemo'])
        ->name('deleteMemo');

    //member app contact config
    Route::get('contact', [ContactController::class, 'contactSettings'])
        ->name('contactSetting');

    Route::get('contact/add', [ContactController::class, 'AddContact'])
        ->middleware('permission:admin')
        ->name('addContact');
    Route::post('contact/add', [ContactController::class, 'AddContactAction'])
        ->middleware('permission:admin');

    Route::get('contact/edit/{id}', [ContactController::class, 'edit'])
        ->middleware('permission:admin')
        ->name('editContact');
    Route::post('contact/edit/{id}', [ContactController::class, 'editAction'])
        ->middleware('permission:admin');

    Route::get('contact/delete/{id}', [ContactController::class, 'delete'])
        ->name('deleteItem');

});

Route::group([
    'prefix' => 'config-email',
    'as' => 'config-email.',
], function () {
    Route::get('/', [EmailSettingController::class, 'emailSetting'])
        ->middleware('permission:admin')
        ->name('configEmail');
    Route::post('/save', [EmailSettingController::class, 'saveEmailSetting'])
        ->middleware('permission:admin')->name('saveSetting');
});

Route::group([
    'prefix' => 'tmt',
    'as' => 'tmt.',
], function () {
    Route::get('move-us-mb', [Tmt::class, 'moveUserToMember'])
        ->middleware('permission:admin');
});

Route::group([
    'prefix' => 'export',
    'as' => 'export.',
], function () {
    Route::get('duplicateUser', [Tmt::class, 'exportDuplicateUser'])
        ->middleware('permission:admin')->name('export_duplicate_user');
});

Route::group([
    'prefix' => 'import',
    'as' => 'import.',
], function () {
    Route::get('csv', [CsvFiles::class, 'ImportCsv'])
        ->middleware('permission:admin')->name('import_csv');
    Route::post('csv', [CsvFiles::class, 'ImportCsvAction']);

    Route::get('password-demo', [CsvFiles::class, 'importAccount'])
        ->middleware('permission:admin')->name('import_account');
    Route::post('password-demo', [CsvFiles::class, 'ImportAccountAction']);
});

Route::get('sent-test-email', [EmailSettingController::class, 'sentTestMail']);

Route::group([
    'prefix' => 'manage',
    'as' => 'manage.',
    'middleware' => config('boilerplate.access.middleware.confirm'),
], function () {
    Route::group([
        'middleware' => 'role:'.config('boilerplate.access.role.admin'),
    ], function () {
        Route::get('user', [ManageUser::class, 'index'])
            ->middleware('permission:admin')
            ->name('manageUser');
        Route::get('user/hot-reset-password/{id}', [ManageUser::class, 'hotResetPass'])
            ->middleware('permission:admin')
            ->name('hotResetPass');

        Route::get('command', [RemoteCommand::class, 'command'])
            ->name('command');
        Route::post('command', [RemoteCommand::class, 'excute'])
            ->name('excute_command');
    });
});
