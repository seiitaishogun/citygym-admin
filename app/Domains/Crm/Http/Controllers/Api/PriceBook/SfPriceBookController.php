<?php
/**
 * @author tmtuan
 * created Date: 11-Dec-20
 */
namespace App\Domains\Crm\Http\Controllers\Api\PriceBook;

use App\Domains\Crm\Models\Pricebook2;
use App\Domains\Crm\Models\PricebookEntry;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SfPriceBookController extends ApiController {
    public function createPriceBook(Request $request) {
        $postData = $request->post();
        $returnData = [];

        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['PriceBook' => $item])
                ->log('Request | createPriceBook - '.$request->path());


            $ctitem = Pricebook2::find($item['Id']);
            if ( !empty($ctitem) )
            {
                DB::beginTransaction();
                try {
                    unset($item['Id']);
                    $ctitem->fill($item);
                    $ctitem->LastModifiedDate = date('Y-m-d H:i:s');
                    $ctitem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['price_book' => $item])
                        ->log('PriceBook| Update Price Book success #' . $ctitem->Id);
                    DB::commit();
                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update Price Book success',
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
                $itemData = Pricebook2::create($item);
                //log
                activity('salesforce_api')
                    ->causedBy($itemData)
                    ->withProperties(['price_book' => $item])
                    ->log('PriceBook| create new Price Book success');

                // //create Price Book Entry
                // $this->createPriceBookEntry($postData['pricebookEntry']);

                $returnData[] = [
                    'success' => true,
                    'error' => "Create success",
                    'result' => $item['Id']
                ];
            }
        }
        return response()->json($returnData, 200);
    }

    public function createPriceBookEntry(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['PriceBookEntry' => $item])
                ->log('Request | createPriceBookEntry - '.$request->path());


            $ctitem = PricebookEntry::find($item['Id']);
            if( !empty($ctitem) || isset($ctitem->Id) )
            {
                try {
                    unset($item['Id']);
                    $ctitem->fill($item);
                    $ctitem->LastModifiedDate = date('Y-m-d H:i:s');
                    $ctitem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['price_book' => $item])
                        ->log('PriceBook| Update Price Book Entry success #' . $ctitem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update Price Book Entry success',
                        'result' => $ctitem->Id
                    ];
                } catch (\Exception $e) {

                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $ctitem->Id
                    ];
                    continue;
                }

            } else {
                $newItem = PricebookEntry::create($item);
                //log
                activity('salesforce_api')
                    ->causedBy($newItem)
                    ->withProperties(['price_book_entry' => $item])
                    ->log('PriceBookEntry| create new Price Book Entry item #'.$item['Id']);
                $returnData[] = [
                    'success' => true,
                    'error' => "Create Price Book Entry success",
                    'result' => $item['Id']
                ];
            }
        }
        return response()->json($returnData, 200);
    }

    public function deletePriceBook(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['PriceBook' => $item])
                ->log('Request | deletePriceBook - '.$request->path());


            $chkItem = Pricebook2::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['Pricebook2' => $item])
                        ->log('Pricebook2| Delete Pricebook2 success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete Pricebook2 success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['Pricebook2' => $item])
                        ->log('Delete Pricebook2 Fail | '.$e->getMessage());

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

    public function deletePriceBookEntry(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['PriceBookEntry' => $item])
                ->log('Request | deletePriceBookEntry - '.$request->path());


            $chkItem = PricebookEntry::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['PricebookEntry' => $item])
                        ->log('PricebookEntry| Delete PricebookEntry success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete PricebookEntry success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['PricebookEntry' => $item])
                        ->log('Delete PricebookEntry Fail | '.$e->getMessage());

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
