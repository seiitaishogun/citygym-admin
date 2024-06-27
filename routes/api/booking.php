<?php
/**
 * @author tmtuan
 * created Date: 15-Dec-20
 */
use App\Domains\Crm\Http\Controllers\Api\Booking\BookingController;
use App\Domains\Crm\Http\Controllers\Api\Booking\SaleBookingController;
use App\Domains\Crm\Http\Controllers\Api\Booking\PtBookingController;
use \App\Domains\Crm\Http\Controllers\Api\Club\ClubController;
use App\Domains\Crm\Http\Controllers\Api\Booking\MemberBooklingController;

Route::group([
    'prefix' => 'club',
    'as' => 'club.',
], function () {
    Route::get('list-clubs', [ClubController::class, 'listClubs']);

    Route::middleware(['middleware' => 'auth.jwt'])->group(function () {

    });
});

Route::group([
    'prefix' => 'booking',
    'as' => 'booking.',
], function () {
    Route::get('list-bookings', [BookingController::class, 'listBooking']); //lấy danh sách lịch tập
    Route::middleware(['middleware' => 'auth.jwt'])->group(function (){
        Route::get('list-sale-bookings', [SaleBookingController::class, 'listSaleBooking']);
        Route::get('list-pt-bookings', [PtBookingController::class, 'listPTBookings']);
        Route::get('list-burnshow-bookings', [PtBookingController::class, 'listBurnShowBooks']);

        Route::get('list-member-bookings', [BookingController::class, 'listBooking']); // lấy danh sách lớp học cho member book
        Route::get('list-member-accept-bookings', [BookingController::class, 'listAcceptBooking']);
        Route::get('list-member-user-bookings', [BookingController::class, 'listUserBooking']); // lấy lịch tập user đã book

        Route::post('member-user-booking', [BookingController::class, 'UserBooking']); // thực hiện đặt lịch tập cho hội viên
        Route::post('member-booking-hv', [BookingController::class, 'memberBookingHv']); // thực hiện đặt lịch tập cho hội viên

        Route::post('member-create-schedule-hv', [BookingController::class, 'CreateScheduleHv']);

        Route::post('cancel-booking', [BookingController::class, 'cancelBooking']); //huỷ lịch tập đã đặt

    	Route::get('detail/{id}', [BookingController::class, 'getBooking'])->name('get-booking');

    	Route::post('checkin', [BookingController::class, 'checkin'])->name('checkin');
    	Route::post('checkin-multi', [BookingController::class, 'checkinMultiple']);
        Route::post('named-checkin', [BookingController::class, 'CheckinWithName']);

        Route::get('list-contract-sale', [BookingController::class, 'listContract']);

        Route::post('t-booking', [SaleBookingController::class, 'bookTSchedule']);
        Route::get('t-booking', [SaleBookingController::class, 'checkTBooking']);

        Route::get('f-booking', [PtBookingController::class, 'checkFBooking']);
        Route::post('f-booking', [PtBookingController::class, 'bookFSchedule']);

        Route::get('burn-show-customer', [PtBookingController::class, 'checkBurnShow']);
        Route::get('burn-show-club', [PtBookingController::class, 'getClub']);

        Route::get('burn-show/customer-contract', [PtBookingController::class, 'getCustomerContract']);
        Route::get('burn-show/customer-contract-cms', [PtBookingController::class, 'getCustomerContractLocal']);

        Route::post('burn-show', [PtBookingController::class, 'burnShow']);
        Route::put('burn-show/confirm', [PtBookingController::class, 'confirmBurnShow']);

        Route::get('list-customer-burn-show', [PtBookingController::class, 'listCustomerBurnshow']); // lấy danh sách lịch burnshow của khách hàng
        Route::post('customer-confirm-burn-show', [PtBookingController::class, 'memberConfirmBurnShow']); // thực hiện confirm 1 lịch burnshow với PT

        Route::post('ms-checkin-contract', [MemberBooklingController::class, 'msCheckinContract']); //ms checkin member bằng hợp đồng
    });
});
