<?php
/**
 * @author tmtuan
 * created Date: 11-Jan-21
 */
namespace App\Domains\Crm\Http\Controllers\Api\Oppty;

use App\Domains\Crm\Models\Club;
use App\Domains\Crm\Models\Opportunity;
use App\Domains\Crm\Models\OpportunityLineItem;
use App\Domains\Crm\Models\PricebookEntry;
use App\Domains\Crm\Models\Product2;
use App\Domains\Crm\Models\RecordType;
use App\Domains\TmtSfObject\Classes\ApexRest;
use App\Domains\TmtSfObject\Classes\SObject;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ProductOpptyController extends OpptyController {
    /**
     * Đối với Oppty Invidual MB load ra những sản phẩm có record type = Product MB
     * Đối với Oppty PT load ra những sản phẩm có record type = Product PT
     * Lấy club với điều kiện Is_TM__c = 0
     *
     * Sep-07-2021 - bổ xung lấy product theo opty Corporate [ https://beunik.atlassian.net/browse/CIT-633 ]
     */
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProduct(Request $request) {
        $user = auth()->user();
        //Son Add Role Sale,Pt
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);        
        $input = $request->query();
        if ( empty($input['record_type']) ) return response()->json(['message' => 'Record Type can not empty'], 422);
        $recordType = RecordType::find($input['record_type']);

        if ( !isset($recordType->Name) || !in_array($recordType->Name, ['Individual MB', 'Individual PT', 'Corporate MB'])) return response()->json(['message' => 'Invalid Record Type'], 422);
        DB::enableQueryLog();
        $selectFields = ['salesforce_Record_Type.Id as record_type_id', 'salesforce_Product2.Id', 'salesforce_Product2.Name', 'salesforce_Product2.ProductCode',
            'salesforce_Product2.Unit_Of_Measure__c'];
        switch ($recordType->Name) {
            case 'Individual MB':
                $proData = Product2::join('salesforce_Record_Type', 'salesforce_Record_Type.Id', '=', 'salesforce_Product2.RecordTypeId')
                            ->select($selectFields)
                            ->where('salesforce_Record_Type.DeveloperName', 'Product_MB')
                            ->where('salesforce_Record_Type.SobjectType', 'Product2')
                            ->where('IsDeleted', 0)
                            ->where('IsActive', 1)
                            ->get();
                break;
            case 'Individual PT':
                $proData = Product2::join('salesforce_Record_Type', 'salesforce_Record_Type.Id', '=', 'salesforce_Product2.RecordTypeId')
                    ->select($selectFields)
                    ->where('salesforce_Record_Type.DeveloperName', 'Product_PT')
                    ->where('salesforce_Record_Type.SobjectType', 'Product2')
                    ->where('IsDeleted', 0)
                    ->where('IsActive', 1)
                    ->get();
                break;
            case 'Corporate MB':
                $proData = Product2::join('salesforce_Record_Type', 'salesforce_Record_Type.Id', '=', 'salesforce_Product2.RecordTypeId')
                    ->select($selectFields)
                    ->where('salesforce_Record_Type.DeveloperName', 'Product_Corporate_MB')
                    ->where('salesforce_Record_Type.SobjectType', 'Product2')
                    ->where('IsDeleted', 0)
                    ->where('IsActive', 1)
                    ->get();
                break;
        } //dd(DB::getQueryLog());

        if ( !empty($proData->toArray()) ) return response()->json($proData, 200);
        else return response()->json(['message' => 'No record found'], 404);
    }

    /**
     * App gửi lên product ID + club ID, lấy ra danh sách Giá dựa theo Club Id ( trong salesforce_Club_Applied_Pricebook__c )
     * loc ra danh sách các pricebok, rồi so sánh với object (salesforce_PricebookEntry) lấy ra những Entry có Product2Id= ProductID đã gửi lên từ App
     * @param Request $request
     */
    public function getPriceBookEntry(Request $request) {
        $user = auth()->user();
        //Son Add Role Sale,Pt
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);        
        $input = $request->query();
        if ( empty($input['product']) || empty($input['club']) ) {
            return response()->json(['message' => 'Invalid data Type'], 422);
        }
        //DB::enableQueryLog();
        $pricebookEntry = PricebookEntry::join('salesforce_Pricebook2', 'salesforce_Pricebook2.Id', '=', 'salesforce_PricebookEntry.Pricebook2Id')
                        ->leftJoin('salesforce_Club_Applied_Pricebook__c', 'salesforce_Club_Applied_Pricebook__c.Price_Book__c', 'salesforce_Pricebook2.Id')
                        ->select('salesforce_PricebookEntry.*')
//                        ->select('salesforce_PricebookEntry.Id', 'salesforce_PricebookEntry.Name')
                        ->where('salesforce_Club_Applied_Pricebook__c.Club__c', $input['club'])
                        ->where('salesforce_PricebookEntry.Product2Id', $input['product'])
                        ->where(function ($qr){
                            $qr->where('salesforce_Pricebook2.Is_Public__c', 1);
                            $qr->where('salesforce_Pricebook2.IsActive', 1);
                            $qr->where('salesforce_Pricebook2.From__c', '<=',Carbon::now()->format('Y-m-d'));
                            $qr->where('salesforce_Pricebook2.To__c','>=', Carbon::now()->format('Y-m-d'));
                        })
                        ->where('salesforce_PricebookEntry.IsDeleted', 0)
                        ->groupBy('Id')

                        ->get(); //dd(DB::getQueryLog());
       
//        $pricebookEntry = DB::raw(
//            "select `salesforce_PricebookEntry`.* from `salesforce_PricebookEntry` inner join `salesforce_Pricebook2` on `salesforce_Pricebook2`.`Id` = `salesforce_PricebookEntry`.`Pricebook2Id`
//left join `salesforce_Club_Applied_Pricebook__c` on `salesforce_Club_Applied_Pricebook__c`.`Price_Book__c` = `salesforce_Pricebook2`.`Id`
//where `salesforce_Club_Applied_Pricebook__c`.`Club__c` = '{$input['club']}' and `salesforce_PricebookEntry`.`Product2Id` = '{$input['product']}'
//GROUP BY `salesforce_Club_Applied_Pricebook__c`.`Club__c`"
//        );
//        dd($pricebookEntry);
        // $pricebookEntry = PricebookEntry::get();
        if ( !empty($pricebookEntry->toArray()) ) return response()->json($pricebookEntry, 200);
        else return response()->json(['message' => 'No record found'], 404);
    }

    /**
     * add a product to Opty
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function saveProductToOppty(Request $request) {
        $postData = $request->post();
        $user = auth()->user();

        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);

        $validator = \Validator::make($postData, [
            'oppty' => 'required',
            'product' => 'required',
            'PricebookEntryId' => 'required',
            'club' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => implode(",",$validator->messages()->all())],422);
        } else {
            $opptyItem = Opportunity::find($postData['oppty']);

            $objectData = [
                "OpportunityId" => $postData['oppty'],
                "selectedProductId" => $postData['product'],
                "selectedClubId" => $postData['club'],
                "selectedPricebookEntryId" => $postData['PricebookEntryId'],
                "sessionQuantity" => $postData['sessionQuantity']??0
            ];

            //create schedule record in SF
            DB::beginTransaction();
            try {
//                $response = SObject::create('opportunity-add-product', $objectData);
                $responseData = ApexRest::post('app-api/v1/opportunity-add-product', $objectData);
                $content = $responseData->getBody()->getContents();
                $result = json_decode($content, true);

                if ( $result['success'] == false ) {
                    $errors = implode('; ', $result['error']);
                    return response()->json(['message' => $errors, 'data' => $objectData], 422);
                }
                $optItem = $result['result']['oppLineItem'];
                $chkItem = OpportunityLineItem::find($optItem['Id']);
                if ( empty($chkItem) ) {
                    //set date
                    $newItem = new OpportunityLineItem();
                    $newItem->fill($optItem);
                    if ( !isset($optItem['IsDeleted']) ) $newItem->IsDeleted = 0;
                    $newItem->save();

                    //log
                    activity('sale_pt_app')
                        ->causedBy($newItem)
                        ->withProperties(['OpportunityLineItem' => $newItem->toArray()])
                        ->log('OpportunityLineItem| create new Opportunity Line Item  #'.$optItem['Id']);

                    try {
                        $opptyObject = [
                            'Club__c' => $postData['club'],
                            'Pricebook2Id' => $postData['pricebookId']
                        ];
                        $responseUD = SObject::update('Opportunity', $postData['oppty'], $opptyObject);

                        //log
                        activity('sale_pt_app')
                            ->withProperties(['Opportunity' => $opptyObject])
                            ->log('Opportunity| Update Opportunity  #'.$postData['oppty']);

                        if ( isset($responseUD->status_code) && $responseUD->status_code == 400 ) {
                            return response()->json(['message' => $responseUD->message, 'data' => $objectData], $responseUD->status_code);
                        }

                    } catch ( ClientException $e) {
                        DB::rollBack();
//                        return response()->json($e->getMessage(), 422);
                        $mess = $e->getResponse()->getBody()->getContents();
                        $mess1 = json_decode($mess);
                        return response()->json(['message' => $mess1[0]->message], 422);
                    }

                    $opptyItem->Club__c = $postData['club'];
                    $opptyItem->Pricebook2Id = $postData['PricebookEntryId'];
                    $opptyItem->save();

                    DB::commit();
                    $optLineItem = OpportunityLineItem::find($optItem['Id']);
                    return response()->json(['message' => 'Save Product to Opportunity Success', 'data' => $optLineItem], 201);
                }

            } catch ( ClientException $e) {
                DB::rollBack();
                $mess = $e->getResponse()->getBody()->getContents();
                $mess1 = json_decode($mess);
//                return response()->json($e->getMessage(), 422);
                return response()->json(['message' => $mess1[0]->message], 422);
            }
        }
    }


    /**
     * Sep-15-2021 - task add product corporate [ https://beunik.atlassian.net/browse/CIT-639 ]
     * @param Request $request
     * @return mixed
     * @throws \App\Domains\TmtSfObject\Classes\SalesforceException
     */
    public function saveCorporateProduct(Request $request)
    {
        $user = auth()->user();
        //Son Add Role Sale,Pt
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);        
        $postData = $request->post();
        $opptyItem = Opportunity::find($postData['opportunityId']);

        if (!isset($opptyItem->Id)) {
            return response()->json(['message' => 'Invalid opty'],422);
        } else {
            $objectData = [
                "opportunityId" => $postData['opportunityId'],
                "productId" => $postData['productId'],
                "pricebookEntryId" => $postData['pricebookEntryId'],
                "pricebookId" => $postData['pricebookId'],
                "quantity" => $postData['quantity']??0,
                "promotionBookId" => $postData['promotionBookId'],
                "promotionItemIds" => $postData['promotionItemIds']??[],
                "giftIds" => $postData['giftIds']??[],
                "startDate" => $postData['startDate'],
                "checkInClubIds" => $postData['checkInClubIds']??[],
            ];

            //create product in SF
            try {
                $responseData = ApexRest::post('app-api/v1/opportunity-add-corporate-product', $objectData);
                $content = $responseData->getBody()->getContents();
                $result = json_decode($content, true);

                if ( $result['success'] == false ) {
                    $errors = implode('; ', $result['error']);
                    return response()->json(['message' => $errors, 'data' => $objectData], 422);
                }
                $optItem = $result['result']['oppLineItem'];
                $chkItem = OpportunityLineItem::find($optItem['Id']);
                if ( empty($chkItem) ) {
                    //set date
                    $newItem = new OpportunityLineItem();
                    $newItem->fill($optItem);
                    if ( !isset($optItem['IsDeleted']) ) $newItem->IsDeleted = 0;
                    $newItem->save();

                    //log
                    activity('sale_pt_app')
                        ->causedBy($newItem)
                        ->withProperties(['OpportunityLineItem' => $newItem->toArray()])
                        ->log('OpportunityLineItem| create new Opportunity Line Item  #'.$optItem['Id']);

                    try {
                        $opptyObject = [
                            'Pricebook2Id' => $postData['pricebookId']
                        ];
                        $responseUD = SObject::update('Opportunity', $opptyItem->Id, $opptyObject);

                        //log
                        activity('sale_pt_app')
                            ->withProperties(['Opportunity' => $opptyObject])
                            ->log('Opportunity| Update Opportunity  #'.$opptyItem->Id);

                        if ( isset($responseUD->status_code) && $responseUD->status_code == 400 ) {
                            return response()->json(['message' => $responseUD->message, 'data' => $objectData], $responseUD->status_code);
                        }

                    } catch ( ClientException $e) {
                        $mess = $e->getResponse()->getBody()->getContents();
                        $mess1 = json_decode($mess);
                        return response()->json(['message' => $mess1[0]->message], 422);
                    }

                    //$opptyItem->Club__c = $postData['club'];
                    $opptyItem->Pricebook2Id = $postData['pricebookEntryId'];
                    $opptyItem->save();

                    $optLineItem = OpportunityLineItem::find($optItem['Id']);
                    return response()->json(['message' => 'Save Product to Opportunity Success', 'data' => $optLineItem], 201);
                }

            } catch ( ClientException $e) {

                $mess = $e->getResponse()->getBody()->getContents();
                $mess1 = json_decode($mess);

                return response()->json(['message' => $mess1[0]->message], 422);
            }
        }


    }
}
