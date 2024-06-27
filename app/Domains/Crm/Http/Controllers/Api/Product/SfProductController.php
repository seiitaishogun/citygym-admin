<?php
/**
 * @author tmtuan
 * created Date: 11-Dec-20
 */
namespace App\Domains\Crm\Http\Controllers\Api\Product;

use App\Domains\Crm\Models\Product2;
use App\Domains\Crm\Models\ProductBenefit;
use App\Domains\Crm\Models\ProductGift;
use App\Domains\Crm\Models\ProductPromotion;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SfProductController extends ApiController {
    public function createProduct(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            $pdtItem = Product2::find($item['Id']);
            if (!empty($pdtItem))
            {
                DB::beginTransaction();
                try {
                    unset($item['Id']);
                    $pdtItem->fill($item);
                    $pdtItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $pdtItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['product' => $pdtItem->toArray()])
                        ->log('Product| Update product  success #' . $pdtItem->Id);
                    DB::commit();
                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update product success',
                        'result' => $pdtItem->Id
                    ];
                } catch (\Exception $e) {
                    DB::rollBack();
                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $pdtItem->Id
                    ];
                    continue;
                }
            } else {
                try {
                    //set date
                    $this->setDefaultDate($item);
                    $itemData = Product2::create($item);
                } catch (\Exception $e) {
                    $returnData[] = [
                        'success' => false,
                        'error' =>"Invalid form field for Product table!",
                        'result' => $item['Id']
                    ];
                }

                //log
                activity('salesforce_api')
                    ->causedBy($itemData)
                    ->withProperties(['product' => $item])
                    ->log('Product| create new Product success #'.$item['Id']);

                $returnData[] = [
                    'success' => true,
                    'error' => "Create success",
                    'result' => $item['Id']
                ];
            }

        }

        return response()->json($returnData, 200);
    }

    public function createProductBenefit(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['ProductBenefit' => $item])
                ->log('Request | createProductBenefit - '.$request->path());

            $ctitem = ProductBenefit::find($item['Id']);
            if( !empty($ctitem) )
            {
                try {
                    unset($item['Id']);
                    $ctitem->fill($item);
                    $ctitem->LastModifiedDate = date('Y-m-d H:i:s');
                    $ctitem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['product_benefit' => $ctitem->toArray()])
                        ->log('product_benefit| Update product benefit  success #' . $ctitem->Id);
                    DB::commit();
                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update product benefit success',
                        'result' => $ctitem->Id
                    ];
                } catch (\Exception $e) {
                    DB::rollBack();
                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $ctitem->Id
                    ];
                    continue;
                }
            } else {
                //set date
                $this->setDefaultDate($item);
                $itemData = ProductBenefit::create($item);
                //log
                activity('salesforce_api')
                    ->causedBy($itemData)
                    ->withProperties(['product_benefit' => $item])
                    ->log('product_benefit| create new Product Benefit item');
                $returnData[] = [
                    'success' => true,
                    'error' => "Create success",
                    'result' => $item['Id']
                ];
            }

        }
        return response()->json($returnData, 200);
    }

    public function createProductGift(Request $request) {
        $postData = $request->post();
        $returnData = [];

        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['ProductGift' => $item])
                ->log('Request | createProductGift - '.$request->path());

            $ctitem = ProductGift::find($item['Id']);
            if( !empty($ctitem) ) {
                try {
                    unset($item['Id']);
                    $ctitem->fill($item);
                    $ctitem->LastModifiedDate = date('Y-m-d H:i:s');
                    $ctitem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['product_gift' => $ctitem->toArray()])
                        ->log('product_gift| Update product gift  success #' . $ctitem->Id);
                    DB::commit();
                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update product gift success',
                        'result' => $ctitem->Id
                    ];
                } catch (\Exception $e) {
                    DB::rollBack();
                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $ctitem->Id
                    ];
                    continue;
                }
            } else {
                //set date
                $this->setDefaultDate($item);
                $itemData = ProductGift::create($item);
                //log
                activity('salesforce_api')
                    ->causedBy($itemData)
                    ->withProperties(['product_gift' => $item])
                    ->log('product_gift| create new Product Gift item');
                $returnData[] = [
                    'success' => true,
                    'error' => "Create success",
                    'result' => $item['Id']
                ];
            }
        }
        return response()->json($returnData, 200);
    }

    public function createProductPromotion(Request $request) {
        $postData = $request->post();
        $returnData = [];

        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['ProductPromotion' => $item])
                ->log('Request | createProductPromotion - '.$request->path());


            $ctitem = ProductPromotion::find($item['Id']);
            if( !empty($ctitem) ) {
                try {
                    unset($item['Id']);
                    $ctitem->fill($item);
                    $ctitem->LastModifiedDate = date('Y-m-d H:i:s');
                    $ctitem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['product_promotion' => $ctitem->toArray()])
                        ->log('product_promotion| Update product promotion  success #' . $ctitem->Id);
                    DB::commit();
                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update product promotion success',
                        'result' => $ctitem->Id
                    ];
                } catch (\Exception $e) {
                    DB::rollBack();
                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $ctitem->Id
                    ];
                    continue;
                }
            } else {
                //set date
                $this->setDefaultDate($item);
                $itemData = ProductPromotion::create($item);
                //log
                activity('salesforce_api')
                    ->causedBy($itemData)
                    ->withProperties(['product_promotion' => $item])
                    ->log('product_promotion| create new Product Promotion item');
                $returnData[] = [
                    'success' => true,
                    'error' => "Create success",
                    'result' => $item['Id']
                ];
            }
        }

        return response()->json($returnData, 200);
    }

    public function deleteProduct(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['Product' => $item])
                ->log('Request | deleteProduct - '.$request->path());


            $chkItem = Product2::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['Product2' => $item])
                        ->log('Product2| Delete Product2 success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete Product2 success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['Product2' => $item])
                        ->log('Delete Product2 Fail | '.$e->getMessage());

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

    public function deleteProductBenefit(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['ProductBenefit' => $item])
                ->log('Request | deleteProductBenefit - '.$request->path());


            $chkItem = ProductBenefit::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['ProductBenefit' => $item])
                        ->log('ProductBenefit| Delete ProductBenefit success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete ProductBenefit success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['ProductBenefit' => $item])
                        ->log('Delete ProductBenefit Fail | '.$e->getMessage());

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

    public function deleteProductGift(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['ProductGift' => $item])
                ->log('Request | deleteProductGift - '.$request->path());


            $chkItem = ProductGift::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['ProductGift' => $item])
                        ->log('ProductGift| Delete ProductGift success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete ProductGift success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['ProductGift' => $item])
                        ->log('Delete ProductGift Fail | '.$e->getMessage());

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

    public function deleteProductPromotion(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['ProductPromotion' => $item])
                ->log('Request | deleteProductPromotion - '.$request->path());


            $chkItem = ProductPromotion::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['ProductPromotion' => $item])
                        ->log('ProductPromotion| Delete ProductPromotion success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete ProductPromotion success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['ProductPromotion' => $item])
                        ->log('Delete ProductPromotion Fail | '.$e->getMessage());

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
