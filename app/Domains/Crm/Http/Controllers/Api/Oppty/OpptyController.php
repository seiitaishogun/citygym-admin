<?php
/**
 * @author tmtuan
 * created Date: 01-Dec-20
 */
namespace App\Domains\Crm\Http\Controllers\Api\Oppty;

use App\Domains\Crm\Models\Contract;
use App\Domains\Crm\Models\RecordType;
use App\Domains\TmtSfObject\Classes\ApexRest;
use App\Domains\TmtSfObject\Classes\Authorization;
use App\Domains\TmtSfObject\Classes\Constant;
use App\Domains\TmtSfObject\Classes\SalesforceException;
use App\Domains\TmtSfObject\Classes\Utilities;
use App\Http\Controllers\Api\ApiController;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;

use App\Domains\Crm\Models\Opportunity;
use App\Domains\Crm\Models\OpportunityLineItem;
use App\Domains\Crm\Models\OpportunityLineBenefit;
use App\Domains\Crm\Models\OpportunityLinePromotion;
use App\Domains\Crm\Models\OpportunityLineGift;
use Illuminate\Support\Facades\DB;
use App\Domains\TmtSfObject\Classes\SObject;
use function Couchbase\defaultDecoder;

class OpptyController extends ApiController {
    public $stageMB = [
        'New',
        'GFP',
        'In Body',
        'Sales Tour',
        'Membership Consult',
        'Closed Lost',
        'Closed Won'
    ];

    public $stageCorp = [
        'New',
        'Meeting',
        'Trial',
        'Negotiation',
        'Closed Lost',
        'Closed Won'
    ];

    public $stagePT = [
        'Consult',
        'POS',
        'F',
        'Closed Won'
    ];

    public $stage = [
        'New',
        'GFP',
        'In Body',
        'Sales Tour',
        'Membership Consult',
        'Consult',
        'POS',
        'F',
        'Meeting',
        'Trial',
        'Negotiation',
        'Closed Won',
        'Closed Lost'
    ];

    public function listOppty(Request $request) {
        $user = auth()->user();
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);

    	// $Opportunity = Opportunity::with('Club')->has('Club')->get();
        $input = $request->query();

    	$Opportunity = Opportunity::with('Club')
    	->with('AccountName')
    	->with('Lead')
    	->with('Parent')
    	->with('PriceBook')
    	->with('PromotionBook')->where('IsDeleted', 0);

    	$ptType = RecordType::where('SobjectType', 'Opportunity')->where('DeveloperName', 'Individual_PT')->get()->first();
    	$saleType = RecordType::where('SobjectType', 'Opportunity')->where('DeveloperName', 'Individual_MB')->get()->first();

    	if ( $user->hasRole('Sale') ) {
    	    $Opportunity->where('Sales_Assign__c',$user->sf_account_id());
    	    $Opportunity->where('RecordTypeId',$saleType->Id);
        } else {
    	    $Opportunity->where('PT_assign__c',$user->sf_account_id());
    	    $Opportunity->where('RecordTypeId',$ptType->Id);
        }

        if(isset($input['startDate']) || isset($input['endDate'])){
            $from = empty($input['startDate']) ? date('Y-m-d H:i:s',strtotime($input['endDate'].'last month')) : date('Y-m-d H:i:s',strtotime($input['startDate']));
            $to = empty($input['endDate']) ? date('Y-m-d H:i:s',strtotime($input['startDate'].'next month')) : date('Y-m-d H:i:s',strtotime($input['endDate'].' 23 hours 59 minutes 59 seconds'));
            $Opportunity->whereBetween('CreatedDate', [$from, $to]);
        }

        if(isset($input['search'])){
            $searchKey = $input['search'];
            $Opportunity->where(function ($query) use ($searchKey) {
                $query->where('Name', 'like', '%'.$searchKey.'%')
                    ->orWhere('Phone__c', 'like', '%'.$searchKey.'%');
            });
        }

        if(isset($input['stage']) && $input['stage'] != 'All'){
            $Opportunity->where('StageName', $input['stage']);
        }

        if($Opportunity->get()){
            $optData = $Opportunity->get();

            // foreach ($optData as $item) {
            //     if ( isset($input['debug']) )  dd($item);
            //     if ( empty($item->LastActivityDate) ) $item->LastActivityDate = $item->CreatedDate;

            //     if ( !empty($item->Club) ) {
            //         if ( empty($item->Club->LastActivityDate) ) $item->Club->LastActivityDate = $item->CreatedDate;
            //         if ( empty($item->Club->LastModifiedDate) ) $item->Club->LastModifiedDate = $item->CreatedDate;
            //         if ( empty($item->Club->SystemModstamp) ) $item->Club->SystemModstamp = $item->CreatedDate;
            //         if ( empty($item->Club->LastViewedDate) ) $item->Club->LastViewedDate = $item->CreatedDate;
            //     }

            //     if ( !empty($item->AccountName) ) {
            //         if ( empty($item->AccountName->LastActivityDate) ) $item->AccountName->LastActivityDate = $item->CreatedDate;
            //         if ( empty($item->AccountName->LastModifiedDate) ) $item->AccountName->LastModifiedDate = $item->CreatedDate;
            //         if ( empty($item->AccountName->SystemModstamp) ) $item->AccountName->SystemModstamp = $item->CreatedDate;
            //         if ( empty($item->AccountName->LastViewedDate) ) $item->AccountName->LastViewedDate = $item->CreatedDate;
            //     }

            // }
            return response()->json($optData, 200);
        } else {
            return response()->json([], 404);
        }
    }

    /**
     * Sep-09-2021 - bổ xung các stage cho corp opty [ https://beunik.atlassian.net/browse/CIT-633 ]
     * @param Request $request
     * @return mixed
     */
    public function listOpptyPaginate(Request $request) {
        $per_page = $input['per_page'] ?? 15;
        $user = auth()->user();
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);

    	// $Opportunity = Opportunity::with('Club')->has('Club')->get();
        $input = $request->query();
        DB::enableQuerylog();
    	$Opportunity = Opportunity::with(['AccountName' => function($qr){
            $qr->select(['Id', 'PersonMobilePhone', 'Phone']);
        }])
    	//->with('Club')
    	//->with('Lead')
    	//->with('Parent')
    	//->with('PriceBook')
    	//->with('PromotionBook')->where('IsDeleted', 0)
        ->select(['Id', 'Name', 'Source_Name__c', 'StageName', 'AccountId']);

    	$ptType = RecordType::where('SobjectType', 'Opportunity')->where('DeveloperName', 'Individual_PT')->get()->first();
    	$saleType = RecordType::where('SobjectType', 'Opportunity')->where('DeveloperName', 'Individual_MB')->get()->first();
    	$corpType = RecordType::where('SobjectType', 'Opportunity')->where('DeveloperName', 'Corporate_MB')->get()->first();

    	if ( $user->hasRole('Sale') ) {
    	    $Opportunity->where('Sales_Assign__c',$user->SfAccount);
            $Opportunity->where(function ($qr) use ($saleType, $corpType){
                $qr->where('RecordTypeId',$saleType->Id);
                $qr->orWhere('RecordTypeId',$corpType->Id);
            });

        } else {
    	    $Opportunity->where('PT_assign__c',$user->SfAccount);
    	    $Opportunity->where('RecordTypeId',$ptType->Id);
        }

        if(isset($input['startDate']) || isset($input['endDate'])){
            $from = empty($input['startDate']) ? date('Y-m-d H:i:s',strtotime($input['endDate'].'last month')) : date('Y-m-d H:i:s',strtotime($input['startDate']));
            $to = empty($input['endDate']) ? date('Y-m-d H:i:s',strtotime($input['startDate'].'next month')) : date('Y-m-d H:i:s',strtotime($input['endDate'].' 23 hours 59 minutes 59 seconds'));
            $Opportunity->whereBetween('CreatedDate', [$from, $to]);
        }

        if(isset($input['search'])){
            $searchKey = $input['search'];
            $Opportunity->where(function ($query) use ($searchKey) {
                $query->where('Name', 'like', '%'.$searchKey.'%')
                    ->orWhere('Phone__c', 'like', '%'.$searchKey.'%');
            });
        }

        if(isset($input['stage']) && $input['stage'] != 'All'){
            $Opportunity->where('StageName', $input['stage']);
        }
        $Opportunity->orderBy('CreatedDate','desc');
        if($Opportunity->get()){
            $optData = $Opportunity->paginate($per_page); //dd(DB::getquerylog());

//            foreach ($optData as $item) {
//                if ( isset($input['debug']) )  dd($item);
//                if ( empty($item->LastActivityDate) ) $item->LastActivityDate = $item->CreatedDate;
//
//                if ( !empty($item->Club) ) {
//                    if ( empty($item->Club->LastActivityDate) ) $item->Club->LastActivityDate = $item->CreatedDate;
//                    if ( empty($item->Club->LastModifiedDate) ) $item->Club->LastModifiedDate = $item->CreatedDate;
//                    if ( empty($item->Club->SystemModstamp) ) $item->Club->SystemModstamp = $item->CreatedDate;
//                    if ( empty($item->Club->LastViewedDate) ) $item->Club->LastViewedDate = $item->CreatedDate;
//                }
//
//                if ( !empty($item->AccountName) ) {
//                    if ( empty($item->AccountName->LastActivityDate) ) $item->AccountName->LastActivityDate = $item->CreatedDate;
//                    if ( empty($item->AccountName->LastModifiedDate) ) $item->AccountName->LastModifiedDate = $item->CreatedDate;
//                    if ( empty($item->AccountName->SystemModstamp) ) $item->AccountName->SystemModstamp = $item->CreatedDate;
//                    if ( empty($item->AccountName->LastViewedDate) ) $item->AccountName->LastViewedDate = $item->CreatedDate;
//                }
//
//            }
            return response()->json($optData, 200);
        } else {
            return response()->json([], 404);
        }
    }

    public function getOppty($id, Request $request) {
        $user = auth()->user();
        //Son Add Role Sale,Pt
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);        
        // var_dump($id);die();

        $Opportunity = Opportunity::with('Club')
            ->with(['RecordType' => function($qr){
                $qr->select(['Id', 'Name','DeveloperName','SobjectType']);
            }])
            ->with(['AccountName' => function($qr){
                $qr->select(['Id', 'Name','LastName','FirstName','Salutation','MiddleName','Suffix','Type','Phone','Email__c','Club__c','Job_Title__c','Company_Name__c','Age__c'])
                    // ->with('Contract')
                    ->with(['Contract' => function($ct){
                                    $ct->select(['Id', 'StartDate','EndDate','Status','StatusCode','Description','Contract_Status__c','Contract_Number__c','Name','AccountId'])->orderBy('StartDate', 'DESC');
                                }]);
            }])
            ->with('OppotunitySales')
//            ->with(['HasProducts' => function($qr){
//                $qr->join('salesforce_Product2', 'salesforce_Product2.Id', '=', 'salesforce_OpportunityLineItem.Product2Id')
//                    ->select(['salesforce_OpportunityLineItem.*']);
//            }])
            ->with('Lead')
            ->with('Parent')
            ->with('PriceBook')
            ->with('PromotionBook')
            ->with(['HasClubApplied'=> function($qr){
                $qr->with(['ClubInfo' => function($cbqr){
                    $cbqr->select(['Id', 'Name']);
                }])
                ->select(['Id', 'Club__c', 'Opportunity__c'])
                ->where('IsDeleted', 0);
            }])
            ->with(['SalesAssign' => function($qr){
                $qr->select(['Id', 'Name', 'Phone']);
            }])
            ->with(['ReferralPromotionBook' => function($qr){
                $qr->select(['Id', 'Name'])->where('IsDeleted', 0);
            }])
            ->where('IsDeleted', 0)
            ->where('Id', $id);

        if ( $user->hasRole('Sale') ) $Opportunity->where('Sales_Assign__c',$user->sf_account_id());
        else $Opportunity->where('PT_assign__c',$user->sf_account_id());

        if ($Opportunity->first()) {
            $oppty = $Opportunity->first();
            $product = OpportunityLineItem::getByOpptyId($id);
            $benefit = OpportunityLineBenefit::getByOpptyId($id);
            $promotion = OpportunityLinePromotion::getByOpptyId($id);
            /**
             * Nov-26 fix load referal promotion item
             * task: https://beunik.atlassian.net/browse/CIT-653
             */
            $refPromotion = OpportunityLinePromotion::getByOpptyId($id, 'ref');
            $gift = OpportunityLineGift::getByOpptyId($id);
            $oppty['product'] = $product;
            $oppty['benefit'] = $benefit;
            $oppty['promotion'] = $promotion;
            $oppty['referral_promotion'] = $refPromotion;
            $oppty['gift'] = $gift;
            return response()->json($oppty, 200);
        } else return response()->json(['status' => false, 'message' => 'No item found!'], 404);
    }

    public function convertOppty($id, Request $request) {
        $user = auth()->user();
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);

        $opptyItem = Opportunity::find($id);

        if ( !isset($opptyItem) || empty($opptyItem->toArray()) ) return response()->json(['message' => 'No item found!'], 404);

        /**
         * Thực hiện gọi API lên SF để convert Oppty
         */
        $recordData = [
            'opportunityId' => $id
        ];

        try {
            $response = ApexRest::post('app-api/v1/convert-opportunity-to-contract', $recordData);
            $respBody = json_decode($response->getBody()->getContents());
            if ( $respBody->success == 'true' ) {
//                $opptyItem->Contract_MB__c = $respBody->result->Id;
                $opptyItem->StageName = 'Closed Won';
                $opptyItem->save();
                //log
                activity('sale_pt_app')
                    ->withProperties(['Opty' => $opptyItem->toArray()])
                    ->log('Opty| Convert opty to contract success');

                $newCt = (array) $respBody->result; //dd($newCt);
                $newContract = new Contract();
                $newContract->fill($newCt);
                $newContract->CreatedDate = date('Y-m-d H:i:s');
                $newContract->LastModifiedDate = date('Y-m-d H:i:s');
                $newContract->IsDeleted = 0;

                //***********
//                $newContract->Id = $newCt->Id;
////                $newContract->Name = $newCt->Name;
//                $newContract->AccountId = $newCt->AccountId;
//                $newContract->Opportunity__c = $newCt->Opportunity__c;
//                $newContract->Pricebook2Id = $newCt->Pricebook2Id;
//                $newContract->Club_Of_Received_Card__c = $newCt->Club_Of_Received_Card__c;
//                $newContract->Payment_Object__c = $newCt->Payment_Object__c;
//                $newContract->Description = $newCt->Description;
//                $newContract->StartDate = $newCt->StartDate;
//                $newContract->RecordTypeId = $newCt->RecordTypeId;
//                $newContract->Contract_Type__c = $newCt->Contract_Type__c;
//                $newContract->Unit_Of_Measure__c = $newCt->Unit_Of_Measure__c;
////                $newContract->Sales_Club__c = $newCt->Sales_Club__c;
//                $newContract->EndDate = $newCt->EndDate;
//                $newContract->CreatedDate = date('Y-m-d H:i:s');
//                $newContract->LastModifiedDate = date('Y-m-d H:i:s');
//                $newContract->IsDeleted = 0;
                //**************

                $newContract->save();

                //log
                activity('salesforce_api')
                    ->withProperties(['Contract' => $newCt])
                    ->log('Contract| create new Contract success #' . $respBody->result->Id);

                unset($respBody->result->attributes);
                $respBody->oppty = $opptyItem;
                $respBody->message = 'Thành công! Hệ thống đang xử lý vui lòng đợi trong giây lát.';
                return response()->json($respBody, $response->getStatusCode());
            } else {
                $errs = $respBody->error;
                $resErr = ( is_array($errs) ) ? implode(',', $errs) : $errs;

                return response()->json(['message' => $resErr], $response->getStatusCode());
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Sep-07-2021 - bổ xung thêm stage cho record type corp [ https://beunik.atlassian.net/browse/CIT-633?focusedCommentId=43800 ]
     * @param Request $request
     * @return mixed
     */
    public function listStageCount() {
        $returnData = [];
        $user = auth()->user();
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);

        if ( $user->hasRole('Sale') ) $selectType = 'Sales_Assign__c';
        else $selectType = 'PT_assign__c';

        /**
         * update stage for corp
         */
        if ( $user->hasRole('Sale') ) $chkStage = array_unique(array_merge($this->stageMB, $this->stageCorp));
        else if ($user->hasRole('PT') ) $chkStage = $this->stagePT;
        else $chkStage = $this->stage;

        foreach ($chkStage as $stage) {
            $countItem = Opportunity::where($selectType, $user->sf_account_id())
                ->where('StageName', $stage)
                ->where('IsDeleted', 0)
                ->get();
            $returnData[] = [
                'stage' => $stage,
                'count' => $countItem->count()
            ];
        }
        return response()->json($returnData, 200);
    }

    /**
     * Sep-07-2021 - bổ xung thêm stage cho record type corp [ https://beunik.atlassian.net/browse/CIT-633?focusedCommentId=43800 ]
     * @param Request $request
     * @return mixed
     */
    public function listStage(Request $request) {
        $user = auth()->user();
        //Son Add Role Sale,Pt
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);        
        $input = $request->query();

        if( isset($input['type']) ) {
            switch ($input['type']) {
                case 'mb':
                    return response()->json( $this->stageMB, 200);
                    break;
                case 'corp':
                    return response()->json( $this->stageCorp, 200);
                    break;
                case 'pt':
                    return response()->json( $this->stagePT, 200);
                    break;
                default:
                    return response()->json($this->stage, 200);
                    break;
            }
        }
        else {
            if ( $user->hasRole('Sale') ) return response()->json($this->stageMB, 200);
            else if ($user->hasRole('PT') ) return response()->json($this->stagePT, 200);
            else return response()->json($this->stage, 200);
        }

    }

    public function deleteOpportunityLineItem($id, Request $request) {
        $postData = $request->post();
        $user = auth()->user();
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);

        if ( empty($user->id) ) {
            return response()->json([
                'message' => 'Invalid Request'
            ], 400);
        }

        $opportunityLineItem = OpportunityLineItem::leftJoin('salesforce_Opportunity', 'salesforce_Opportunity.id', '=', 'salesforce_OpportunityLineItem.OpportunityId')
                    ->Where('salesforce_OpportunityLineItem.Id', $id);

        if ( $user->hasRole(['Sale', 'PT']) ) $opportunityLineItem->where('Sales_Assign__c',$user->sf_account_id());
        else $opportunityLineItem->where('PT_assign__c',$user->sf_account_id());

        $opportunityLineItem->get()->first();

        if (empty($opportunityLineItem)) {
            return response()->json([
                'status' => false,
                'message' => 'Lead does not exist or you don\'t have permission to delete this opportunityLineItem!'
            ], 422);
        }

        // $postData['Status'] = 'New';
        // $postData['Staff_Assignment__c'] = $user->sf_account_id();

        //create schedule record in SF
        DB::beginTransaction();
        try {
            $response = SObject::delete('OpportunityLineItem', $id);
            if ( isset($response->status_code) && $response->status_code == 400 ) {  //dd($response);
                return response()->json(['message' => $response->message, 'data' => $postData], $response->status_code);
            }
            $opportunityLineItem = OpportunityLineItem::Where('Id', $id)
                        ->get()->first();
            $opportunityLineItem->IsDeleted = "1";
            $opportunityLineItem->save();


            //log
            activity('sale_pt_app')
                ->causedBy($opportunityLineItem)
                ->withProperties(['OpportunityLineItem' => $opportunityLineItem->toArray()])
                ->log('OpportunityLineItem| delete OpportunityLineItem Item  #'.$response);
            DB::commit();

            return response()->json(['message' => 'Delete Success'], 201);
        } catch ( \Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 422);
        }
    }
}
