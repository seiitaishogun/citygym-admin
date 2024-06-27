<?php
/**
 * @author tmtuan
 * created Date: 18-Jan-21
 */
namespace App\Domains\Crm\Http\Controllers\Api\Search;

use App\Domains\Crm\Models\Opportunity;
use App\Domains\Crm\Models\SfAcccount;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends ApiController {

    /**
     * September-09-21 fix ( https://beunik.atlassian.net/browse/CIT-635 )
     * @param Request $request
     * @return mixed
     */
    public function search(Request $request) {
        $user = auth()->user();
        //Son Add Role Sale,Pt
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);        
        $type = $request->query('type');
        $input = $request->query();
        $per_page = $input['per_page'] ?? 15;

        if ( !isset($type) || empty($type) ) return response()->json(['message' => 'Invalid Type!'], 422);
        //DB::enableQueryLog();
        switch ($type) {
            case 'opty':
                $phone = $input['search'];

                $query = Opportunity::with('Club')
                    ->with('Lead')
                    ->with('Parent')
                    ->with('PriceBook')
                    ->with('PromotionBook')
                    ->join('salesforce_Account', 'salesforce_Account.Id', '=', 'salesforce_Opportunity.AccountId')
                    ->select(['salesforce_Opportunity.Id', 'salesforce_Opportunity.Name', 'salesforce_Opportunity.StageName', 'salesforce_Opportunity.Email__c',
                    'salesforce_Account.PersonMobilePhone'])->where('salesforce_Opportunity.IsDeleted', 0);

                if ( strlen($phone) == 5 ) {
                    $query->whereRaw(DB::raw("RIGHT(salesforce_Account.PersonMobilePhone, 5) = {$phone}"));
                } else {
                    $query->where('salesforce_Account.PersonMobilePhone', 'like', '%'.$phone.'%');
                }

                if ( $user->hasRole('Sale') ) $query->where('salesforce_Opportunity.Sales_Assign__c',$user->sf_account_id());
                else $query->where('salesforce_Opportunity.PT_assign__c',$user->sf_account_id());

//                if ( isset($input['search']) && !empty($input['search']) ) $query->where('PersonMobilePhone', 'like', '%'.$input['search'].'%');
                $optyCount = $query->count();
                break;
            case 'account':
                $query =  SfAcccount::select(['*']);
                if ( isset($input['search']) && !empty($input['search']) ) {
                    if ( strlen($input['search']) == 5 ) {
                        $query->whereRaw(DB::raw("RIGHT(PersonMobilePhone, 5) = {$input['search']}"));
                    } else {
                        $query->where('salesforce_Account.PersonMobilePhone', 'like', '%'.$input['search'].'%');
                    }
                }

                $accCount = $query->count(); //dd(DB::getQueryLog());
                break;
        }
        $data = $query->paginate($per_page);
        $returnData['data'] = $data;
        $returnData['optyCount'] = $optyCount??0;
        $returnData['accCount'] = $accCount??0;
        //log
        $querylog = $data->toArray();
        activity('sale_pt_app')
            ->withProperties(['Search Data' => $querylog['data']])
            ->log('Search| Search Query #'.$input['search']);

        if ( $returnData ) return response()->json($returnData, 200);
        else return response()->json(['messasge' => 'No item found!'], 404);

    }
}
