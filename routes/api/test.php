<?php
use App\Domains\Crm\Http\Controllers\Api\Tests\TestController;

Route::group([
    'prefix' => 'test',
    'as' => 'test.',
], function () {
    Route::get('test-call', [TestController::class, 'testTest'])->name('test-test');
    Route::get('test-no-call', [TestController::class, 'testTestNo'])->name('test-test');
});
