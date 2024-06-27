<?php
/**
 * @author tmtuan
 * created Date: 10-Nov-20
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

class ApiController extends Controller {
    public function handleOptions()
    {
        return '';
    }

    public function returnErrorResponse($code, $message, $httpStatus = Response::HTTP_OK)
    {
        $response = ['success' => false];
        $response['error'] = [
            'code' => $code,
            'message' => $message
        ];
        return response()->json(
            $response,
            $httpStatus
        );
    }

    public function setDefaultDate(&$item) {
        $timezone = config('app.timezone');
        $item['CreatedDate'] = $item['CreatedDate'] ?? Carbon::now($timezone);
        $item['LastModifiedDate'] = $item['LastModifiedDate'] ?? Carbon::now($timezone);
        $item['SystemModstamp'] = $item['SystemModstamp'] ?? Carbon::now($timezone);
        $item['LastViewedDate'] = $item['LastViewedDate'] ?? Carbon::now($timezone);

        $item['IsDeleted'] = $item['IsDeleted'] ?? 0;
    }

    public function setupLogin(){
        $authCf = config('auth.guards');
        $guardCf = [
            'web' => [
                'driver' => 'session',
                'provider' => 'member'
            ],
            'api' => [
                'driver' => 'token',
                'provider' => 'member',
                'hash' => false,
            ]
        ];
        Config::set('auth.guards', $guardCf);
    }

    public function handleScheduleTime(&$item) {
        $timezone = config('app.timezone');

        if ( isset($item['Start__c']) && !empty($item['Start__c']) ) {
            $start_time = Carbon::createFromFormat('Y-m-d H:i:s', $item['Start__c'], $timezone);
            $start_time->addHour(7);
            $item['Start__c'] = $start_time->toDateTimeString();
        }

        if ( isset($item['End__c']) && !empty($item['End__c']) ) {
            $end_time = Carbon::createFromFormat('Y-m-d H:i:s', $item['End__c'], $timezone);
            $end_time->addHour(7);
            $item['End__c'] = $end_time->toDateTimeString();
        }

        if ( isset($item['Booking_Time_Start__c']) && !empty($item['Booking_Time_Start__c']) ) {
            $booking_time_start = Carbon::createFromFormat('Y-m-d H:i:s', $item['Booking_Time_Start__c'], $timezone);
            $booking_time_start->addHour(7);
            $item['Booking_Time_Start__c'] = $booking_time_start->toDateTimeString();
        }

        if ( isset($item['Booking_Time_End__c']) && !empty($item['Booking_Time_End__c']) ) {
            $bookin_time_end = Carbon::createFromFormat('Y-m-d H:i:s', $item['Booking_Time_End__c'], $timezone);
            $bookin_time_end->addHour(7);
            $item['Booking_Time_End__c'] = $bookin_time_end->toDateTimeString();
        }

        if ( isset($item['Check_In_Time_Start__c']) && !empty($item['Check_In_Time_Start__c']) ) {
            $checkin_time_start = Carbon::createFromFormat('Y-m-d H:i:s', $item['Check_In_Time_Start__c'], $timezone);
            $checkin_time_start->addHour(7);
            $item['Check_In_Time_Start__c'] = $checkin_time_start->toDateTimeString();
        }

        if ( isset($item['Check_In_Time_End__c']) && !empty($item['Check_In_Time_End__c']) ) {
            $checkin_time_end = Carbon::createFromFormat('Y-m-d H:i:s', $item['Check_In_Time_End__c'], $timezone);
            $checkin_time_end->addHour(7);
            $item['Check_In_Time_End__c'] = $checkin_time_end->toDateTimeString();
        }

        if ( isset($item['Cancel_Time_Start__c']) && !empty($item['Cancel_Time_Start__c']) ) {
            $cancel_time_start = Carbon::createFromFormat('Y-m-d H:i:s', $item['Cancel_Time_Start__c'], $timezone);
            $cancel_time_start->addHour(7);
            $item['Cancel_Time_Start__c'] = $cancel_time_start->toDateTimeString();
        }

        if ( isset($item['Cancel_Time_End__c']) && !empty($item['Cancel_Time_End__c']) ) {
            $cancel_time_end = Carbon::createFromFormat('Y-m-d H:i:s', $item['Cancel_Time_End__c'], $timezone);
            $cancel_time_end->addHour(7);
            $item['Cancel_Time_End__c'] = $cancel_time_end->toDateTimeString();
        }

        if ( isset($item['Guest_Booking_Time_Start__c']) && !empty($item['Guest_Booking_Time_Start__c']) ) {
            $guest_booking_time_start = Carbon::createFromFormat('Y-m-d H:i:s', $item['Guest_Booking_Time_Start__c'], $timezone);
            $guest_booking_time_start->addHour(7);
            $item['Guest_Booking_Time_Start__c'] = $guest_booking_time_start->toDateTimeString();
        }

        if ( isset($item['Guest_Booking_Time_End__c']) && !empty($item['Guest_Booking_Time_End__c']) ) {
            $guest_booking_time_end = Carbon::createFromFormat('Y-m-d H:i:s', $item['Guest_Booking_Time_End__c'], $timezone);
            $guest_booking_time_end->addHour(7);
            $item['Guest_Booking_Time_End__c'] = $guest_booking_time_end->toDateTimeString();
        }

    }
}
