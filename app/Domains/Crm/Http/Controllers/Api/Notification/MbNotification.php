<?php
/**
 * @author tmtuan
 * created Date: 26-Feb-21
 */

namespace App\Domains\Crm\Http\Controllers\Api\Notification;

use App\Domains\Crm\Models\AppNotification;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;

class MbNotification extends ApiController {
    public function listNotification(Request $request) {
        $user = auth()->user();
        if ( !$user ) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);
        $input = $request->query();

        $notiData = AppNotification::where('user_id', $user->id)
                ->where('name', 'app_MB')
                ->orWhere('group', 'all')
                ->orderBy('id', 'desc');

        if($notiData->get()){
            return response()->json($notiData->get(), 200);
        } else {
            return response()->json([], 404);
        }
    }

}
