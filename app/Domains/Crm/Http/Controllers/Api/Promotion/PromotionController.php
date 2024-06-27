<?php
namespace App\Domains\Crm\Http\Controllers\Api\Promotion;

use App\Domains\Crm\Models\Promotion;
use App\Domains\Crm\Models\PromotionItem;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;

class PromotionController extends ApiController{

    public function listPromotions(Request $request) {
        $user = auth()->user();
        if ( empty($user) ) return response()->json([
            'message' => 'Invalid Request'
        ], 400);

        $input = $request->query();
        $per_page = $input['per_page'] ?? 15;

        $promotions = Promotion::with(['club' => function($qr){
            $qr->select(['Id', 'Name', 'Club_Name__c', 'Promotion__c']);
        }])
        ->select(['Id', 'Name', 'Code__c', 'Remark__c', 'Status__c', 'Valid_From__c', 'Valid_To__c', 'Applied_To__c'])->where('IsDeleted', 0);

        if($user->can('Sale')){
            $promotions->whereIn('Applied_To__c', ['MB', 'All', 'Both']);
        }
        else if($user->can('PT')){
            $promotions->whereIn('Applied_To__c', ['PT', 'All', 'Both']);
        }
        else{
            $promotions->whereIn('Applied_To__c', ['MB', 'All', 'Both']);
        }

        if ( isset($input['status']) && !empty($input['status']) ) $promotions->where('Status__c', $input['status'] );
        else $promotions->where('Status__c', '!=', 'NULL' );

        if(isset($input['startDate']) || isset($input['endDate'])){
            $from = empty($input['startDate']) ? date('Y-m-d H:i:s',strtotime($input['endDate'].'last month')) : date('Y-m-d H:i:s',strtotime($input['startDate'].' 00:00:01'));
            // $promotions->where('Valid_From__c', '>=', $from);
            $to = empty($input['endDate']) ? date('Y-m-d H:i:s',strtotime($input['startDate'].'next month')) : date('Y-m-d H:i:s',strtotime($input['endDate'].' 23:59:59'));
            // $promotions->where('Valid_To__c', '<=', $to);
            $promotions->where(function ($qr) use ($from, $to){
                $qr->orWhere(function ($qrw) use ($from, $to){
                    $qrw->where('Valid_From__c', '>=',$from);
                    $qrw->where('Valid_To__c','<=', $to);
                });

                $qr->orWhere(function ($qrw) use ($from, $to){
                    $qrw->where('Valid_From__c', '<=',$from);
                    $qrw->where('Valid_To__c','>=', $from);
                });

                $qr->orWhere(function ($qrw) use ($from, $to){
                    $qrw->where('Valid_From__c', '<=',$to);
                    $qrw->where('Valid_To__c','>=', $to);
                });
            });
        }

        $promotions->orderBy('Valid_To__c', 'desc');
        if($result = $promotions->paginate($per_page)){
            return response()->json($result, 200);
        }
        return response()->json(['message' => 'No item found'], 404);
    }

    public function getPromotion(Request $request) {
        $user = auth()->user();
        //Son Add Role Sale,Pt
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);        
        $promotionId = $request->promotionId;

        $promotion = Promotion::with(['club' => function($qr){
            $qr->select(['Id', 'Name', 'Club_Name__c', 'Promotion__c']);
        }])
        ->select(['Id', 'Name', 'Code__c', 'Remark__c', 'Status__c', 'Valid_From__c', 'Valid_To__c', 'Applied_To__c'])
        ->find($promotionId);

        if ( empty($promotion) ) return response()->json(['message' => 'No item found'], 404);
        else return response()->json($promotion, 200);
    }

    public function getPromotionItemOfPromotion(Request $request) {
        $user = auth()->user();
        //Son Add Role Sale,Pt
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);        
        if ( empty($user) ) return response()->json([
            'message' => 'Invalid Request'
        ], 400);
        $promotionId = $request->promotionId;

        $promotions = PromotionItem::with(['Benefit' => function($qr){
            $qr->select(['Id', 'Name']);
        }])
        ->with(['ProductPromotion' => function($qr){
            $qr->with(['Product' => function($q){
                $q->select(['Id', 'Name']);
            }])
            ->select(['Id', 'Name', 'Promotion_Item__c', 'Product__c', 'Promotion_Record_Type__c', 'Fix_Optional__c', 'Guest_Privilledge_Applied__c', 'Quantity_for_Guest__c',
                'Promotion_Item_Name__c', 'Promotion_item_Quantity__c']);
        }])
        ->with(['Promotion' => function($promo){
            $promo->select(['Id', 'Name', 'Term_And_Condition__c']);
        }])
        ->with(['Gift' => function($gift) {
            $gift->select(['Id', 'Name']);
        }])
        ->where('IsDeleted', 0)->where('Promotion__c', $promotionId);

        if($request->has('limit_record')){
            $limitRecord = $request->query('limit_record');
            $promotions->take($limitRecord);
        }

        if($result = $promotions->get()){
            return response()->json($result, 200);
        }
        return response()->json(['message' => 'No item found'], 404);
    }

}
