<?php
/**
 * @author tmtuan
 * created Date: 02-Dec-20
 */

use App\Domains\Crm\Http\Controllers\Api\Users\SfUserController;
use App\Domains\Crm\Http\Controllers\Api\Club\SfClubController;
use App\Domains\Crm\Http\Controllers\Api\Booking\SfBookingController;
use App\Domains\Crm\Http\Controllers\Api\Booking\SfTaskController;
use App\Domains\Crm\Http\Controllers\Api\Promotion\SfPromotionController;
use App\Domains\Crm\Http\Controllers\Api\SupportCase\SfCaseController;
use App\Domains\Crm\Http\Controllers\Api\Contract\SfContractController;
use App\Domains\Crm\Http\Controllers\Api\Lead\SfLeadController;
use App\Domains\Crm\Http\Controllers\Api\Oppty\SfOpptyController;
use App\Domains\Crm\Http\Controllers\Api\Oppty\SfGfpController;
use App\Domains\Crm\Http\Controllers\Api\Oppty\SfEditOpptyController;
use App\Domains\Crm\Http\Controllers\Api\Product\SfProductController;
use App\Domains\Crm\Http\Controllers\Api\PriceBook\SfPriceBookController;
use App\Domains\Crm\Http\Controllers\Api\Source\SfSourceController;
use App\Domains\Crm\Http\Controllers\Api\Account\SfAccountController;
use App\Domains\Crm\Http\Controllers\Api\SfClass\SfClassController;
use App\Domains\Crm\Http\Controllers\Api\SfClass\SfClassRoomController;
use App\Domains\Crm\Http\Controllers\Api\SfClass\SfClassGroupController;
use App\Domains\Crm\Http\Controllers\Api\Benefit\SfBenefitController;
use App\Domains\Crm\Http\Controllers\Api\Account\SfBankController;
use App\Domains\Crm\Http\Controllers\Api\Benefit\SfGiftController;
use App\Domains\Crm\Http\Controllers\Api\Account\SfContactController;
use App\Domains\Crm\Http\Controllers\Api\Oppty\SfClubAccessController;
use App\Domains\Crm\Http\Controllers\Api\Notification\SfNotification;
use \App\Domains\Crm\Http\Controllers\Api\Oppty\SfOpportunitySales;

Route::group([
    'prefix' => 'sf',
    'as' => 'sf.',
], function () {
    Route::middleware(['auth.jwt', 'sf.checkRoles'])->group(function () {
        Route::group([
            'prefix' => 'notification',
            'as' => 'notification.',
        ], function () {
            Route::post('create-notification', [SfNotification::class, 'setNotification']);
        });

        Route::group([
            'prefix' => 'account',
            'as' => 'account.',
        ], function () {
            Route::post('create-account', [SfAccountController::class, 'createSfAcccount'])->name('create_account');

            Route::post('create-bank-account', [SfBankController::class, 'createBank'])->name('create_bankAccount');
            Route::delete('del-bank-account', [SfBankController::class, 'deleteBank']);

            Route::post('create-contact', [SfContactController::class, 'createContact'])->name('create_contact');
            Route::delete('del-contact', [SfContactController::class, 'deleteContact']);
        });

        Route::group([
            'prefix' => 'benefit',
            'as' => 'benefit.',
        ], function () {
            Route::post('create-benefit', [SfBenefitController::class, 'createBenefit'])->name('create_benefit');
            Route::delete('del-benefit', [SfBenefitController::class, 'deleteBenefit']);

            Route::post('create-gift', [SfGiftController::class, 'createGift'])->name('create_gift');
            Route::delete('del-gift', [SfGiftController::class, 'deleteGift']);
        });

        Route::group([
            'prefix' => 'user',
            'as' => 'user.',
        ], function () {
            Route::post('create-user', [SfUserController::class, 'createUser'])->name('create_user');
            Route::post('create-user-batch', [SfUserController::class, 'createUserBatch'])->name('create_user_batch');
            Route::post('create-user-kpi', [SfUserController::class, 'createUserKpi'])->name('create_user_kpi');
            Route::post('set-password', [SfUserController::class, 'setPassword']);
            Route::post('set-password-multi', [SfUserController::class, 'setPasswordMulti']);

            Route::post('check-username', [SfUserController::class, 'checkUserName']);
            Route::post('change-email', [SfUserController::class, 'changeEmail']);
        });

        Route::group([
            'prefix' => 'club',
            'as' => 'club.',
        ], function () {
            Route::post('create-club', [SfClubController::class, 'createClub'])->name('create_club');
            Route::delete('del-club', [SfClubController::class, 'deleteClub']);

            Route::post('create-club-access-product', [SfClubController::class, 'createClubAccessProduct'])->name('create_clubAccessPdt');
            Route::delete('del-club-access-product', [SfClubController::class, 'deleteClubAccessProduct']);

            Route::post('create-club-applied-pricebook', [SfClubController::class, 'createClubAppliedPricebook'])->name('create_clubAppliedPriceBook');
            Route::delete('del-club-applied-pricebook', [SfClubController::class, 'deleteClubAppliedPricebook']);

            Route::post('create-club-applied-promotion', [SfClubController::class, 'createClubAppliedPromotion'])->name('create_clubAppliedPromotion');
            Route::delete('del-club-applied-promotion', [SfClubController::class, 'deleteClubAppliedPromotion']);

            Route::post('create-club-access', [SfClubAccessController::class, 'createClubAccess']);
            Route::delete('del-club-access', [SfClubAccessController::class, 'deleteClubAccess']);
        });

        Route::group([
            'prefix' => 'booking',
            'as' => 'booking.',
        ], function () {
            Route::post('create-booking', [SfBookingController::class, 'createBooking'])->name('create_booking');
            Route::put('edit/{id}', [SfBookingController::class, 'editBooking']);
            Route::delete('del-booking', [SfBookingController::class, 'deleteBooking']);

            Route::post('create-schedule-trainer', [SfBookingController::class, 'createScheduleTrainer'])->name('create_scheduleTrainer');
            Route::delete('del-schedule-trainer', [SfBookingController::class, 'deleteScheduleTrainer']);

            Route::post( 'create-schedule-hv', [SfBookingController::class, 'createScheduleHv'])->name('create_scheduleHv');
            Route::delete('del-schedule-hv', [SfBookingController::class, 'deleteScheduleHv']);
        });

        Route::group([
            'prefix' => 'task',
            'as' => 'task.',
        ], function () {
            Route::post('create-task', [SfTaskController::class, 'createTask'])->name('create_task');
            Route::delete('del-task', [SfTaskController::class, 'deleteTask']);
        });

        Route::group([
            'prefix' => 'promotion',
            'as' => 'promotion.',
        ], function () {
            Route::post('create-promotion', [SfPromotionController::class, 'createPromotion'])->name('create_promotion');
            Route::delete('del-promotion', [SfPromotionController::class, 'deletePromotion']);

            Route::post('create-promotion-item', [SfPromotionController::class, 'createPromotionItems'])->name('create_promotion_item');
            Route::delete('del-promotion-item', [SfPromotionController::class, 'deletePromotionItem']);

            Route::post('create-promotion-item-club', [SfPromotionController::class, 'createPromotionItemClub'])->name('create_promotion_item_club');
            Route::delete('del-promotion-item-club', [SfPromotionController::class, 'deletePromotionItemClub']);
        });

        Route::group([
            'prefix' => 'case',
            'as' => 'case.',
        ], function () {
            Route::post('create-case', [SfCaseController::class, 'createCase'])->name('create_case');
            Route::delete('del-case', [SfCaseController::class, 'deleteCase']);

            Route::post('create-case-transfer', [SfCaseController::class, 'CaseContractTransfer'])->name('create_case_transfer');
            Route::delete('del-case-transfer', [SfCaseController::class, 'deleteCaseTransfer']);

        });

        Route::group([
            'prefix' => 'contract',
            'as' => 'contract.',
        ], function () {
            Route::post('create-contract', [SfContractController::class, 'createContract'])->name('create_contract');
            Route::delete('del-contract', [SfContractController::class, 'deleteContract']);

            Route::post('create-contract-benefit', [SfContractController::class, 'createContractBenefit'])->name('create_contract_benefit');
            Route::delete('del-contract-benefit', [SfContractController::class, 'deleteContractBenefit']);

            Route::post('create-contract-club', [SfContractController::class, 'createContractClub'])->name('create_contract_club');
            Route::delete('del-contract-club', [SfContractController::class, 'deleteContractClub']);

            Route::post('create-contract-gift', [SfContractController::class, 'createContractGift'])->name('create_contract_gift');
            Route::delete('del-contract-gift', [SfContractController::class, 'deleteContractGift']);

            Route::post('create-contract-product', [SfContractController::class, 'createContractProduct'])->name('create_contract_product');
            Route::delete('del-contract-product', [SfContractController::class, 'deleteContractProduct']);

            Route::post('create-contract-promotion', [SfContractController::class, 'createContractPromotion'])->name('create_contract_promotion');
            Route::delete('del-contract-promotion', [SfContractController::class, 'deleteContractPromotion']);

            Route::post('create-contract-sale', [SfContractController::class, 'createContractSale']);
            Route::put('edit-contract-sale/{id}', [SfContractController::class, 'editContractSale']);
            Route::delete('del-contract-sale/{id}', [SfContractController::class, 'deleteContractSale']);
            Route::delete('del-contract-sale', [SfContractController::class, 'mdeleteContractSale']);

            Route::post('create-contract-suspend', [SfContractController::class, 'createContractSuspend'])->name('create_contract_suspend');
            Route::delete('del-contract-suspend', [SfContractController::class, 'deleteContractSuspend']);
        });

        Route::group([
            'prefix' => 'lead',
            'as' => 'lead.',
        ], function () {
            Route::post('create-lead', [SfLeadController::class, 'createLead'])->name('create_lead');
            Route::put('edit/{id}', [SfLeadController::class, 'updateLead']);
            Route::delete('del-lead', [SfLeadController::class, 'deleteLead']);
        });

        Route::group([
            'prefix' => 'oppty',
            'as' => 'oppty.',
        ], function () {
            Route::post('create-oppty', [SfOpptyController::class, 'createOppty'])->name('create_oppty');
            Route::delete('del-oppty', [SfOpptyController::class, 'deleteOppty']);

            Route::post('create-oppty-line-item', [SfOpptyController::class, 'createOpptyLineItem']);
            Route::delete('del-oppty-line-item', [SfOpptyController::class, 'deleteOpptyLineItem']);

            Route::post('create-oppty-line-benefit', [SfOpptyController::class, 'createOpptyLineBenefit']);
            Route::delete('del-oppty-line-benefit', [SfOpptyController::class, 'deleteOpptyLineBenefit']);

            Route::post('create-oppty-line-gift', [SfOpptyController::class, 'createOpptyLineGift']);
            Route::delete('del-oppty-line-gift', [SfOpptyController::class, 'deleteOpptyLineGift']);

            Route::post('create-oppty-line-promotion', [SfOpptyController::class, 'createOpptyLinePromotion']);
            Route::delete('del-oppty-line-promotion', [SfOpptyController::class, 'deleteOpptyLinePromotion']);

            Route::post('create-gfp', [SfGfpController::class, 'createGfp'])->name('create_gfp');
            Route::delete('del-gfp', [SfGfpController::class, 'deleteGfp']);

            Route::post('create-oppty-sale', [SfOpportunitySales::class, 'createOpportunitySale']);
            Route::put('edit-oppty-sale/{id}', [SfOpportunitySales::class, 'editOpportunitySale']);
            Route::delete('del-oppty-sale/{id}', [SfOpportunitySales::class, 'deleteOpportunitySale']);
            Route::delete('del-oppty-sale', [SfOpportunitySales::class, 'mdeleteContractSale']);

            //edit
            Route::put('edit/{id}', [SfEditOpptyController::class, 'updateOppty']);
        });

        Route::group([
            'prefix' => 'product',
            'as' => 'product.',
        ], function () {
            Route::post('create-product', [SfProductController::class, 'createProduct'])->name('create_product');
            Route::delete('del-product', [SfProductController::class, 'deleteProduct']);

            Route::post('create-product-benefit', [SfProductController::class, 'createProductBenefit'])->name('create_product_benefit');
            Route::delete('del-product-benefit', [SfProductController::class, 'deleteProductBenefit']);

            Route::post('create-product-gift', [SfProductController::class, 'createProductGift'])->name('create_product_gift');
            Route::delete('del-product-gift', [SfProductController::class, 'deleteProductGift']);

            Route::post('create-product-promotion', [SfProductController::class, 'createProductPromotion'])->name('create_product_promotion');
            Route::delete('del-product-promotion', [SfProductController::class, 'deleteProductPromotion']);
        });

        Route::group([
            'prefix' => 'pricebook',
            'as' => 'pricebook.',
        ], function () {
            Route::post('create-pricebook', [SfPriceBookController::class, 'createPriceBook'])->name('create_pricebook');
            Route::delete('del-pricebook', [SfPriceBookController::class, 'deletePriceBook']);

            Route::post('create-pricebook-entry', [SfPriceBookController::class, 'createPriceBookEntry'])->name('create_pricebook_entry');
            Route::delete('del-pricebook-entry', [SfPriceBookController::class, 'deletePriceBookEntry']);
        });

        Route::group([
            'prefix' => 'source',
            'as' => 'source.',
        ], function () {
            Route::post('create-source', [SfSourceController::class, 'createSource'])->name('create_source');
            Route::delete('del-source', [SfSourceController::class, 'deleteSource']);
        });

        Route::group([
            'prefix' => 'ctg-class',
            'as' => 'ctg-class.',
        ], function () {
            Route::post('create-class', [SfClassController::class, 'createClass'])->name('create_class');
            Route::delete('del-class', [SfClassController::class, 'deleteClass']);

            Route::post('create-class-room', [SfClassRoomController::class, 'createClassRoom'])->name('create_classRoom');
            Route::delete('del-class-room', [SfClassRoomController::class, 'deleteClassRoom']);

            Route::post('create-class-group', [SfClassGroupController::class, 'createClassGroup'])->name('create_classGroup');
            Route::delete('del-class-group', [SfClassGroupController::class, 'deleteClassGroup']);//*
        });

    });
});
