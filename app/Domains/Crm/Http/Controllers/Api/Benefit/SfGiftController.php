<?php
/**
 * @author tmtuan
 * created Date: 25-Dec-20
 */
namespace App\Domains\Crm\Http\Controllers\Api\Benefit;

use App\Domains\Crm\Models\Gift;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SfGiftController extends ApiController {
    public function createGift(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['Gift' => $item])
                ->log('Request | createGift - '.$request->path());


            $chkItem = Gift::find($item['Id']);
            if (!empty($chkItem)) {
                DB::beginTransaction();
                try {
                    unset($item['Id']);
                    $chkItem->fill($item);
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['gift' => $item])
                        ->log('Gift| Update Gift success #' . $chkItem->Id);
                    DB::commit();
                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update Gift success',
                        'result' => $chkItem->Id
                    ];
                } catch (\Exception $e) {
                    DB::rollBack();
                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $chkItem->Id
                    ];
                    continue;
                }
            } else {
                try {
                    //set date
                    $this->setDefaultDate($item);

                    $newItem = Gift::create($item);
                    //log
                    activity('salesforce_api')
                        ->causedBy($newItem)
                        ->withProperties(['gift' => $item])
                        ->log('Gift| create new gift #' . $item['Id']);

                    $returnData[] = [
                        'success' => true,
                        'error' => "Create new gift success",
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $item['Id']
                    ];
                }
            }
        }
        return response()->json($returnData, 200);
    }

    public function deleteGift(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['Gift' => $item])
                ->log('Request | deleteGift - '.$request->path());


            $chkItem = Gift::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['Gift' => $item])
                        ->log('Gift| Delete Gift success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete Gift success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['Gift' => $item])
                        ->log('Delete Gift Fail | '.$e->getMessage());

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
