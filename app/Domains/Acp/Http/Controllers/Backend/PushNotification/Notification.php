<?php
/**
 * @author tmtuan
 * created Date: 29-Mar-21
 */
namespace App\Domains\Acp\Http\Controllers\Backend\PushNotification;

use App\Domains\Acp\Traits\PushNotification;
use App\Domains\Crm\Models\AppNotification;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class Notification extends Controller {
    use PushNotification;

    public function showNotiForm($app_type) {
        if ( !isset($app_type) || !in_array($app_type, ['sale', 'member']) ) return redirect()->route('admin.dashboard')->withErrors(__('acp.invalid_noti_group'));

        if ( $app_type == 'member' ) $groups = config('firebase.message_group.member_app');
        else $groups = config('firebase.message_group.sale_app');

        return view('backend.noti.push_noti', ['groups' => $groups, 'app_type' => $app_type]);
    }

    public function sendPush(Request $request)
    {
        $postData = $request->post();
        $validator = \Validator::make($postData, [
            'title' => 'required',
            'app_group' => 'required',
            'content' => 'required',
            'app_type' => 'required',
        ],
        [
            'title.required' => 'Vui lòng nhập Tiêu đề',
            'app_group.required' => 'Vui lòng chọn nhóm',
            'content.required' => 'Vui lòng nhập nội dung tin nhắn',
            'app_type.required' => 'Invalid App',
        ]
        );
        if ($validator->fails())
        {
            return redirect()->back()->withErrors($validator->errors());
        }
        $groups = config('firebase.message_group');
        $_noti = new AppNotification();
        if ( $postData['app_type'] == 'sale' ) $appName = $_noti::SALE_APP;
        else if ( $postData['app_type'] == 'member' ) $appName = $_noti::MB_APP;

        if ( !in_array($postData['app_type'].'_app', array_keys($groups)) ) return redirect()->back()->withErrors([__('acp.invalid_noti_group')]);

        if ( $postData['scheduled'] == 2 ) {
            $send_time = Carbon::parse($postData['date-input']);
            $newNoti = [
                'name' => $appName,
                'group' => $postData['app_group'],
                'title' => $postData['title'],
                'message' => $postData['content'],
                'content' => $postData['content'],
                'is_seen' => 0,
                'send_time' => $send_time,
                'is_sent' => 0,
            ];
            $_noti::create($newNoti);
            return redirect()->back()->withFlashSuccess(__('acp.send_noti_success'));
        } else {
            $totalUnread = 1;
            $data = [
                'group' => $postData['app_group'],
                'app' => $postData['app_type']
            ];

            // Push notification
            $result = $this->pushMessage($postData['title'], $postData['content'], $data);
            if ( $result['success'] ) {
                $newNoti = [
                    'name' => $appName,
                    'group' => $postData['app_group'],
                    'title' => $postData['title'],
                    'message' => $postData['content'],
                    'content' => $postData['content'],
                    'is_seen' => 0,
                    'is_sent' => 1,
                ];
                $_noti::create($newNoti);
                return redirect()->back()->withFlashSuccess(__('acp.send_noti_success'));
            }
            else return redirect()->back()->withErrors([$result['error']]);
        }

    }
}
