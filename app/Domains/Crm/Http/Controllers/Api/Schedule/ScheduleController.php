<?php
namespace App\Domains\Crm\Http\Controllers\Api\Schedule;

use App\Domains\Crm\Models\Schedule;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;

class ScheduleController extends ApiController{
    public function getScheduleByUser(Request $request) {
        $user = auth()->user();
        if ( empty($user) ) return response()->json([
            'message' => 'Invalid Request'
        ], 400);
        $schedule = Schedule::where('OwnerId', $user->sf_account_id())->where('IsDeleted', 0);

        if($request->has('job_title')){
            $jobTitle = $request->query('job_title');
            switch ($jobTitle) {
                case 'Sale':
                    $schedule->where('RecordTypeId', '0120l000000FtHlAAK');
                    break;
                case 'PT':
                    $schedule->whereIn('RecordTypeId', ['0120l000000FtHqAAK', '0120l000000FoKBAA0']);
                    break;
                default:
                    return response()->json([], 404);
                    break;
            }
        }

        if($result = $schedule->get()){
            return response()->json($result, 200);
        }
        return response()->json([], 404);
    }

}
