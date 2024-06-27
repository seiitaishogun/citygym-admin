<?php
/**
 * @author tmtuan
 * created Date: 8/26/2021
 * project: citygym-admin
 */
namespace App\Domains\Crm\Http\Controllers\Api\Booking;

use App\Domains\Crm\Models\Contract;
use App\Domains\Crm\Models\Schedule;
use App\Domains\Crm\Models\ScheduleHV;
use App\Domains\TmtSfObject\Classes\SObject;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MemberBooklingController extends BookingController {
    public function msCheckinContract(Request $request) {
        $schedule_id = $request->schedule;
        $contract_id = $request->contract;
        $contract_num = $request->contract_num;
        if ( empty($schedule_id) ) {
            return response()->json([
                'status' => false,
                'message' => 'Lịch tập không hợp lệ.'
            ], 422);
        }

        $schedule = Schedule::where('Id', $schedule_id)->first();
        if ( !isset($schedule->Id) ) {
            return response()->json([
                'status' => false,
                'message' => 'Lịch tập không hợp lệ.'
            ], 422);
        }

        if(empty($contract_id) && empty($contract_num)) {
            return response()->json([
                'status' => false,
                'message' => 'Mã hợp đồng không hợp lệ.'
            ], 422);
        }

        if($contract_num){
            $contract = Contract::where('Contract_Number_Searchable__c', $contract_num)->first();
        } 
        if($contract_id){
            $contract = Contract::where('Id', $contract_id)->first();
        } 

        if ( !isset($contract->Id) ) {
            return response()->json([
                'status' => false,
                'message' => 'Mã hợp đồng không chính xác. Vui lòng thử lại.'
                // 'message' => 'Invalid contract'
            ], 422);
        }
        
        $newItem = new ScheduleHV();
        $scheduleData = [
            'Account__c' => $contract->AccountId,
            'Schedule__c' => $schedule->Id,
            'Registered_Guest__c' => $postData['guest_number'] ?? 0,
            'Source__c' => 'App',
            'HV_Status__c' => 'Booked',
            'is_Checkin__c' => 1,
            'Checkin_Time__c' => Carbon::now()->toIso8601String(),
        ];

        //create schedule record in SF
        try {
            $response = SObject::create('Schedule_HV__c', $scheduleData);
            if( isset($response->status_code)
                && in_array($response->status_code, [400, 404]) ) return response()->json([
                'message' => $response->message,
                'data' => $scheduleData],
                $response->status_code);

            //set date
            $this->setDefaultDate($scheduleData);

            $newItem->fill($scheduleData);
            $newItem->Id = $response;
            $newItem->is_sync_crm = 1;
            $newItem->save();
            //log
            activity('member_app')
                ->causedBy($newItem)
                ->withProperties(['schedule hv' => $newItem->toArray()])
                ->log('Schedule HV| create new schedule HV #' . $response);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 422);
        }

        return response()->json($newItem, 200);

    }
}
