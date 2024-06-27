<?php
/**
 * @author tmtuan
 * created Date: 18-Dec-20
 */
namespace App\Domains\Crm\Http\Controllers\Api\SupportCase;

use App\Domains\Crm\Models\SupportCase;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;

class CaseController extends ApiController {
    public function listCases(Request $request) {
        $user = auth()->user();

        if ( empty($user) ) return response()->json([
            'message' => 'Invalid Request'
        ], 400);

        $per_page = $queryData['per_page'] ?? 5;
        $caseData = SupportCase::with('Club')
            ->where('IsDeleted', 0)
            ->where('AccountId', $user->sf_account_id())
            ->orderBy('LastModifiedDate', 'desc')
            ->paginate($per_page);
//echo '<pre>'; print_r($caseData->toArray()); exit;
        if (empty($caseData)) return response()->json(['msg' => __('No Record Found')], 404);
        else return response()->json($caseData, 200);
    }

    public function getCase($id, Request $request) {
        $user = auth()->user();

        if ( empty($user) ) return response()->json([
            'message' => 'Invalid Request'
        ], 400);

        $item = SupportCase::with('Club')->find($id); // dd($item); exit;
        if (empty($item)) return response()->json(['msg' => __('No Record Found')], 404);
        else return response()->json($item, 200);
    }
}
