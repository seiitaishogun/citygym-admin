<?php
/**
 * @author tmtuan
 * created Date: 24-Dec-20
 */
namespace App\Domains\Crm\Http\Controllers\Api\Benefit;

use App\Domains\Crm\Models\Benefit;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SfBenefitController extends ApiController {

    public function createBenefit (Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['Benefit' => $item])
                ->log('Request | createBenefit - '.$request->path());


            $chkItem = Benefit::find($item['Id']);
            if (!empty($chkItem)) {

                DB::beginTransaction();
                try {
                    unset($item['Id']);
                    $chkItem->fill($item);
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['benefit' => $item])
                        ->log('Benefit| Update Benefit success #' . $chkItem->Id);
                    DB::commit();
                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update Benefit success',
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

                    $newItem = Benefit::create($item);
                    //log
                    activity('salesforce_api')
                        ->causedBy($newItem)
                        ->withProperties(['benefit' => $item])
                        ->log('Benefit| create new benefit #' . $item['Id']);

                    $returnData[] = [
                        'success' => true,
                        'error' => "Create new benefit success",
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

    public function deleteBenefit(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['Benefit' => $item])
                ->log('Request | deleteBenefit - '.$request->path());


            $chkItem = Benefit::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['benefit' => $item])
                        ->log('Benefit| Delete Benefit success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete Benefit success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['Benefit' => $item])
                        ->log('Delete Benefit Fail | '.$e->getMessage());

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
