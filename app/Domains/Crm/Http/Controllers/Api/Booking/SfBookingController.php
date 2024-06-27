<?php
/**
 * @author tmtuan
 * created Date: 09-Dec-20
 */
namespace App\Domains\Crm\Http\Controllers\Api\Booking;

use App\Domains\Acp\Traits\PushNotification;
use App\Domains\Auth\Models\User;
use App\Domains\Crm\Models\AppNotification;
use App\Domains\Crm\Models\Schedule;
use App\Domains\Crm\Models\ScheduleHV;
use App\Domains\Crm\Models\ScheduleTrainer;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;


class SfBookingController extends ApiController {

    use PushNotification;
    /**
     * API nhận danh sách Booking từ SF đẩy về
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createBooking(Request $request) {
        $postData = $request->post();
        $returnData = [];

        foreach ( $postData as $item ) {
            //convert time
            $this->handleScheduleTime($item);

            $bkItem = Schedule::find($item['Id']);
            if ( !empty($bkItem) ) {
                try {
                    unset($item['Id']);

                    $bkItem->fill($item);
                    $bkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $bkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['schedule' => $item])
                        ->log('Schedule| Update schedule success #' . $bkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update schedule success',
                        'result' => $bkItem->Id
                    ];
                } catch (\Exception $e) {

                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $bkItem->Id
                    ];
                    continue;
                }

            } else {
                //set date
                $this->setDefaultDate($item);
                $schedule = Schedule::create($item);
                //log
                activity('salesforce_api')
                    ->causedBy($schedule)
                    ->withProperties(['schedule' => $item])
                    ->log('Booking| create new schedule #'.$item['Id']);

                $returnData[] = [
                    'success' => true,
                    'error' => "Create success",
                    'result' => $item['Id']
                ];
            }
        }

        return response()->json($returnData, 200);
    }

    public function editBooking($id, Request $request) {
        $postData = $request->all();
        $returnData = [];

        //log request
        activity('salesforce_api')
            ->withProperties(['Booking' => $postData])
            ->log('Request | editBooking - '.$request->path());

        $item = Schedule::find($id);
        if ( empty($item) ) $returnData = [
            'success' => false,
            'error' => 'No item found',
            'result' => $id
        ];
        else {
            $postData = $postData[0];
            if ( isset($postData['Id'])) unset($postData['id']);
            if ( isset($postData['CreatedDate'])) unset($postData['CreatedDate']);
//            if ( isset($postData['LastModifiedDate']) && !empty($postData['LastModifiedDate']) ) $postData['LastModifiedDate'] = Carbon::parse($postData['LastModifiedDate'])->format('Y-m-d H:i:s');

            try {
//                if ( isset($postData['Member_Check_In__c']) && $postData['Member_Check_In__c'] == true ) $postData['Member_Check_In__c'] = 1;
//                else $postData['Member_Check_In__c'] = 0;
//
//                if ( isset($postData['MS_Check_In__c']) && $postData['MS_Check_In__c'] == true ) $postData['MS_Check_In__c'] = 1;
//                else $postData['MS_Check_In__c'] = 0;

                $item->fill($postData);
                $item->LastModifiedDate = Carbon::now();
                $item->save();
                $returnData = [
                    'success' => true,
                    'error' => 'Update success',
                    'result' => $id
                ];

                //log
                activity('salesforce_api')
                    ->withProperties(['schedule' => $postData])
                    ->log('Booking| update schedule success #' . $id);
            } catch (\Exception $e) {
                $returnData = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'result' => $id
                ];
            }

        }
        return response()->json($returnData, 200);
    }

    public function createScheduleTrainer(Request $request) {
        $postData = $request->post();
        $returnData = [];

        foreach ( $postData as $item ) {
            $chkItem = ScheduleTrainer::find($item['Id']);
            if ( !empty($chkItem) ) $chkItem->forceDelete();

            //set date
            $this->setDefaultDate($item);

            $newItem = ScheduleTrainer::create($item);
            //log
            activity('salesforce_api')
                ->causedBy($newItem)
                ->withProperties(['schedule_trainer' => $item])
                ->log('Booking| create new schedule trainer #'.$item['Id']);

            $returnData[] = [
                'success' => true,
                'error' => "Create success",
                'result' => $item['Id']
            ];
        }
        return response()->json($returnData, 200);
    }

    public function createScheduleHv(Request $request) {
        $postData = $request->post();
        $returnData = [];


        foreach ( $postData as $item ) {
            $chkItem = ScheduleHV::find($item['Id']);
            if ( !empty($chkItem) ) {
                //convert time
                $this->handleScheduleTime($item);
                try {
                    unset($item['Id']);
                    $chkItem->fill($item);
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['schedule_hv' => $item])
                        ->log('schedule_hv| Update Schedule HV success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update Schedule HV success',
                        'result' => $chkItem->Id
                    ];
                    //goi ham gui tin nhắn cho user
                    $this->sendHvNotification($chkItem, $item);

                }catch (\Exception $e) {
                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $chkItem->Id
                    ];
                    continue;
                }
                continue;
            } else {
                try {
                    //set date
                    $this->setDefaultDate($item);

                    $newItem = ScheduleHV::create($item);
                    //log
                    activity('salesforce_api')
                        ->causedBy($newItem)
                        ->withProperties(['schedule_hv' => $item])
                        ->log('schedule_hv| create new schedule HV item #'.$item['Id']);

                    $returnData[] = [
                        'success' => true,
                        'error' => "Create success",
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $item['Id']
                    ];
                }
            }
        }
        return response()->json($returnData, 200);
    }

    public function deleteBooking(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['Booking' => $item])
                ->log('Request | deleteBooking - '.$request->path());


            $chkItem = Schedule::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['Booking' => $item])
                        ->log('Booking| Delete Booking success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete Booking success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['Booking' => $item])
                        ->log('Delete Booking Fail | '.$e->getMessage());

                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $chkItem->Id
                    ];
                    continue;
                }
            }
        }
        return response()->json($returnData, 200);
    }

    public function deleteScheduleTrainer(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['ScheduleTrainer' => $item])
                ->log('Request | deleteScheduleTrainer - '.$request->path());


            $chkItem = ScheduleTrainer::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['ScheduleTrainer' => $item])
                        ->log('ScheduleTrainer| Delete ScheduleTrainer success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete ScheduleTrainer success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['contact' => $item])
                        ->log('Delete ScheduleTrainer Fail | '.$e->getMessage());

                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $chkItem->Id
                    ];
                    continue;
                }
            }
        }
        return response()->json($returnData, 200);
    }

    public function deleteScheduleHv(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['ScheduleHv' => $item])
                ->log('Request | deleteScheduleHv - '.$request->path());


            $chkItem = ScheduleHV::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['ScheduleHV' => $item])
                        ->log('ScheduleHV| Delete ScheduleHV success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete ScheduleHV success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['contact' => $item])
                        ->log('Delete ScheduleHV Fail | '.$e->getMessage());

                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $chkItem->Id
                    ];
                    continue;
                }
            }
        }
        return response()->json($returnData, 200);
    }

    /**
     * Xử lý trạng thái booking
     * nếu trạng thái Schedule_HV chuyển từ trạng thái khác qua “Queue” (CRM sync trạng thái booking của hội viên thành trạng thái chờ)
     * → Send push notification cho hội viên “Lớp [Tên lớp] - [Giờ lớp] đã đầy. Bạn được đưa vào danh sách chờ.“
     *
     * nếu trạng thái Schedule_HV chuyển từ trạng thái khác qua “Booked” (CRM sync trạng thái booking của hội viên từ chờ thành “Đã đặt”)
     * → Send push notification cho hội viên “Lớp [Tên lớp] - [Giờ lớp] đã được?????“
     */
    public function sendHvNotification($chkItem, $item ) {
        $_noti = new AppNotification();

        $userData = User::join('user_sf_account', 'user_sf_account.user_id', '=', 'users.id')
            ->where('user_sf_account.sf_account_id', $chkItem->Account__c)
            ->get()->first();
        if ( isset($userData->id) ) {
            //get schedule
            $schedule = Schedule::where('Id', $chkItem->Schedule__c)->first();

            if ( $item['HV_Status__c'] == 'Queue' ) {
                $dataOption = [
                    "Object" => 'Schedule_HV',
                    "Id" => $chkItem->Id
                ];
                $newNoti = new AppNotification();
                $newNoti->data_option = json_encode($dataOption);
                $newNoti->name = $_noti::MB_APP;
                $newNoti->user_id = $userData->id;

                $data = [
                    'group' => 'user_'.$userData->id,
                    'app' => 'member',
                    'data' => $dataOption
                ];

                // Push notification
                $mytime = $schedule->Start__c->format('H:i');
                $messTitle = "Lớp [{$schedule->Class_Name__c}] - [{$mytime}] đã đầy. Bạn được đưa vào danh sách chờ.";
                $result = $this->pushMessage($messTitle, $messTitle, $data);
                if ( $result['success'] ) {

                    $newNoti->title = $messTitle;
                    $newNoti->message = $messTitle;
                    $newNoti->content = $messTitle;
                    $newNoti->is_sent = 1;
                    $newNoti->is_seen = 0;

                    $_noti::create($newNoti->toArray());
                    return true;
                }
            }

            if ( $item['HV_Status__c'] == 'Booked' ) {
                $dataOption = [
                    "Object" => 'Schedule_HV',
                    "Id" => $chkItem->Id
                ];
                $newNoti = new AppNotification();
                $newNoti->data_option = json_encode($dataOption);
                $newNoti->name = $_noti::MB_APP;
                $newNoti->user_id = $userData->id;

                $data = [
                    'group' => 'user_'.$userData->id,
                    'app' => 'member',
                    'data' => $dataOption
                ];

                // Push notification
                if ( !is_null($item['Checkin_Time__c']) || $item['Checkin_Time__c'] != null ) {
                    $mytime = $schedule->Start__c->format('H:i');
                    $messTitle = "Lớp [{$schedule->Class_Name__c}] - [{$mytime}] đã được checkin thành công. Vui lòng kiểm tra lịch đã đặt.";
                    $result = $this->pushMessage($messTitle, $messTitle, $data);
                    if ( $result['success'] ) {

                        $newNoti->title = $messTitle;
                        $newNoti->message = $messTitle;
                        $newNoti->content = $messTitle;
                        $newNoti->is_sent = 1;
                        $newNoti->is_seen = 0;

                        $_noti::create($newNoti->toArray());
                        return true;
                    }
                } else {
                    $mytime = $schedule->Start__c->format('H:i');
                    $messTitle = "Lớp [{$schedule->Class_Name__c}] - [{$mytime}] đã được đặt thành công. Vui lòng kiểm tra lịch đã đặt.";
                    $result = $this->pushMessage($messTitle, $messTitle, $data);
                    if ( $result['success'] ) {

                        $newNoti->title = $messTitle;
                        $newNoti->message = $messTitle;
                        $newNoti->content = $messTitle;
                        $newNoti->is_sent = 1;
                        $newNoti->is_seen = 0;

                        $_noti::create($newNoti->toArray());
                        return true;
                    }

                }

            }
        }
    }
}
