<?php
/**
 * @author tmtuan
 * created Date: 12-Jan-21
 */
namespace App\Domains\Crm\Http\Controllers\Api\Oppty;

use App\Domains\Crm\Models\OpportunityLinePromotion;
use App\Domains\Crm\Models\Promotion;
use App\Domains\Crm\Models\PromotionItem;
use App\Domains\Crm\Models\RecordType;
use App\Domains\TmtSfObject\Classes\SObject;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PromoOpptyController extends OpptyController {

    /**
     * Lấy danh sách các trương trình KM (Promotion) theo Club (field Club__c trong object Opportunity)
     * từ object Club_Applied_Promotions__c - Điều kiện Valid_To__c <= now() AND Valid_From__c >= now() AND Status__c = Active
     * @param Request $request
     */
    public function getPromos(Request $request) {
        $user = auth()->user();
        //Son Add Role Sale,Pt
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);
        $input = $request->query();

        /**
         *
         */
        $refRecordType = RecordType::where('DeveloperName', 'Referral')
                        ->where('SobjectType', 'Promotion__c')
                        ->first();
// var_dump($refRecordType->Id);die();
        if ( !isset($input['club']) || empty($input['club']) ) return response(['message' => 'Invalid Club'], 422);
//DB::enableQueryLog();
        $promoData = Promotion::leftJoin('salesforce_Club_Applied_Promotions__c', 'salesforce_Club_Applied_Promotions__c.Promotion__c', '=', 'salesforce_Promotion__c.Id')
                ->leftJoin('salesforce_Promotion_Item__c', 'salesforce_Promotion_Item__c.Promotion__c', '=', 'salesforce_Promotion__c.Id')
                ->leftJoin('salesforce_Product_Promotion__c', 'salesforce_Promotion_Item__c.Id', '=', 'salesforce_Product_Promotion__c.Promotion_Item__c')
                ->where('salesforce_Club_Applied_Promotions__c.Club__c', $input['club'])
                //->where('salesforce_Promotion__c.Status__c', 'Active')
                ->where(function ($qr){
                    $qr->where('salesforce_Promotion__c.Valid_From__c', '<=',Carbon::now()->format('Y-m-d'));
                    $qr->where('salesforce_Promotion__c.Valid_To__c','>=', Carbon::now()->format('Y-m-d'));
                })
//                ->where('salesforce_Promotion__c.Valid_From__c', '<=',)
//                ->where('salesforce_Promotion__c.Valid_To__c', '>=', Carbon::now())
                ->where('salesforce_Promotion__c.IsDeleted', 0)
                ->where(function($query) use ($refRecordType) {
                    $query->whereNull('salesforce_Promotion__c.RecordTypeId')
                          ->orWhere('salesforce_Promotion__c.RecordTypeId', '!=', $refRecordType->Id??'');
                })
                // ->where('salesforce_Promotion__c.RecordTypeId', '!=', $refRecordType->Id??'')
                ->select(['salesforce_Promotion__c.*', 'salesforce_Product_Promotion__c.Quantity_for_Guest__c', 'salesforce_Product_Promotion__c.Promotion_item_Quantity__c'])
                ->groupBy('salesforce_Promotion__c.Id')
                ->get();
//dd(DB::getQueryLog());
        if ( !empty($promoData->toArray()) ) return response()->json($promoData, 200);
        else return response()->json(['message' => 'No record found'], 404);
    }

    public function getPromoItems($promo, Request $request) {
        $user = auth()->user();
        //Son Add Role Sale,Pt
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);
        $promoData = Promotion::find($promo);
        $pdId = $request->query('product');
        if (empty($promoData)) return response()->json(['message' => 'No promotion found'], 404);

        $promoItemsData = PromotionItem::with(['productPromotion' => function($qr) use ($pdId) {
            if ( isset($pdId) ) $qr->where('salesforce_Product_Promotion__c.Product__c', $pdId);
        }])
            ->where('Promotion__c', $promoData->Id)
            ->select('Id', 'Name', 'FOC_Product__c', 'Valid_From__c', 'Valid_To__c')
            ->where(function ($qr){
                $qr->where('Valid_From__c', '<=', Carbon::now()->format('Y-m-d'));
                $qr->where('Valid_To__c', '>=', Carbon::now()->format('Y-m-d'));
            })
            ->where('IsDeleted', 0)
            ->get();

        $returnData = [
            'promotion' => $promoData,
            'promotionItem' => $promoItemsData
        ];
        if ( !empty($promoItemsData->toArray()) ) return response()->json($returnData, 200);
        else return response()->json(['message' => 'No record found'], 404);
    }

    public function savePromo(Request $request) {
        $user = auth()->user();
        //Son Add Role Sale,Pt
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);
        $postData = $request->post();

        $validator = \Validator::make($postData, [
            'promotion_item' => 'required',
            'foc_product' => 'required',
            'oppty' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => implode(",",$validator->messages()->all())],422);
        } else {
            $promos = explode(';', $postData['promotion_item']);
            $focs = explode(';', $postData['foc_product']);
            $i=0;
            foreach ($promos as $item) {
                $objectData = [
                    "Promotion_Item__c" => $item,
                    "Opportunity__c" => $postData['oppty'],
                    "FOC_Product__c" => $focs[$i]
                ];

                //create schedule record in SF
                DB::beginTransaction();
                try {
                    $response = SObject::create('Opportunity_Line_Promotion__c', $objectData);
                    if ( isset($response->status_code) && $response->status_code == 400 ) return response()->json(['message' => $response->message, 'data' => $objectData], $response->status_code);

                    //set date
                    $newItem = new OpportunityLinePromotion();
                    $this->setDefaultDate($objectData);

                    $newItem->fill($objectData);
                    $newItem->Id = $response;
                    $newItem->sync_result = $response->status_code;
                    if ($response->status_code == 200)
                        $newItem->last_sync_success = date('Y-m-d H:i:s');
                    $newItem->save();
                    $i++;
                    //log
                    activity('sale_pt_app')
                        ->causedBy($newItem)
                        ->withProperties(['OpportunityLinePromotion' => $newItem->toArray()])
                        ->log('Opportunity Line Promotion| create new Opportunity Line Promotion  #'.$response);
                    DB::commit();

                } catch ( \Exception $e) {
                    DB::rollBack();
                    continue;
                }
            }
            return response()->json(['message' => 'Save Product to Opportunity Line Promotion Success', 'data' => $newItem], 201);
        }
    }

    /**
     * Oct-15-2021 - Task: https://beunik.atlassian.net/browse/CIT-649
     * Lấy danh sách  Promotion có Record Type là Referral + product id
     * @param Request $request
     * @return mixed
     */
    public function listRefPromo(Request $request)
    {
        $user = auth()->user();
        //Son Add Role Sale,Pt
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);
        $inputData = $request->all();

        if ( !isset($inputData['product']) || empty($inputData['product']) ) return response(['message' => 'Invalid Product'], 422);

        //get Referral record Type
        $recordType = RecordType::where('SobjectType', 'Promotion__c')
            ->where('DeveloperName', 'Referral')->get()->first();


        $promoData = Promotion::leftJoin('salesforce_Promotion_Item__c', 'salesforce_Promotion_Item__c.Promotion__c', '=', 'salesforce_Promotion__c.Id')
            ->leftJoin('salesforce_Product_Promotion__c', 'salesforce_Product_Promotion__c.Promotion_Item__c', '=', 'salesforce_Promotion_Item__c.Id')
            ->select('salesforce_Promotion__c.*')
            ->where('salesforce_Promotion__c.RecordTypeId', $recordType->Id)
            ->where('salesforce_Product_Promotion__c.Product__c', $inputData['product'])
            ->groupBy('salesforce_Promotion__c.Id')->get();

        if ( !empty($promoData->toArray()) ) return response()->json($promoData, 200);
        else return response()->json(['message' => 'No record found'], 404);
    }

    /**
     * Oct-15-2021 - Task: https://beunik.atlassian.net/browse/CIT-649
     * Lấy Promotion Item có Record Type là Referral_Buyer theo Promotion ID
     * @param $id
     * @return mixed
     */
    public function listRefPromoItem($id)
    {
        //get Referral record Type
        $recordType = RecordType::where('SobjectType', 'Promotion_Item__c')
            ->where('DeveloperName', 'Referral_Buyer')->get()->first();

        $promoItems = PromotionItem::where('Promotion__c', $id)
                    ->where('RecordTypeId', $recordType->Id)
                    ->where(function ($qr){
                        $qr->where('Valid_From__c', '<=', Carbon::now()->format('Y-m-d'));
                        $qr->where('Valid_To__c', '>=', Carbon::now()->format('Y-m-d'));
                    })->get();

        if ( !empty($promoItems->toArray()) ) return response()->json($promoItems, 200);
        else return response()->json(['message' => 'No record found'], 404);
    }
}
