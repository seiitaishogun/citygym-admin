<?php
/**
 * @author tmtuan
 * created Date: 10-Dec-20
 */
namespace App\Domains\Crm\Http\Controllers\Api\Promotion;

use App\Domains\Crm\Models\Promotion;
use App\Domains\Crm\Models\PromotionItem;
use App\Domains\Crm\Models\PromotionItemClub;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SfPromotionController extends ApiController {
    public function createPromotion(Request $request) {
        $postData = $request->post();
        $returnData = [];

        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['Promotion' => $item])
                ->log('Request | createPromotion - '.$request->path());

            $promotionItem = Promotion::find($item['Id']);
            if (!empty($promotionItem))
            {
                DB::beginTransaction();
                try {
                    unset($item['Id']);
                    $promotionItem->fill($item);
                    $promotionItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $promotionItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['promotion' => $item])
                        ->log('Promotion| Update promotion success #' . $promotionItem->Id);
                    DB::commit();
                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update promotion success',
                        'result' => $promotionItem->Id
                    ];
                } catch (\Exception $e) {
                    DB::rollBack();
                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $promotionItem->Id
                    ];
                    continue;
                }
            } else {
                //set date
                $this->setDefaultDate($item);
                $promotion = Promotion::create($item);
                //log
                activity('salesforce_api')
                    ->causedBy($promotion)
                    ->withProperties(['promotion' => $item])
                    ->log('Promotion| create new promotion #'.$item['Id']);

                $returnData[] = [
                    'success' => true,
                    'error' => "Create success",
                    'result' => $item['Id']
                ];
            }

        }
        return response()->json($returnData, 200);

        // return response()->json([
        //     'message' => 'Promotion Data Created Successfull'
        // ], 201);

    }

    public function createPromotionItems(Request $request) {
        $returnData = [];

        $postData = $request->post();
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['PromotionItems' => $item])
                ->log('Request | createPromotionItems - '.$request->path());

            // create promotion item
            $promotionItem_c = PromotionItem::find($item['Id']);
            if (!empty($promotionItem_c))
            {
                DB::beginTransaction();
                try {
                    unset($item['Id']);
                    $promotionItem_c->fill($item);
                    $promotionItem_c->LastModifiedDate = date('Y-m-d H:i:s');
                    $promotionItem_c->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['promotion_item' => $item])
                        ->log('promotion_item| Update promotion item success #' . $promotionItem_c->Id);
                    DB::commit();
                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update promotion item success',
                        'result' => $promotionItem_c->Id
                    ];
                } catch (\Exception $e) {
                    DB::rollBack();
                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $promotionItem_c->Id
                    ];
                    continue;
                }
            } else {
                $promotionItems = PromotionItem::create($item);
                //log
                activity('salesforce_api')
                    ->causedBy($promotionItems)
                    ->withProperties(['promotion_item' => $item])
                    ->log('Promotion| create new promotion item #'.$item['Id']);

                $returnData[] = [
                    'success' => true,
                    'error' => "Create success",
                    'result' => $item['Id']
                ];
            }
        }
        return response()->json($returnData, 200);
    }

    public function createPromotionItemClub(Request $request) {
        $returnData = [];

        $postData = $request->post();
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['PromotionItemClub' => $item])
                ->log('Request | createPromotionItemClub - '.$request->path());

            // create promotion item club
            $promotionItemClub_c = PromotionItemClub::find($item['Id']);
            if (!empty($promotionItem_c))
            {
                DB::beginTransaction();
                try {
                    unset($item['Id']);
                    $promotionItem_c->fill($item);
                    $promotionItem_c->LastModifiedDate = date('Y-m-d H:i:s');
                    $promotionItem_c->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['promotion_item_club' => $item])
                        ->log('promotion_item_club| Update promotion item club success #' . $promotionItem_c->Id);
                    DB::commit();
                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update promotion item club success',
                        'result' => $promotionItem_c->Id
                    ];
                } catch (\Exception $e) {
                    DB::rollBack();
                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $promotionItem_c->Id
                    ];
                    continue;
                }
            } else {
                $promotionItemClub = PromotionItemClub::create($item);
                //log
                activity('salesforce_api')
                    ->causedBy($promotionItemClub)
                    ->withProperties(['promotion_item_club' => $item])
                    ->log('Promotion| create new promotion item club #'.$item['Id']);

                $returnData[] = [
                    'success' => true,
                    'error' => "Create success",
                    'result' => $item['Id']
                ];
            }
        }
        return response()->json($returnData, 200);
    }

    public function deletePromotion(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['Promotion' => $item])
                ->log('Request | deletePromotion - '.$request->path());

            $chkItem = Promotion::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['Promotion' => $item])
                        ->log('Promotion| Delete Promotion success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete Promotion success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['Promotion' => $item])
                        ->log('Delete Promotion Fail | '.$e->getMessage());

                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $chkItem->Id
                    ];
                    continue;
                }
            }
        }
        return response()->json($returnData, 200);
    }

    public function deletePromotionItem(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['PromotionItem' => $item])
                ->log('Request | deletePromotionItem - '.$request->path());

            $chkItem = PromotionItem::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['PromotionItem' => $item])
                        ->log('PromotionItem| Delete PromotionItem success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete PromotionItem success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['PromotionItem' => $item])
                        ->log('Delete PromotionItem Fail | '.$e->getMessage());

                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $chkItem->Id
                    ];
                    continue;
                }
            }
        }
        return response()->json($returnData, 200);
    }

    public function deletePromotionItemClub(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['PromotionItemClub' => $item])
                ->log('Request | deletePromotionItemClub - '.$request->path());

            $chkItem = PromotionItemClub::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['PromotionItemClub' => $item])
                        ->log('PromotionItemClub| Delete PromotionItemClub success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete PromotionItemClub success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['PromotionItemClub' => $item])
                        ->log('Delete PromotionItemClub Fail | '.$e->getMessage());

                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $chkItem->Id
                    ];
                    continue;
                }
            }
        }
        return response()->json($returnData, 200);
    }
}
