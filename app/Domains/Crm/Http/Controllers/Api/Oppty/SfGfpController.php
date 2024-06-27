<?php
/**
 * @author tmtuan
 * created Date: 23-Dec-20
 */

namespace App\Domains\Crm\Http\Controllers\Api\Oppty;

use App\Domains\Crm\Models\GFP;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;

class SfGfpController extends ApiController{
    public function createGfp(Request $request) {
        $postData = $request->post();
        $returnData = [];

        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['Gfp' => $item])
                ->log('Request | createGfp - '.$request->path());

            $ctitem = GFP::find($item['Id']);
            if (!empty($ctitem)) {
                try {
                    unset($item['Id']);
                    $ctitem->fill($item);
                    $ctitem->LastModifiedDate = date('Y-m-d H:i:s');
                    $ctitem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['GFP' => $item])
                        ->log('GFP| Update GFP success #' . $ctitem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update GFP success',
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
                //set date
                $this->setDefaultDate($item);

                $newItem = GFP::create($item);
                //log
                activity('salesforce_api')
                    ->causedBy($newItem)
                    ->withProperties(['GFP' => $item])
                    ->log('GFP| create new gfp Id #'.$item['Id']);
                $returnData[] = [
                    'success' => true,
                    'error' => "Create success",
                    'result' => $item['Id']
                ];
            }
        }

        return response()->json($returnData, 200);
    }

    public function deleteGfp(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['Gfp' => $item])
                ->log('Request | deleteGfp - '.$request->path());


            $chkItem = GFP::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['GFP' => $item])
                        ->log('GFP| Delete GFP success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete GFP success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['GFP' => $item])
                        ->log('Delete GFP Fail | '.$e->getMessage());

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
