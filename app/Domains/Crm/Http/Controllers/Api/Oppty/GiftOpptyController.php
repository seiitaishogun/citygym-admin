<?php
/**
 * @author tmtuan
 * created Date: 11-Jan-21
 */
namespace App\Domains\Crm\Http\Controllers\Api\Oppty;

use App\Domains\Crm\Models\OpportunityLineGift;
use App\Domains\Crm\Models\Product2;
use App\Domains\TmtSfObject\Classes\SObject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GiftOpptyController extends OpptyController {

    /**
     * Hệ thống chỉ load ra những Gift có mối quan hệ với Product đã được gán trên Opportunity
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGift($id, Request $request) {
        $user = auth()->user();
        //Son Add Role Sale,Pt
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);
        $proItem = Product2::with('gift')
                    ->find($id);

        if ( empty($proItem) ) return response()->json(['message' => 'Invalid Product'], 404);

        if ( !empty($proItem->toArray()) ) return response()->json($proItem, 200);
        else return response()->json(['message' => 'No record found'], 404);
    }

    /**
     * Lưu các Gift đã được user chọn vào trong Oppty
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function saveGift(Request $request) {
        $postData = $request->post();
        $user = auth()->user();

        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);


        $validator = \Validator::make($postData, [
            'gift' => 'required',
            'product' => 'required',
            'oppty' => 'required',
            'gift_quantity' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => implode(" ",$validator->messages()->all())],422);
        } else {
            $gifts = array_filter(explode(';', $postData['gift']));
            $quantities = array_filter(explode(';', $postData['gift_quantity']));
            $i = 0;
            foreach ($gifts as $item) {
                $objectData = [
                    "Gift__c" => $item,
                    "Product__c" => $postData['product'],
                    "Opportunity__c" => $postData['oppty'],
                    "Quantity__c" => $quantities[$i]
                ];

                //create Opportunity_Line_Gift__c record in SF
                DB::beginTransaction();
                try {
                    $response = SObject::create('Opportunity_Line_Gift__c', $objectData);
                    if ( isset($response->status_code) && $response->status_code == 400 ) return response()->json(['message' => $response->message, 'data' => $objectData], $response->status_code);

                    //set date
                    $newItem = new OpportunityLineGift();
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
                        ->withProperties(['OpportunityLineGift' => $newItem->toArray()])
                        ->log('OpportunityLineGift| create new Opportunity Line Gift  #'.$response);
                    DB::commit();

                } catch ( \Exception $e) {
                    DB::rollBack();
                    continue;
                }
            }
            return response()->json(['message' => 'Save Product to Opportunity Line Gift Success', 'data' => $newItem], 201);
        }
    }
}
