<?php
/**
 * @author tmtuan
 * created Date: 28-Dec-20
 */
namespace App\Domains\Crm\Http\Controllers\Api\Booking;

use App\Domains\Auth\Models\User;
use App\Domains\Crm\Models\Opportunity;
use App\Domains\Crm\Models\RecordType;
use App\Domains\Crm\Models\Schedule;
use App\Domains\Crm\Models\SfAcccount;
use Illuminate\Http\Request;
use App\Domains\TmtSfObject\Classes\SObject;
use App\Domains\Crm\Http\Controllers\Traits\sfBooking;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SaleBookingController extends BookingController{
    use sfBooking;

    /**
     * màn hình booking T cho sale chỉ load ra những oppty có recordtype là Individual_MB
     * ko cho phép đặt lịch trùng giờ
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkTBooking(Request $request) {
        $user = auth()->user();

        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);

        $returnData = [];
        $returnData['bk_setting'][] = $this->getSfBookingSetting();

        // get oppty
        $oppties = Opportunity::join('salesforce_Record_Type', 'salesforce_Record_Type.Id', '=', 'salesforce_Opportunity.RecordTypeId')
                        ->select(['salesforce_Opportunity.Id', 'RecordTypeId', 'salesforce_Opportunity.name', 'PT_assign__c'])
                        ->where('salesforce_Record_Type.SobjectType', 'Opportunity')
                        ->where('salesforce_Record_Type.DeveloperName', 'Individual_MB')
                        ->where('IsDeleted', 0)
                        ->where('Sales_Assign__c', $user->sf_account_id())->get();

        $returnData['oppty'] = $oppties??[];

        // get PT
        //DB::enableQueryLog();
        //        $pt = SfAcccount::join('salesforce_Record_Type', 'salesforce_Record_Type.Id', '=', 'salesforce_Account.RecordTypeId')
        //                        ->select(['salesforce_Account.Id', 'RecordTypeId', 'salesforce_Account.name'])
        //                        ->where('salesforce_Record_Type.SobjectType', 'Account')
        //                        ->where('salesforce_Record_Type.DeveloperName', 'Employee')
        //                        ->where('Job_Title__c', 'Personal Trainer')
        //                        ->where('salesforce_Account.IsDeleted', 0)
        //                        ->get();
        //dd(DB::getQueryLog());

        $usersDt = User::whereHas("roles", function($q){
            $q->where("name", "PT");
        })->get();

        $ptData = [];
        foreach ( $usersDt as $us ) {
            $acc = SfAcccount::join('user_sf_account', 'user_sf_account.sf_account_id', '=', 'salesforce_Account.Id')
                        ->select(['salesforce_Account.Id', 'RecordTypeId', 'salesforce_Account.name'])
                        ->where('user_sf_account.user_id', $us->id)->get()->first();
            if ( !empty($acc) ) $ptData[] = $acc;
        }

        $returnData['PT'] = $ptData;

        return response()->json($returnData, 200);
    }

    /**
     * Đặt buổi T Booking
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bookTSchedule(Request $request) {
        $postData = $request->post();
        $user = auth()->user();

        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);

        //get T record Type
        $recordType = RecordType::where('SobjectType', 'Schedule__c')
                        ->where('DeveloperName', 'T_Booking')->get()->first();
        //check booking time
        //        $bkChk = Schedule::where('Opportunity_T__c', $postData['Oppty'])
        //                        ->where('RecordTypeId', $recordType->Id)
        //                        ->count();
        //        if ( $bkChk >= $postData['schedule_limit'] ) return response()->json('Invalid Booking Count', 422);

        //Schedule Data
        $startTime = Carbon::parse($postData['Start_Time']);
        $scheduleData = [
            "RecordTypeId" => $recordType->Id??'',
            "PT_Assign__c" => $postData['PT_Assign'],
            "Status__c" => "Open",
            "Opportunity_T__c" => $postData['Oppty'],
            "Start__c" => $startTime->toIso8601String(),
            "Duration__c" => $postData['Duration'],
            "Description__c" => $postData['Description'],
            "Source__c" => "App"
        ];

        $newItem = new Schedule();

        //create schedule record in SF
        try {
            $response = SObject::create('Schedule__c', $scheduleData);
            if ( isset($response->status_code) && $response->status_code == 400 ) return response()->json(['message' => $response->message, 'data' => $scheduleData], $response->status_code);

            //set date
            $this->setDefaultDate($scheduleData);

            $newItem->fill($scheduleData);
            $newItem->Id = $response;
            $newItem->End__c = $postData['End_Time'] ?? $startTime->addMinutes($postData['Duration']);
            $newItem->sync_result = $response->status_code;
            if ($response->status_code == 200)
                $newItem->last_sync_success = date('Y-m-d H:i:s');
            $newItem->save();
            //log
            activity('sale_pt_app')
                ->causedBy($newItem)
                ->withProperties(['schedule' => $newItem->toArray()])
                ->log('Schedule T| create new schedule T #'.$response);
            return response()->json(['message' => 'Đặt lịch thành công', 'data' => $newItem], 201);
        } catch ( \Exception $e) {
            return response()->json($e->getMessage(), 422);
        }

    }

    public function listSaleBooking(Request $request) {
        $user = auth()->user();
        if ( !$user->hasRole(['Sale'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);

        if (!$user->sf_account_id()) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid sf_account_id'
            ], 404);
        }
        $input = $request->query();
        $per_page = $queryData['per_page'] ?? 15;

        $query = Schedule::join('salesforce_Opportunity', 'salesforce_Opportunity.Id', '=', 'salesforce_Schedule__c.Opportunity_T__c')
            ->join('salesforce_Record_Type', 'salesforce_Record_Type.Id', '=', 'salesforce_Schedule__c.RecordTypeId')
            ->with(['OpportunityT' => function($md){
                $md->select(['Id', 'Name']);
            }])
            ->with(['PtAssign' => function($qr){
                $qr->select(['Id', 'LastName', 'MiddleName', 'FirstName', 'Name']);
            }])
            ->select(['salesforce_Schedule__c.Id as Schedule_Id', 'salesforce_Schedule__c.Opportunity_T__c',
                'salesforce_Schedule__c.Start__c', 'salesforce_Schedule__c.End__c', 'salesforce_Schedule__c.Status__c', 'salesforce_Record_Type.Name as Booking_Type', 'salesforce_Schedule__c.PT_Assign__c'])
            ->where('salesforce_Record_Type.DeveloperName', 'T_Booking')
            ->where('salesforce_Schedule__c.IsDeleted', 0)
            ->where('salesforce_Opportunity.Sales_Assign__c', $user->sf_account_id());

        if( isset($input['date']) && !empty($input['date']) ){
            $startDate = Carbon::createFromFormat('m/d/Y H:i:s', $input['date'].' 00:00:00');

            $query->where('salesforce_Schedule__c.Start__c', '>', $startDate )
                ->where('salesforce_Schedule__c.Start__c', '<', $startDate->copy()->endOfDay() );
        } //else $query->whereDate('salesforce_Schedule__c.CreatedDate', Carbon::today() );

        $data = $query->paginate($per_page);
        if ( count($data) == 0 ) return response()->json(['message' => 'No data found!'], 404);
        else return response()->json($data, 200);
    }
}
