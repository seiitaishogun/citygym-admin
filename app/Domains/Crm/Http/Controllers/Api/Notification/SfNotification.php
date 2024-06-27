<?php
/**
 * @author tmtuan
 * created Date: 23-Apr-21
 */

namespace App\Domains\Crm\Http\Controllers\Api\Notification;

use App\Domains\Acp\Traits\PushNotification;
use App\Domains\Auth\Models\User;
use App\Domains\Crm\Models\AppNotification;
use App\Http\Controllers\Api\ApiController;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SfNotification extends ApiController {
    use PushNotification;

    /**
     * API SF gửi Push Notification
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setNotification(Request $request) {
        $postData = $request->post();
        $_noti = new AppNotification();

        $validator = \Validator::make($postData, [
            'name' => 'required|min:2',
            'title' => 'required|min:2',
            'message' => 'required|min:2',
            'groupName' => 'required',
            'sendTime' => 'required'
        ],
        [
            'name.required' => 'Vui lòng nhập tên app',
            'name.min' => 'Tên app quá ngắn',
            'title.required' => 'Vui lòng nhập tiêu đề tin nhắn',
            'title.min' => 'Tiêu đề tin nhắn quá ngắn',
            'message.required' => 'Vui lòng nhập nội dung tin nhắn',
            'message.min' => 'Nội dung tin nhắn quá ngắn',
            'groupName.required' => 'Vui lòng điền nhóm user để gửi tin',
            'sendTime.required' => 'Vui lòng điền thời gian gửi tin',
        ]);
        if ($validator->fails())
        {
            $messages = $validator->messages();
            return response()->json([
                'status' => false,
                'message' => $messages
            ], 200);
        }
        //check scheduling
//        if ( !in_array($postData['scheduling'], ['now', 'schedule'])) return response()->json([
//            'status' => false,
//            'message' => 'scheduling chỉ có 2 dạng ( now hoặc schedule )'
//        ], 200);

        //check app type and convert to firebase appName
        if ( $postData['name'] == $_noti::SALE_APP ) $appName = 'sale';
        else if ( $postData['name'] == $_noti::MB_APP ) $appName = 'member';

        //check group
        switch ($postData['groupName'] ) {
            case 'all-mb':
                $group = 'all';
                break;
            case 'all-sales-pt':
                $group = 'all';
                break;
            case 'member ':
            case 'ms':
            case 'user_sale':
            case 'user_pt':
                $group = $postData['groupName'];
                break;
            case 'user':
                if ( !isset($postData['accountId']) || empty($postData['accountId']) ) return response()->json([
                    'status' => false,
                    'message' => 'Thiếu account id'
                ], 200);
                else {
                    $userData = $this->getUser($postData['accountId'] ?? '');
                    if ( !$userData || $userData == false ) return response()->json([
                        'status' => false,
                        'message' => 'Invalid account id'
                    ], 200);

                    $group = 'user_'.$userData->id;
                    $postData['user_id'] = $userData->id;
                }
                break;
        }

        //check data_option
        $dataOption = [
            "Object" => (isset($postData['objectName'])) ? $postData['objectName'] : '',
            "Id" => (isset($postData['objectId'])) ? $postData['objectId'] : ''
        ];
        $timezone = config('app.timezone');

        $newNoti = new AppNotification($postData);
        $newNoti->group = $postData['groupName'];
        $newNoti->data_option = json_encode($dataOption);

        $send_time = Carbon::createFromFormat('Y-m-d H:i:s', $postData['sendTime'], $timezone);
        //$send_time->addHour(7);
        if ( $send_time <= Carbon::now() ) {
            try {
                $data = [
                    'group' => $group,
                    'app' => $appName,
                    'data' => $dataOption
                ];

                // Push notification
                $result = $this->pushMessage($postData['title'], $postData['content'], $data);
                if ( $result['success'] ) {
                    $newNoti->is_sent = 1;
                    $newNoti->is_seen = 0;

                    $_noti::create($newNoti->toArray());
                    return response()->json([
                        'status' => true,
                        'message' => __('acp.send_noti_success')
                    ], 200);

                }
                else return response()->json([
                    'status' => true,
                    'message' => $result['error']
                ], 200);
            } catch (\Exception $e){
                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

        } else {
            if (!isset($postData['sendTime']) ) return response()->json([
                'status' => false,
                'message' => 'Invalid send date'
            ], 200);

            $send_time = Carbon::parse($postData['send_date']);
            $newNoti->send_time = $send_time;
            $newNoti->is_sent = 0;
            $newNoti->is_seen = 0;
            $_noti::create($newNoti->toArray());

            return response()->json([
                'status' => true,
                'message' => __('acp.send_noti_success')
            ], 200);
        }
    }

    /**
     * Lấy thông tin user theo SF account_id
     * @param $account_id
     * @return bool
     */
    public function getUser($account_id) {
        if ( empty($account_id) ) return false;
        else {
            $user = User::join('user_sf_account', 'user_sf_account.user_id', '=', 'users.id')
                ->where('user_sf_account.sf_account_id', $account_id)
                ->get()->first();
            if ( !isset($user->id) ) return false;
            else return $user;
        }
    }
}
