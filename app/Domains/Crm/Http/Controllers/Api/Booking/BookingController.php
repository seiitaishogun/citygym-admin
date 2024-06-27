<?php
/**
 * @author tmtuan
 * created Date: 09-Dec-20
 */

namespace App\Domains\Crm\Http\Controllers\Api\Booking;

use App\Domains\Crm\Http\Controllers\Api\Contract\ContractController;
use App\Domains\Crm\Models\Club;
use App\Domains\Crm\Models\ContractSale;
use App\Domains\Crm\Models\Schedule;
use App\Domains\Crm\Models\ScheduleHV;
use App\Domains\Crm\Models\ScheduleTrainer;
use App\Domains\TmtSfObject\Classes\SObject;
use App\Http\Controllers\Api\ApiController;
use App\Services\sfconnect\sfconnect;
use GuzzleHttp\Exception\ClientException;
use http\Env\Response;
use Illuminate\Http\Request;
use App\Domains\Crm\Models\Contract;


use App\Domains\Crm\Models\SfAcccount;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BookingController extends ApiController
{

//    public function Booking(Request $request) {
//
//        $user = auth()->user();
//        if ( empty($user) ) return response()->json([
//            'status' => false,
//            'message' => 'Invalid Request'
//        ], 400);
//
//        $item = $request->post();
//
//            $bkItem = Schedule::find($item['Id']);
//            if ( !empty($bkItem) ) {
//                return response()->json(['message' => "This schedule Id #{$item['Id']} is exist", 'item' => $item], 409);
//            }
//
//            $schedule = Schedule::create($item);
//            //log
//            activity('salesforce_api')
//                ->causedBy($schedule)
//                ->withProperties(['schedule' => $item])
//                ->log('Booking| create new schedule #'.$item['Id']);
//
//            //insert schedule trainer
//            $scheduleTrainerItem = ScheduleTrainer::find($item['scheduleTrainer']['Id']);
//            if ( !empty($scheduleTrainerItem) ) $scheduleTrainerItem->forceDelete();
//
//            $scheduleTrainer = ScheduleTrainer::create($item['scheduleTrainer']);
//            //log
//            activity('salesforce_api')
//                ->causedBy($scheduleTrainer)
//                ->withProperties(['schedule_trainer' => $item['scheduleTrainer']])
//                ->log('Booking| create new schedule trainer #'.$item['scheduleTrainer']['Id']);
//
//
//            // //insert schedule HV
//            // $scheduleHVItem = ScheduleHV::find($item['scheduleHV']['Id']);
//            // if ( !empty($scheduleHVItem) ) $scheduleHVItem->forceDelete();
//
//                $item['scheduleHV']['Schedule__c'] = $schedule->Id;
//                $item['scheduleHV']['Account__c'] = $user->sf_account_id();
//
//            $scheduleHV = ScheduleHV::create($item['scheduleHV']);
//            //log
//            activity('api')
//                ->causedBy($scheduleHV)
//                ->withProperties(['schedule_hv' => $item['scheduleHV']])
//                ->log('Booking| create new schedule HV item #'.$item['scheduleHV']['Id']);
//
//
//        $returnData = [
//            'message' => 'Schedule Data Created Successfull'
//        ];
//        return response()->json($returnData, 201);
//    }


    /**
     * Sep-22-2021 - https://beunik.atlassian.net/browse/CIOS-184
     * Đổi query từ class group -> class
     * @param Request $request
     * @return mixed
     */
    public function listBooking(Request $request)
    {
        $user = auth()->user();

        $input = $request->query();
        $per_page = $input['per_page'] ?? 15;
        $search = $input['search'] ?? '';
        $page = $input['page'] ?? 1;

        $date = $input['date'] ?? '';
        $time = $input['time'] ?? '';
        $class = $input['class'] ?? '';
        $class_group = $input['class_group'] ?? '';

        $startDate = $input['startTime'] ?? '';
        $endDate = $input['endTime'] ?? '';

        $club = '';
        if ((!isset($input['club']) || empty($input['club'])) && $user) {
            if ( $user->hasRole('MS') ) {
                $club = $user->club_id.',';
            } else {
                //Lấy danh sách các club của user theo hợp đồng
                $ctData = Contract::where('AccountId', $user->sf_account_id())->where('IsDeleted', 0)->get()->toArray();
                $ctIds = '';
                $total = count($ctData);
                if ($total == 0)
                    return response()->json(['message' => 'User don\'t have any contract'], 404);
                $i = 1;
                foreach ($ctData as $ct) {
                    if ($i < $total) $ctIds .= "'" . $ct['Id'] . "'" . ',';
                    else $ctIds .= "'" . $ct['Id'] . "'";
                    $i++;
                }

                $sfconnect = new sfconnect();
                $query = "SELECT Id, Club__c ";
                $query .= " FROM Contract_Club__c ";
                $query .= " WHERE Contract__c IN (" . $ctIds . ")";
                try {
                    $rs = $sfconnect->callQuery($query);
                    if (!empty($rs) && is_array($rs)) {
                        $totalit = count($rs);
                        $i = 1;
                        foreach ($rs as $item) {
                            if ($i < $totalit) {
                                if ( isset($item->Club__c) ) $club .= $item->Club__c . ',';
                            }
                            else $club .= $item->Club__c;
                        }
                    }
                } catch (Exception $e) {
                    return response()->json(['Can not connect to Server!'], 500);
                }
            }

        } elseif (isset($input['club'])) $club =  $input['club'];
        else {
            $clubData = Club::all();
            $totalit = count($clubData);
            $i = 1;
            foreach ($clubData as $item) {
                if ($i < $totalit) $club .= $item->Id . ',';
                else $club .= $item->Id;
            }
        }
        $option = [
            "date" => $date,
            "time" => $time,
            "class" => $class,
            "class_group" => $class_group,
            "club" => $club,
            "search" => $search,
            "page" => $page,
            "recordType" => [
                "DeveloperName" => "Lich_Giang_Day",
                "SobjectType" => "Schedule__c",
            ],
            "startTime" => $startDate,
            "endTime" => $endDate,
            'user' => $user,
            'per_page' => $per_page
        ];

        //Lấy danh sách lớp
        /**
         * Nov-09-2021 - https://beunik.atlassian.net/browse/CIOS-215
         * lấy recordtype = Lich Giảng Dạy
         */
        if ( $user ) $query = Schedule::getMemberScheduleById($user?$user->sf_account_id():null, $option);
        else $query = Schedule::listSchedule($option);  //lấy danh sách lịch khi user không login

        /**
         * Kiểm tra xem các lớp này user đang login có đặt lịch hay chưa?
         * Nếu chưa đặt thì sẽ hiển thị ngược lại không hiển thị
         * Nếu user được phép book cho người đi kèm ( is_guest_booking = 1 ) thì không cần loại bỏ lịch
         */
        if ( $user && !isset($input['is_guest_booking']) ) {
            $response = [];
            foreach ( $query as $item ) {
                $scheduleHV = ScheduleHV::where('Schedule__c', $item->Id)
                    ->whereIn('HV_Status__c', ['Booked', 'Queue'])
                    ->where('Account__c', $user->sf_account_id())
                    ->first();
                if ( !isset($scheduleHV->Id) ) $response[] = $item;
            }
            return response()->json(['data' =>$response], 200);
        } else return response()->json($query, 200);

    }

    public function listAcceptBooking(Request $request)
    {
        $user = auth()->user();
        //Son Add Role Sale,Pt
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);
        if (!$user->sf_account_id()) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid sf_account_id'
            ], 404);
        }
        $input = $request->query();
        $per_page = $input['per_page'] ?? 15;
        $search = $input['search'] ?? '';
        $page = $input['page'] ?? 1;

        $date = $input['date'] ?? '';
        $time = $input['time'] ?? '';
        $class = $input['class'] ?? '';
        $club = $input['club'] ?? '';
        $startDate = $input['startTime'] ?? '';
        $endDate = $input['endTime'] ?? '';
        $option = [
            "date" => $date,
            "time" => $time,
            "class" => $class,
            "club" => $club,
            "search" => $search,
            "page" => $page,
            "recordType" => [
                "DeveloperName" => "PT_Session",
                "SobjectType" => "Schedule__c",
            ],
            "startTime" => $startDate,
            "endTime" => $endDate,
        ];
        $query = Schedule::getMemberScheduleById($user->sf_account_id(), $option);
        return response()->json($query, 200);
    }

    public function listUserBooking(Request $request)
    {
        $user = auth()->user();
        if (!$user->sf_account_id()) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid sf_account_id'
            ], 404);
        }
        $input = $request->query();
        $per_page = $input['per_page'] ?? 15;
        $search = $input['search'] ?? '';
        $page = $input['page'] ?? 1;

        $date = $input['date'] ?? '';
        $time = $input['time'] ?? '';
        $class = $input['class'] ?? '';
        $club = $input['club'] ?? '';
        $option = [
            "date" => $date,
            "time" => $time,
            "class" => $class,
            "club" => $club,
            "search" => $search,
            "page" => $page,
            "per_page" => $per_page,
        ];

        $data = ScheduleHV::getScheduleById($user->sf_account_id(), $option);
        return response()->json($data, 200);
    }

    /**
     * Xử lý đặt lịch tập cho member
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function memberBookingHv(Request $request)
    {
        $user = auth()->user();
        if (!$user->sf_account_id()) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid sf_account_id'
            ], 400);
        }
        $postData = $request->post();
        $Schedule__c = $postData['Schedule__c'] ?? '';

        if (!isset($Schedule__c) || empty($Schedule__c)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Schedule__c'
            ], 400);
        }
        $schedule = Schedule::where('Id', $Schedule__c)->get()->first();

        if ( !isset($schedule->Id) ) return response()->json([
            'status' => false,
            'message' => 'Invalid Schedule__c'
        ], 400);

        //validate booking
        $postData['Account__c'] = $user->sf_account_id();
        $check = ScheduleHV::validateBookingScheduleHv($postData, $schedule);

        if ( $check['status'] === false ) return response()->json([
            'status' => false,
            'message' => $check['message']
        ], 422);

        $newItem = new ScheduleHV();
        $isGuest = (isset($postData['is_guest_booking']) && $postData['is_guest_booking'] == 1 ) ? true : false;
        $scheduleData = [
            'Account__c' => $user->sf_account_id(),
            'Schedule__c' => $postData['Schedule__c'] ?? '',
            'Registered_Guest__c' => $postData['guest_number'] ?? 0,
            'Source__c' => 'App',
            'HV_Status__c' => 'Booked',
            'Is_Guest__c' => $isGuest
            //'Is_Guest__c' => $postData['Is_Guest__c'] ?? 0
        ];

        //create schedule record in SF
        try {
            $response = SObject::create('Schedule_HV__c', $scheduleData);
            $response = (object) $response;

            if( isset($response->status_code)
                && in_array($response->status_code, [400, 404]) ) return response()->json([
                        'message' => $response->message,
                        'data' => $scheduleData],
                        $response->status_code);

            //set date
            $this->setDefaultDate($scheduleData);

            $newItem->fill($scheduleData);
            $newItem->Id = $response->scalar;
            $newItem->is_sync_crm = 1;
//            $newItem->sync_result = $response->status_code;
//            $newItem->sync_result = 200;
//            if ($response->status_code == 200)
//            $newItem->last_sync_success = date('Y-m-d H:i:s');
//            dd($newItem->toArray());
            $newItem->save();
            //log
//            activity('member_app')
//                ->causedBy($newItem)
//                ->withProperties(['schedule hv' => $newItem->toArray()])
//                ->log('Schedule HV| create new schedule HV #' . $response);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 422);
        }

        return response()->json($newItem, 200);
    }


    /**
     * Xử lý đặt lịch tập - Quy trình cũ
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function UserBooking(Request $request) {
        $now = Carbon::now();
        $user = auth()->user();
        //Son Add Role Sale,Pt
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);
        if (!$user->sf_account_id()) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid sf_account_id'
            ], 400);
        }
        $postData = $request->post();
        $Schedule__c = $postData['Schedule__c']??'';

        if (!isset($Schedule__c) || empty($Schedule__c)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Schedule__c'
            ], 400);
        }

        $newItem = new ScheduleHV();

        $scheduleData = [
            'Account__c' => $user->sf_account_id(),
            'Schedule__c' => $postData['Schedule__c']??'',
            'Registered_Guest__c' => $postData['guest_number']??0,
            'Source__c' => 'App',
            'HV_Status__c' => 'Booked',
        ];

        //create schedule record in SF
        try {
            $response = SObject::create('Schedule_HV__c', $scheduleData);
            if ( isset($response->status_code) && in_array($response->status_code, [400,404]) ) return response()->json(['message' => $response->message, 'data' => $scheduleData], $response->status_code);

            //set date
            $this->setDefaultDate($scheduleData);

            $newItem->fill($scheduleData);
            $newItem->Id = $response;
            // $newItem->End__c = $postData['End_Time'];
            $newItem->save();
            //log
            activity('member_app')
                ->causedBy($newItem)
                ->withProperties(['schedule hv' => $newItem->toArray()])
                ->log('Schedule HV| create new schedule HV #'.$response);
        } catch ( \Exception $e) {
            return response()->json($e->getMessage(), 422);
        }

        return response()->json($newItem, 200);
    }

    /**
     * Xử lý lưu đặt lịch tại CMS trước và sau 5 phút sẽ có cron chạy để đồng bộ trực tiếp lên SF
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function CreateScheduleHv(Request $request)
    {
        $now = Carbon::now();
        $user = auth()->user();
        //Son Add Role Sale,Pt
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);
        if (!$user->sf_account_id()) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid sf_account_id'
            ], 400);
        }
        $postData = $request->post();
        $Schedule__c = $postData['Schedule__c'] ?? '';

        if (!isset($Schedule__c) || empty($Schedule__c)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Schedule__c'
            ], 400);
        }

        // kiem tra nguoi dung con hop dong
        $Contract = Contract::where('AccountId', $user->sf_account_id())
        //                     ->where('Status','Activated')
        //                     ->where('StartDate','<=',$now->toDateTimeString())
        //                     ->where('EndDate','>=',$now->toDateTimeString())
            ->first();
        if (!$Contract) {
            return response()->json([
                'status' => false,
                'message' => 'contract is not available'
            ], 400);
        }
        //kiểm tra trùng lịch
        $checkOverlap = ScheduleHV::where('Schedule__c', $Schedule__c)
                        ->where('Account__c', $user->sf_account_id())
                        ->first();
        if ( isset($checkOverlap->Id) ) return response()->json([
            'status' => false,
            'message' => 'Bạn đã đặt lịch vào lớp này rồi!'
        ], 400);
        //kiem tra thoi gian lich
        //kiem tra lop full hay chua
        $schedule = Schedule::find($Schedule__c);
        if (!isset($schedule->Id)) {
            return response()->json([
                'status' => false,
                'message' => 'Lớp này không tồn tại!'
            ], 400);
        } else {
            if ( $schedule->Start__c->diffInMinutes() <= 30 ) return response()->json([
                'status' => false,
                'message' => 'Lớp này đã sắp tới giờ học và không được phép booking online!'
            ], 400);

            $checkCapacity = ScheduleHV::where('Schedule__c', $Schedule__c)->count();
            if ($checkCapacity + 1 > $schedule->Capacity__c) return response()->json([
                'status' => false,
                'message' => 'Lớp này đã đầy, không thể book tiếp được!'
            ], 400);
        }

        $newItem = new ScheduleHV();

        $newItem->Name = $postData['Name'] ?? '';
        $newItem->CreatedDate = $now->toDateTimeString();
        $newItem->LastModifiedDate = $now->toDateTimeString();
        $newItem->Schedule__c = $postData['Schedule__c'] ?? '';
        $newItem->Account__c = $user->sf_account_id();
        $newItem->Source__c = 'App';
        $newItem->HV_Status__c = 'Booked';
        $newItem->Registered_Guest__c = $postData['guest_number'] ?? 0;
        $newItem->is_sync_crm = 0;
        $newItem->IsDeleted = 0;
        //get the id
        $hvIds = ScheduleHV::where('is_sync_crm', 0)->count();
        $newItem->Id = $hvIds + 1;
        $newItem->save();
        //log
        activity('member_app')
            ->causedBy($newItem)
            ->withProperties(['schedule hv' => $newItem->toArray()])
            ->log('Schedule HV| create new schedule HV');

        return response()->json($newItem, 200);
    }

    /**
     * Xử lý checkin member vào lớp
     * MS scan QR code của member và gửi lên ID của ScheduleHV
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkin(Request $request)
    {
        $postData = $request->post();
        $user = auth()->user();
        //Son Add Role Sale,Pt
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);
        if (empty($user)) return response()->json([
            'status' => false,
            'message' => 'Invalid Request'
        ], 400);

        $sf_account_id = $user->sf_account_id(); //lấy sf account id của MS

        if (!$sf_account_id) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid sf_account_id'
            ], 400);
        }

        if (!isset($postData['id']) || empty($postData['id'])) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid id'
            ], 400);
        }
        $scheduleData = ScheduleHV::where('Id', $postData['id'])->first();

        if( !isset($scheduleData->Id) ) return  response()->json([
            'status' => false,
            'message' => 'Lịch này không còn tồn tại!'
        ], 400);

        if( $scheduleData->is_Checkin__c == 1 ) return  response()->json([
            'status' => false,
            'message' => 'Lịch này đã check in rồi!'
        ], 400);

        //update schedule record in SF
        try {
            $myTime = Carbon::now();
            $updateRecord = [
                'is_Checkin__c' => 1,
                'Checkin_Time__c' => $myTime->toIso8601String()
            ];
            $response = SObject::update('Schedule_HV__c', $scheduleData->Id, $updateRecord);
            if (isset($response->status_code) && in_array($response->status_code, [400, 404])) return response()->json(['message' => $response->message, 'data' => $scheduleData], $response->status_code);

            //update on local DB
            $scheduleData->is_Checkin__c = 1;
            $scheduleData->Checkin_Time__c = $myTime;
            $scheduleData->save();
            //log
            activity('member_app')
                ->causedBy($scheduleData)
                ->withProperties(['schedule hv' => $scheduleData->toArray()])
                ->log('Schedule HV| create new schedule HV #' . $response);

            return  response()->json([
                'status' => true,
                'message' => 'Checkin Success!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 422);
        }

        //        return response()->json([
        //            'status' => ScheduleHV::checkin($sf_account_id, $postData['id']),
        //        ]);

    }

    /**
     * xử lý check in cùng lúc nhiều user
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkinMultiple(Request $request) {
        $postData = $request->post();
        $user = auth()->user();
        if (empty($user)) return response()->json([
            'status' => false,
            'message' => 'Invalid Request'
        ], 400);

        $sf_account_id = $user->sf_account_id(); //lấy sf account id của MS

        if (!$sf_account_id) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid sf_account_id'
            ], 400);
        }
        $responseData = [];
        $listItem = array_filter(explode(';', $postData['name']));
        $myTime = Carbon::now();
        //loop the data
        $sfUpdateRecord = [];
        $check = true;
        foreach ($listItem as $item){
            if ( !empty($item) ) {
                $scheduleData = ScheduleHV::where('Name', $item)->first();

                if( !isset($scheduleData->Id) ) {
                    $responseData[] = [
                        'status' => false,
                        'message' => 'Mã này không hợp lệ!',
                        'id' => $item
                    ];
                    $check = false;
                }
                else
                {
                    if( $scheduleData->is_Checkin__c == 1 ) {
                        $responseData[] = [
                            'status' => false,
                            'message' => 'Lịch này đã check in rồi!',
                            'id' => $item,
                        ];
                        $check = false;
                    }
                    else {
                        $sfUpdateRecord[] = [
                            'attributes' => ['type' => 'Schedule_HV__c'],
                            'Id' => $scheduleData->Id,
                            'is_Checkin__c' => 1,
                            'Checkin_Time__c' => $myTime->toIso8601String()
                        ];
                    }
                }
            }
        }

        //if ( !$check ) return response()->json(['message' => $responseData], 422);

        //update schedule record in SF
        try {
            $response = SObject::updateMultiple($sfUpdateRecord);
            if (isset($response->status_code) && in_array($response->status_code, [400, 404])) return response()->json(['message' => $response->message, 'data' => $scheduleData], $response->status_code);

            foreach ($response as $objItem) {
                if ( $objItem['success'] ) {
                    $updateData = [
                        'is_Checkin__c' => 1,
                        'Checkin_Time__c' => $myTime->toIso8601String(),
                        'HV_Status__c' => 'Checked-in'
                    ];
                    ScheduleHV::where('Id', $objItem['id'])
                            ->update($updateData);
                }
            }
            //log
            activity('member_app')
                ->causedBy($scheduleData)
                ->withProperties(['update_record' => $sfUpdateRecord, 'sf_response' => $response])
                ->log('Schedule HV| process checkin success');
            $responseData = array_merge($responseData, $response);

            return  response()->json($responseData, 200);
        } catch (ClientException $e) {
            $mess = $e->getResponse()->getBody()->getContents();
            $mess1 = json_decode($mess);
            return response()->json(['message' => $mess1], 422);
        }
    }

    /**
     * Xử lý checkin khi member quên đem điện thoại => user sẽ nhập vào tên của Schedule HV (vd: SA-0024) để checkin
     * @param Request $request
     */
    public function CheckinWithName(Request $request) {
        $postData = $request->post();
        $validator = \Validator::make($postData, [
            'name' => 'required',
            'schedule' => 'sometimes|required'
        ],
            [
                'name.required' => 'Vui lòng nhập tên lịch tập',
            ]
        );
        if ($validator->fails())
        {
            $messages = implode(";",$validator->messages()->all());
            return response()->json([
                'message' => $messages
            ], 422);
        }
        $schedule = ScheduleHV::where('Name', $postData['name'])
                    ->where('Schedule__c' ,$postData['schedule'])->get()->first();

        if ( empty($schedule) ) return response()->json([
            'status' => false,
            'message' => 'Lịch tập này không tồn tại trong hệ thống! Vui lòng kiểm tra lại'
        ], 422);

        if ( $schedule->is_Checkin__c == 1 ) return response()->json([
            'status' => false,
            'message' => 'Lịch tập đã được checkin rồi! Vui lòng kiểm tra lại'
        ], 422);

        if ( $schedule->HV_Status__c == 'Cancelled' ) return response()->json([
            'status' => false,
            'message' => 'Lịch tập này đã bị hủy! Vui lòng kiểm tra lại'
        ], 422);

        //update schedule HV record in SF
        try {
            $myTime = Carbon::now();
            $updateRecord = [
                'is_Checkin__c' => 1,
                'Checkin_Time__c' => $myTime->toIso8601String()
            ];
            $response = SObject::update('Schedule_HV__c', $schedule->Id, $updateRecord);
            if (isset($response->status_code) && in_array($response->status_code, [400, 404])) return response()->json(['message' => $response->message, 'data' => $schedule], $response->status_code);

            //update on local DB
            $schedule->is_Checkin__c = 1;
            $schedule->Checkin_Time__c = $myTime;
            $schedule->HV_Status__c = 'Checked-in';
            $schedule->save();
            //log
            activity('member_app')
                ->causedBy($schedule)
                ->withProperties(['schedule hv' => $schedule->toArray()])
                ->log('Schedule HV| create new schedule HV #' . $response);

            return  response()->json([
                'status' => true,
                'message' => 'Checkin Success!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 422);
        }

    }

    /**
     * Hủy booking của member
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelBooking(Request $request)
    {
        $postData = $request->post();
        if (!isset($postData['type']) || empty($postData['type'])) return response()->json(['message' => 'Invalid type!'], 422);
        if (!isset($postData['id']) || empty($postData['id'])) return response()->json(['message' => 'Invalid ID!'], 422);

        switch ($postData['type']) {
            case 'schedule_hv':
                $item = ScheduleHV::find($postData['id']);
                if (empty($item)) return response()->json(['message' => 'No item found'], 404);
                else {
                    //update schedule HV record in SF
                    try {
                        $updateRecord = [
                            'HV_Status__c' => 'Cancelled',
                        ];
                        $response = SObject::update('Schedule_HV__c', $item->Id, $updateRecord);
                        if (isset($response->status_code) && in_array($response->status_code, [400, 404])) {
                            return response()->json(['message' => $response->message, 'data' => $item], $response->status_code);
                        }

                        //update on local DB
                        $item->HV_Status__c = 'Cancelled';
                        $item->save();
                        $response = ['message' => 'Hủy booking lớp học thành công'];

                        //log
                        activity('member_app')
                            ->causedBy($item)
                            ->withProperties(['schedule hv' => $item->toArray()])
                            ->log('Schedule HV| Cancel Booking #' . $item->Name);

                    } catch ( ClientException $e) {
                        $mess = $e->getResponse()->getBody()->getContents();
                        $mess1 = json_decode($mess);

                        foreach ($mess1 as $item) {
                            unset($item->errorCode);
                        }

                        return response()->json($mess1, 422);
                    }

                }
                break;
            case 'schedule':
            default:
                $item = Schedule::find($postData['id']);
                if (empty($item)) return response()->json(['message' => 'No item found'], 404);
                else {
                    //update schedule HV record in SF
                    try {
                        $updateRecord = [
                            'Status__c' => 'Cancelled',
                        ];
                        $response = SObject::update('Schedule_HV__c', $item->Id, $updateRecord);
                        if (isset($response->status_code) && in_array($response->status_code, [400, 404])) return response()->json(['message' => $response->message, 'data' => $item], $response->status_code);

                        //update on local DB
                        $item->Status__c = 'Cancelled';
                        $item->save();
                        $response = ['message' => 'Hủy booking thành công'];

                        //log
                        activity('member_app')
                            ->causedBy($item)
                            ->withProperties(['schedule' => $item->toArray()])
                            ->log('Schedule| Cancel Booking #' . $response);

                    } catch (\Exception $e) {
                        return response()->json($e->getMessage(), 422);
                    }

                }
                break;
        }
        return response()->json($response, 200);
    }

    public function listContract(Request $request)
    {
        $user = auth()->user();
        //Son Add Role Sale,Pt
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);
        $returnData = [];
        \DB::enableQueryLog();
        $ctSale = ContractSale::where('Sales__c', $user->sf_account_id())->get();
        if (empty($ctSale)) return response()->json(['message' => 'Invalid Request']);

        $cusData = [];
        $fieldIN = "";
        foreach ($ctSale as $item) {
            if (!empty($item)) {
                if ($fieldIN == "")
                    $fieldIN .= "'" . $item->Contract__c . "'";
                else
                    $fieldIN .= "," . "'" . $item->Contract__c . "'";
            }
        }
        $contracts = ContractController::getSFContracts($fieldIN);

        if (!empty($contracts)) return response()->json($contracts, 200);
        else return response()->json(['message' => 'Không tìm thấy hợp đồng'], 404);
    }

}
