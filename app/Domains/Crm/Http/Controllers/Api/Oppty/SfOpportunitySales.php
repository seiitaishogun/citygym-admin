<?php
/**
 * @author tmtuan
 * created Date: 14-May-21
 */
namespace App\Domains\Crm\Http\Controllers\Api\Oppty;


use App\Domains\Crm\Models\OppotunitySales;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;

class SfOpportunitySales extends ApiController {

    /**
     * Đồng bộ Opportunity Sale
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createOpportunitySale(Request $request)
    {
        $postData = $request->post();
        $returnData = [];
        foreach ($postData as $item) {

            $ctitem = OppotunitySales::find($item['Id']);
            if (!empty($ctitem)) {
                try {
                    unset($item['Id']);
                    $ctitem->fill($item);
                    $ctitem->LastModifiedDate = $item['LastModifiedDate'] ?? date('Y-m-d H:i:s');
                    $ctitem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['OpportunitySale' => $item])
                        ->log('Opportunity Sale| Update Opportunity sale success #' . $ctitem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update contract sale success',
                        'result' => $ctitem->Id
                    ];
                } catch (\Exception $e) {
                    
                    //log
                    activity('salesforce_api')
                        ->withProperties(['OpportunitySale' => $item])
                        ->log('Opportunity Sale| edit Opportunity Sale Fail <br> Message: '.$e->getMessage());

                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $ctitem->Id
                    ];
                    continue;
                }
            } else {
                try {
                    //set date
                    $this->setDefaultDate($item);

                    $insertItem = OppotunitySales::create($item);
                    //log
                    activity('salesforce_api')
                        ->causedBy($insertItem)
                        ->withProperties(['OpportunitySale' => $item])
                        ->log('Opportunity Sale| create new Opportunity Sale success #'.$item['Id']);
                    $returnData[] = [
                        'success' => true,
                        'error' => "Create success",
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['OpportunitySale' => $item])
                        ->log('Opportunity Sale| edit Opportunity Sale Fail <br> Message: '.$e->getMessage());
                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $item['Id']
                    ];
                    continue;
                }
            }
        }
        return response()->json($returnData, 200);
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editOpportunitySale($id, Request $request) {
        $postData = $request->all();

        $item = OppotunitySales::find($id);
        if ( empty($item) ) return response()->json([
            'success' => false,
            'error' => 'No item found',
            'result' => $id
        ], 200);
        else {
            DB::beginTransaction();
            try {
                unset($postData['Id']);
                $item->fill($postData);
                $item->save();

                //log
                activity('salesforce_api')
                    ->withProperties(['OpportunitySale' => $item])
                    ->log('Opportunity Sale| edit Opportunity sale success #'.$id);
                DB::commit();
                return response()->json([
                    'success' => true,
                    'error' => 'Update success',
                    'result' => $id
                ], 200);
            }catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                    'result' => $id
                ], 200);
            }

        }

    }
    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteOpportunitySale($id, Request $request) {
        //log request
        activity('salesforce_api')
            ->withProperties(['Contract-Sale-Id' => $id])
            ->log('Request | deleteContractSale - '.$request->path());

        $item = OppotunitySales::find($id);
        if ( empty($item) ) return response()->json([
            'success' => false,
            'error' => 'No item found',
            'result' => $id
        ], 200);
        else {
            $item->delete();
            return response()->json([
                'success' => true,
                'error' => 'Record delete success!',
                'result' => $id
            ], 200);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function mdeleteContractSale(Request $request) {
        $postData = $request->all();

        //log request
        activity('salesforce_api')
            ->withProperties(['ContractSale' => $postData])
            ->log('Request | mdeleteContractSale - '.$request->path());


        $returnData = [];
        foreach ($postData as $item) {
            $delItem = OppotunitySales::find($item['Id']);
            if (empty($delItem)) $returnData[] = [
                'success' => false,
                'error' => 'No item found',
                'result' => $item['Id']
            ];
            else {
                $delItem->delete();
                $returnData[] = [
                    'success' => true,
                    'error' => 'Record delete success!',
                    'result' => $item['Id']
                ];
            }
        }
        return response()->json($returnData, 200);
    }
}
