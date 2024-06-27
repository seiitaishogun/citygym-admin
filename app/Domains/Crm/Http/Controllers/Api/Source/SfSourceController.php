<?php
/**
 * @author tmtuan
 * created Date: 15-Dec-20
 */
namespace App\Domains\Crm\Http\Controllers\Api\Source;

use App\Domains\Crm\Models\Source;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SfSourceController extends ApiController {
    public function createSource(Request $request) {
        $postData = $request->post();
        $returnData = [];

        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['Source' => $item])
                ->log('Request | createSource - '.$request->path());

            $scItem = Source::find($item['Id']);
            if (!empty($scItem))
            {
                DB::beginTransaction();
                try {
                    unset($item['Id']);
                    $scItem->fill($item);
                    $scItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $scItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['source' => $item])
                        ->log('Source| Update source success #' . $scItem->Id);
                    DB::commit();
                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update source success',
                        'result' => $scItem->Id
                    ];
                } catch (\Exception $e) {
                    DB::rollBack();
                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $scItem->Id
                    ];
                    continue;
                }
            } else {
                try {
                    //set date
                    $this->setDefaultDate($item);
                    $sourceData = Source::create($item);
                } catch (\Exception $e) {
                    $returnData[] = [
                        'success' => false,
                        'error' => "Invalid form field for Source table!",
                        'result' => $item['Id']
                    ];
                    continue;
                }

                //log
                activity('salesforce_api')
                    ->causedBy($sourceData)
                    ->withProperties(['source' => $item])
                    ->log('Source| create new Source success #'.$item['Id']);
                $returnData[] = [
                    'success' => true,
                    'error' => "Create success",
                    'result' => $item['Id']
                ];
            }
        }

        return response()->json($returnData, 200);
    }

    public function deleteSource(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['Source' => $item])
                ->log('Request | deleteSource - '.$request->path());

            $chkItem = Source::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['Source' => $item])
                        ->log('Source| Delete Source success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete Source success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['Source' => $item])
                        ->log('Delete Source Fail | '.$e->getMessage());

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
