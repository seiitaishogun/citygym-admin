<?php
/**
 * @author tmtuan
 * created Date: 24-Feb-21
 */

namespace App\Domains\Crm\Http\Controllers\Api\Notification;

use App\Domains\Crm\Models\AppNotification;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;

class SaleNotification extends ApiController {

    public function listNotification(Request $request) {
        $user = auth()->user();
        if ( !$user ) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);

        $notiData = AppNotification::where('user_id', $user->id)->orderBy('created_at', 'desc');
        $input = $request->query();

        if($notiData->get()){
            return response()->json($notiData->get(), 200);
        } else {
            return response()->json([], 404);
        }
    }

    public function createSaleNoti(Request $request) {
        $user = auth()->user();
        //Son Add Role Sale,Pt
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);
        $postData = $request->all();

        $noti = [
            'name' => AppNotification::SALE_APP,
            'user_id' => $postData['user_id']??0,
            'group' => $postData['group']??null,
            'title' => $postData['title']??'',
            'message' => $postData['message']??'',
            'content' => $postData['content']??'',
        ];
        try {
        AppNotification::create($noti);
            $returnData[] = [
                'msg' => 'Create Notification Success'
            ];
        } catch (\Exception $e) {
            $returnData[] = [
                'error' => $e->getMessage()
            ];
        }
        return response()->json($returnData);
    }

    public function getNotification($id, Request $request) {
        $user = auth()->user();
        if ( !$user ) return response()->json(['message' => 'Bạn không có quyền xem tin nhắn này'], 401);
        $noti = AppNotification::find($id);

        if ( empty($noti) ) return response()->json(['message' => 'Không tìm thấy dữ liệu phù hợp'], 404);

        $noti->is_seen = 1;
        $noti->save();

        return response()->json($noti, 200);

    }

}
