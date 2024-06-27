<?php
/**
 * @author tmtuan
 * created Date: 30-Dec-20
 */
namespace App\Domains\Crm\Http\Controllers\Api\Booking;

use App\Domains\Auth\Models\User;
use App\Domains\Crm\Http\Controllers\Traits\sfBooking;
use App\Domains\Crm\Models\Club;
use App\Domains\Crm\Models\Contract;
use App\Domains\Crm\Models\ContractSale;
use App\Domains\Crm\Models\Opportunity;
use App\Domains\Crm\Models\RecordType;
use App\Domains\Crm\Models\Schedule;
use App\Domains\Crm\Models\SfAcccount;
use App\Domains\TmtSfObject\Classes\SObject;
use App\Services\sfconnect\sfconnect;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Domains\Crm\Http\Controllers\Api\Contract\ContractController;
use Illuminate\Support\Facades\DB;
use function Couchbase\defaultDecoder;

class PtBookingController extends BookingController {
    use sfBooking;

    /**
     * 10-Jun-2021 Fix update theo task: https://beunik.atlassian.net/browse/CIT-597
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listPTBookings(Request $request) {
        $user = auth()->user();
        if ( !$user->hasRole('PT') ) return response()->json(['message' => 'You don\'t have permission to perform this action!'], 401);
        if (!$user->sf_account_id()) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid sf_account_id'
            ], 404);
        }
        $input = $request->query();
        $per_page = $input['per_page'] ?? 15;

        $rcType = RecordType::select('Id')
        ->where(function ($qr){
            $qr->where('DeveloperName', 'F_Booking');
        })
        ->where('SobjectType', 'Schedule__c')->get()->toArray();
        $recordTypes = [];
        foreach ($rcType as $rcItem ) $recordTypes[] = $rcItem['Id'];
        try {
            DB::enableQueryLog();
            $query = Schedule::join('salesforce_Record_Type', 'salesforce_Record_Type.Id', '=', 'salesforce_Schedule__c.RecordTypeId')
                ->with(['OpportunityF' => function($md){
                    $md->select(['Id', 'Name']);
                }])
                ->with(['OpportunityT' => function($md){
                    $md->select(['Id', 'Name']);
                }])
                ->with(['PtAssign' => function($md){
                    $md->select(['Id', 'Name']);
                }])
                ->with(['Contract' => function($ct){
                    $ct->select(['Id', 'AccountId','Contract_Code__c'])
                        ->with(['Account' => function($acc){
                            $acc->select(['Id', 'Name']);
                        }]);
                }])
                ->select(['salesforce_Schedule__c.Id as Schedule_Id', 'salesforce_Schedule__c.Opportunity_T__c', 'salesforce_Schedule__c.Opportunity_F__c',
                    'salesforce_Schedule__c.Start__c', 'salesforce_Schedule__c.End__c', 'salesforce_Schedule__c.Status__c', 'salesforce_Record_Type.Name as Booking_Type',
                    'salesforce_Schedule__c.PT_Assign__c', 'salesforce_Schedule__c.Contract__c'
                ])
                ->where('IsDeleted', 0)
                ->where('PT_Assign__c', $user->sf_account_id())
                ->wherein('salesforce_Record_Type.Id', $recordTypes );

            if( isset($input['date']) && !empty($input['date']) ){
                $startDate = Carbon::createFromFormat('m/d/Y H:i:s', $input['date'].' 00:00:00');

                $query->where('salesforce_Schedule__c.Start__c', '>', $startDate )
                    ->where('salesforce_Schedule__c.Start__c', '<', $startDate->copy()->endOfDay() );
            }
            $data = $query->paginate($per_page);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        if ( count($data) == 0 ) return response()->json(['message' => 'No data found!'], 404);
        else return response()->json($data, 200);
    }

    /**
     * Lấy danh sách booking burnshow
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listBurnShowBooks(Request $request) {

        $user = auth()->user();
        if ( !$user->hasRole('PT') ) return response()->json(['message' => 'You don\'t have permission to perform this action!'], 401);
        if (!$user->sf_account_id()) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid sf_account_id'
            ], 404);
        }
        $input = $request->query();
        $per_page = $queryData['per_page'] ?? 15;

        $rcType = RecordType::select('Id')
            ->where('DeveloperName', 'PT_Session')
            ->where('SobjectType', 'Schedule__c')->get()->first();

        $query = Schedule::join('salesforce_Record_Type', 'salesforce_Record_Type.Id', '=', 'salesforce_Schedule__c.RecordTypeId')
            ->with(['OpportunityF' => function($md){
                $md->select(['Id', 'Name']);
            }])
            ->with(['OpportunityT' => function($md){
                $md->select(['Id', 'Name']);
            }])
            ->with(['PtAssign' => function($md){
                $md->select(['Id', 'Name']);
            }])
            ->with(['Contract' => function($ct){
                $ct->select(['Id', 'AccountId','Contract_Code__c'])
                    ->with(['Account' => function($acc){
                        $acc->select(['Id', 'Name']);
                    }]);
            }])
            ->select(['salesforce_Schedule__c.Id as Schedule_Id', 'salesforce_Schedule__c.Opportunity_T__c', 'salesforce_Schedule__c.Opportunity_F__c',
                'salesforce_Schedule__c.Start__c', 'salesforce_Schedule__c.End__c', 'salesforce_Schedule__c.Status__c', 'salesforce_Record_Type.Name as Booking_Type',
                'salesforce_Schedule__c.PT_Assign__c', 'salesforce_Schedule__c.PT_Check_In__c', 'salesforce_Schedule__c.MS_Check_In__c', 'salesforce_Schedule__c.Member_Check_In__c',
                'salesforce_Schedule__c.Contract__c','salesforce_Schedule__c.Contract_Code__c'
            ])
            ->where('IsDeleted', 0)
            ->where('PT_Assign__c', $user->sf_account_id())
            ->where('salesforce_Record_Type.Id', $rcType->Id );

        if( isset($input['date']) && !empty($input['date']) ){
            $startDate = Carbon::createFromFormat('m/d/Y H:i:s', $input['date'].' 00:00:00');

            $query->where('salesforce_Schedule__c.Start__c', '>', $startDate )
                ->where('salesforce_Schedule__c.Start__c', '<', $startDate->copy()->endOfDay() );
        }
        $data = $query->paginate($per_page);
//dd($data->start_time);
        if ( count($data) == 0 ) return response()->json(['message' => 'No data found!'], 404);
        else return response()->json($data, 200);
    }

    /**
     * màn hình booking F cho PT chỉ load ra những oppty có recordtype là Individual_PT
     * đối với buổi F thì tổng số buổi F được đặt sẽ lấy trong table Oppotunity với field là  F_limitation__c và RecordTypeId là Individual_PT. Nếu số buổi F > F_limitation__c
     * hệ thống vẫn cho PT book nhưng sẽ thông báo cho parent PT biết
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkFBooking(Request $request) {
        $user = auth()->user();
        $returnData = [];
        $returnData['bk_setting'][] = $this->getSfBookingSetting();

        // get oppty
        $oppties = Opportunity::join('salesforce_Record_Type', 'salesforce_Record_Type.Id', '=', 'salesforce_Opportunity.RecordTypeId')
            ->select(['salesforce_Opportunity.Id', 'RecordTypeId', 'salesforce_Opportunity.name', 'PT_assign__c', 'F_limitation__c'])
            ->where('salesforce_Record_Type.SobjectType', 'Opportunity')
            ->where('salesforce_Record_Type.DeveloperName', 'Individual_PT')
            ->where('IsDeleted', 0)
            ->where('PT_assign__c', $user->sf_account_id())->get();
        $returnData['oppty'] = $oppties??[];

        return response()->json($returnData, 200);
    }

    /**
     * Đặt F Booking
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bookFSchedule(Request $request) {
        $user = auth()->user();
        $postData = $request->post();

        //get F record Type
        $recordType = RecordType::where('SobjectType', 'Schedule__c')
            ->where('DeveloperName', 'F_Booking')->get()->first();

        //check booking
        $bkChk = Schedule::where('Opportunity_T__c', $postData['Oppty'])
            ->where('RecordTypeId', $recordType->Id)
            ->where('IsDeleted', 0)
            ->count();
        if ( $bkChk >= $postData['schedule_limit'] ) {
            //report to parent PT
        }

        //Schedule Data
        $startTime = Carbon::parse($postData['Start_Time']);
        $scheduleData = [
            "RecordTypeId" => $recordType->Id??'',
            "PT_Assign__c" => $user->sf_account_id(),
            "Status__c" => "Open",
            "Opportunity_F__c" => $postData['Oppty'],
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
            $newItem->save();
            //log
            activity('sale_pt_app')
                ->causedBy($newItem)
                ->withProperties(['schedule' => $newItem->toArray()])
                ->log('Schedule F| create new schedule F #'.$response);
        } catch ( \Exception $e) {
            return response()->json($e->getMessage(), 422);
        }

        return response()->json(['message' => 'Đặt lịch thành công', 'data' => $newItem], 201);
    }

    /**
     * Lấy danh sách khách hàng Theo Object Contract Sale -> Lấy ra toàn bộ các Contract có mối quan hệ với thằng PT đó
     * --> Lọc ra những Contract có Status = Activated --> Lấy Account của những thằng Contract này ra --> Load lên đây
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkBurnShow(Request $request) {
        $user = auth()->user();
        $returnData = [];
        $input = $request->get('debug');
//        $returnData['bk_setting'][] = $this->getSfBookingSetting();
        \DB::enableQueryLog();
        $ctSale = ContractSale::select(['Id', 'Contract__c', 'Name', 'Sales__c'])->where('Sales__c', $user->sf_account_id()??$user->SfAccount)->get();
        if ( empty($ctSale) ) return response()->json(['message' => 'Invalid Request']);
        if ( $ctSale->count() == 0 ) return response()->json(['message' => 'No Item Found'], 404);

        $cusData = [];
        $fieldIN = "";
        foreach ( $ctSale as $item ) {
            if ( !empty($item) ) {
                if($fieldIN == "")
                    $fieldIN.= "'".$item->Contract__c."'";
                else
                    $fieldIN.= ","."'".$item->Contract__c."'";
            }
        }
        $contracts = ContractController::getSFContracts($fieldIN); //dd($contracts);
        $cusData = [];
        foreach ( $contracts as $ct ) {
            if ( !empty($ct) ) {
                if ( isset($ct->AccountId) ) {
                    if ( !in_array($ct->AccountId, $cusData) ) $cusData[] = $ct->AccountId;
                }
            }
        } if ( $input ) //dd($contracts);
//        dd($cusData);
        //\DB::enableQueryLog();
        $customers = [];
        $customers = SfAcccount::join('salesforce_Contract', 'salesforce_Contract.AccountId', '=', 'salesforce_Account.Id')
                    ->select(['salesforce_Contract.Id as ContractId', 'salesforce_Account.Id', 'salesforce_Account.Name'])
                    ->whereIn('salesforce_Account.Id', $cusData)
                    ->groupBy('salesforce_Account.Id')->get();
        // get customer
        //dd(\DB::getQueryLog());
//        $customers = SfAcccount::join('salesforce_Contract', 'salesforce_Contract.AccountId', '=', 'salesforce_Account.Id')
//                    ->with(['Contract' => function($qr){
//                        $qr->with(['Product' => function($pdtQr){
//                            $pdtQr->with('productData');
//                        }]);
//                    }])
//                    ->join('salesforce_Contract_Sale__c', 'salesforce_Contract_Sale__c.Contract__c', '=', 'salesforce_Contract.Id')
//                    ->select(['salesforce_Account.Id', 'salesforce_Account.Name', 'salesforce_Contract.Id as Contract'])
//                    ->where('salesforce_Contract_Sale__c.Sales__c', $user->sf_account_id())
//                    ->where('salesforce_Contract.Status', 'Activated')
//                    ->get(); //dd(\DB::getQueryLog());

        $sfconnect = new sfconnect();

        foreach ($customers as $cus) {
            $query = "SELECT Id, Product__c, Session_Duration__c" ;
            $query .= " FROM Contract_Product__c ";
            $query .= " WHERE contract__c = '" . $cus->ContractId . "'";
            try {
                $rs = $sfconnect->callQuery($query);
                if (isset($rs['success']) && $rs['success'] == false) return [];
                $cus->product = $rs;
            } catch (Exception $e) {
                continue;
            }

        }
//        $returnData['customers'] = $customers??null;

        //get club
//        $club = Club::select(['Id', 'Name'])->get();
//        $returnData['club'] = $club;

        return response()->json($customers??null, 200);
    }

    public function getClub(Request $request) {
        //get club
        $club = Club::select(['Id', 'Name'])
            ->where('IsDeleted', 0)
            ->where('Hide_In_Print_View__c', 0)
            ->where('Is_TM__c', 0)
            ->get();

        return response()->json($club, 200);
    }

    public function getCustomerContract(Request $request) {
        $cusId = $request->get('customer');
        $user = auth()->user();
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);

        $input = $request->query();


        /**********************************************************************************/
        /**
         * old code get contract from SF
         */
        $ContractSale = ContractSale::leftJoin('salesforce_Contract', 'salesforce_Contract.Id', '=', 'salesforce_Contract_Sale__c.Contract__c')
            ->select(['salesforce_Contract_Sale__c.Contract__c'])
            ->where('salesforce_Contract_Sale__c.IsDeleted', 0)
            ->where('salesforce_Contract.AccountId', $cusId)
            ->where('salesforce_Contract_Sale__c.Sales__c', $user->sf_account_id())->get();

        $fieldIN = "";
        foreach ($ContractSale as $item) {
            if($fieldIN == "")
                $fieldIN.= "'".$item->Contract__c."'";
            else
                $fieldIN.= ","."'".$item->Contract__c."'";
        }
        $sfconnect = new sfconnect();

        $query = "SELECT Id,Name,AccountId,ContractNumber,Contract_Number__c,Status";
        $query .= " FROM Contract ";
        $query .= " WHERE Status != 'Draft' AND Id IN  (".$fieldIN.")";

        try {
            $rs = $sfconnect->callQuery($query);
            return response()->json($rs);
        } catch (ClientException $e) {
            $mess = $e->getResponse()->getBody()->getContents();
            $mess1 = json_decode($mess);
            return response()->json(['message' => $mess1[0]->message], 422);
        }

    }

    /**
     * Lấy danh sách hợp đồng của khách hàng trong booking
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCustomerContractLocal(Request $request) {
        $cusId = $request->get('customer');
        $user = auth()->user();
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);

        $input = $request->query();

        /**********************************************************************************/
        /**
         * new code use object contract from DB
         */
        $customerContracts = Contract::leftJoin('salesforce_Contract_Sale__c', 'salesforce_Contract_Sale__c.Contract__c', '=', 'salesforce_Contract.Id')
            ->select(['salesforce_Contract_Sale__c.Contract__c', 'salesforce_Contract.Id', 'salesforce_Contract.Name', 'salesforce_Contract.AccountId', 'salesforce_Contract.ContractNumber',
                'salesforce_Contract.Contract_Number__c', 'salesforce_Contract.Status'])
            ->where('salesforce_Contract_Sale__c.IsDeleted', 0)
            ->where('salesforce_Contract.AccountId', $cusId)
            ->where('salesforce_Contract.Status', '!=', 'Draft')
            ->where('salesforce_Contract_Sale__c.Sales__c', $user->sf_account_id())->get();

        if (!empty($customerContracts) ) return response()->json($customerContracts);
        else return response()->json(['message' => 'No item found!'], 404);
    }

    /**
     * Đặt burn show book
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function burnShow(Request $request) {
        $user = auth()->user();
        $postData = $request->post();

        //get PT Session record Type
        $recordType = RecordType::where('SobjectType', 'Schedule__c')
            ->where('DeveloperName', 'PT_Session')->get()->first();

        /**
         * check booking sau
         */

        //Schedule Data
        $startTime = Carbon::parse($postData['Start_Time']);
        $scheduleData = [
            "RecordTypeId" => $recordType->Id??'',
            "PT_Assign__c" => $user->sf_account_id(),
            "Contract__c" => $postData['Contract'],
            "Status__c" => $postData['Status'],
            "Club_PT_Session__c" => $postData['Club'],
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
            $newItem->save();
            //log
            activity('sale_pt_app')
                ->causedBy($newItem)
                ->withProperties(['schedule' => $newItem->toArray()])
                ->log('Schedule Burn Show| create new Burn Show #'.$response);
        } catch ( \Exception $e) {
            return response()->json($e->getMessage(), 422);
        }

        return response()->json(['message' => 'Đặt lịch thành công', 'data' => $newItem], 201);
    }

    /**
     * PT thực hiện confirm buổi tập Burnshow
     * PT_Check_In__c => true nếu user là PT
     * Member_Check_In__c => true nếu user là Member
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function confirmBurnShow(Request $request) {
        $user = auth()->user();
        $postData = $request->all();

        if ( !$user->hasRole(['PT']) ) return response()->json(['message' => 'You dont\'t have permission on this action!'], 401);

        $validator = \Validator::make($postData, [
            'booking' => 'sometimes|required',
            'PT_Check_In' => 'sometimes|required',
            'Member_Check_In' => 'sometimes|required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->messages();
            return response()->json([
                'status' => false,
                'message' => $messages
            ], 422);
        } else {
            $item = Schedule::find($postData['booking']);
            if ( empty($item) ) return response()->json(['message' => 'Invalid Item!'], 404);

            DB::beginTransaction();
            try {
                $schedule = [
                    'PT_Check_In__c' => $postData['PT_Check_In']
                ];
                $response = SObject::update('Schedule__c', $postData['booking'], $schedule);
                if ( isset($response->status_code) && $response->status_code == 400 ) return response()->json(['message' => $response->message, 'data' => $postData], $response->status_code);
                $item->PT_Check_In__c = true;
                $item->save();
                //log
                activity('sale_pt_app')
                    ->withProperties(['Schedule' => $item->toArray()])
                    ->log('Schedule| update Schedule success  #'.$item->Id);

                DB::commit();
                return response()->json(['message' => 'Confirm Success'], 200);
            } catch ( \Exception $e) {
                DB::rollBack();
                return response()->json($e->getMessage(), 422);
            }
        }
    }


    /**
     * https://beunik.atlassian.net/browse/CIOS-169
     *
     * @param Request $request
     * @param date format m/d/Y
     * @return mixed
     */
    public function listCustomerBurnshow(Request $request) {
        $user = auth()->user();
        $postData = $request->all();

        //get PT Session record Type
        $recordType = RecordType::where('SobjectType', 'Schedule__c')
            ->where('DeveloperName', 'PT_Session')->get()->first();

        $query = Schedule::with([
            'PTAssign' => function ($md) {
                $md->select(['Id', 'Name', 'LastName', 'FirstName', 'PersonMobilePhone']);
            },
            'Trainer1' => function ($md) {
                $md->select(['Id', 'Name','LastName','FirstName']);
            },
            'Trainer2' => function ($md) {
                $md->select(['Id', 'Name','LastName','FirstName']);
            },
            'Trainer3' => function ($md) {
                $md->select(['Id', 'Name','LastName','FirstName']);
            },
            'Contract' => function ($md) {
                $md->select(['Id', 'Contract_Number_Member__c','Contract_Number_PT__c','Contract_Number__c', 'Contract_Number_Searchable__c']);
            },
            'ClubPTSession' => function ($md) {
                $md->select(['Id', 'Name']);
            },
        ])
            ->where('IsDeleted', 0)
            ->where('RecordTypeId', $recordType->Id??'')
            ->where('Account_Name__c', $user->sf_account_id());

        if( isset($postData['date']) && !empty($postData['date']) ){
            $startDate = Carbon::createFromFormat('m/d/Y H:i:s', $postData['date'].' 00:00:00');

            $query->where('Start__c', '>', $startDate )
                ->where('Start__c', '<', $startDate->copy()->endOfDay() );
        }
        $schedules = $query->orderBy('Start__c', 'asc')->get();

        foreach ($schedules as $item) {
            if ( isset($item->PTAssign) ) {
                $userData = User::join('user_sf_account', 'user_sf_account.user_id', '=', 'users.id')
                    ->select(['id', 'avata', 'created_at'])
                    ->where('sf_account_id', $item->PTAssign->Id)->first();
                $item->PTAssign->avatar = $userData->avata;
            }
        }
        return response()->json($schedules);
    }


    /**
     * Member thực hiện confirm buổi tập Burnshow
     * Member_Check_In__c => true nếu user là Member
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function memberConfirmBurnShow(Request $request) {
        $postData = $request->all();

        $validator = \Validator::make($postData, [
            'booking' => 'sometimes|required',
            'Member_Check_In' => 'sometimes|required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->messages();
            return response()->json([
                'status' => false,
                'message' => $messages
            ], 422);
        } else {
            $item = Schedule::find($postData['booking']);
            if ( empty($item) ) return response()->json(['message' => 'Invalid Item!'], 404);

            DB::beginTransaction();
            try {
                $schedule = [
                    'Member_Check_In__c' => ($postData['Member_Check_In'] == 1 ) ? true : false
                ];
                $response = SObject::update('Schedule__c', $postData['booking'], $schedule);
                if ( isset($response->status_code) && $response->status_code == 400 ) return response()->json(['message' => $response->message, 'data' => $postData], $response->status_code);
                $item->PT_Check_In__c = true;
                $item->save();
                //log
                activity('sale_pt_app')
                    ->withProperties(['Schedule' => $item->toArray()])
                    ->log('Schedule| update Schedule success  #'.$item->Id);

                DB::commit();
                return response()->json(['message' => 'Confirm Success'], 200);
            } catch ( \Exception $e) {
                DB::rollBack();
                return response()->json($e->getMessage(), 422);
            }
        }
    }
}
