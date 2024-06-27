<?php
/**
 * @author tmtuan
 * created Date: 22-Dec-20
 */
namespace App\Domains\Crm\Http\Controllers\Api\Account;

use App\Domains\Auth\Models\User;
use App\Domains\Crm\Models\SfAcccount;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;

class AccountController extends ApiController {
    public function listAccount(Request $request) {
        $user = auth()->user();
        //Son Add Role Sale,Pt
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);      
        $accounts = SfAcccount::where('OwnerId', $user->sf_account_id())
                            ->where('IsDeleted', 0)
                            ->get();

        if ( empty($accounts->toArray()) ) return response()->json(['message' => 'No items Found'], 404);
        else return response()->json($accounts, 200);
    }

    public function getAccount( Request $request) {
        $user = auth()->user();
        $id = $request->query('id');
        $account = SfAcccount::find($id);

        if (empty($account)) return response()->json(['message' => 'No item found!'], 404);
        else return response()->json($account, 200);
    }

    public function getPt(Request $request) {
        // get PT
        //        $pt = SfAcccount::join('salesforce_Record_Type', 'salesforce_Record_Type.Id', '=', 'salesforce_Account.RecordTypeId')
        //            ->select(['salesforce_Account.Id', 'RecordTypeId', 'salesforce_Account.name'])
        //            ->where('salesforce_Record_Type.SobjectType', 'Account')
        //            ->where('salesforce_Record_Type.DeveloperName', 'Employee')
        //            ->where('Job_Title__c', 'PT')
        //            ->get();
        $user = auth()->user();
        //Son Add Role Sale,Pt
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);
        $usersDt = User::whereHas("roles", function($q){ $q->where("name", "PT"); })->get();
        $ptData = [];
        foreach ( $usersDt as $us ) {
            $acc = SfAcccount::join('user_sf_account', 'user_sf_account.sf_account_id', '=', 'salesforce_Account.Id')
                ->select(['salesforce_Account.Id', 'RecordTypeId', 'salesforce_Account.name'])
                ->where('user_sf_account.user_id', $us->id)->get()->first();
            if ( !empty($acc) ) $ptData[] = $acc;
        }

        if (empty($ptData)) return response()->json(['message' => 'Không tìm thấy dữ liệu!'], 404);
        else return response()->json($ptData, 200);
    }

}
