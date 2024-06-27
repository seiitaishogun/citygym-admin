<?php
/**
 * @author tmtuan
 * created Date: 09-Mar-21
 */
namespace App\Domains\Crm\Http\Controllers\Api\Contract;

use App\Domains\Crm\Models\Contract;
use App\Domains\Crm\Models\Product2;
use App\Domains\TmtSfObject\Classes\ApexRest;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;

class ContractTrialController extends ContractController {
    public function listProdTrial(Request $request) {
        $user = auth()->user();
        //Son Add Role Sale,Pt
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);        
        try {
            $responseData = ApexRest::get('app-api/v1/create-contract-trial');
            $content = $responseData->getBody()->getContents();
            $result = json_decode($content, true);

            if ( $result['success'] == false ) {
                $errors = implode('; ', $result['error']);
                return response()->json(['message' => $errors], 422);
            } else {
        //                $products = [];
        //                foreach ($result['result']['listProduct'] as $item) {
        //                    unset($item['attributes']);
        //                    $products[] = $item;
        //                }
                return response()->json(['data' => $result['result']['listProduct']], 200);
            }

        } catch (ClientException $e) {
            $mess = $e->getResponse()->getBody()->getContents();
            $mess1 = json_decode($mess);
            return response()->json(['message' => $mess1[0]->message], 422);
        }
    }

    public function createProdTrial(Request $request) {
        $postData = $request->post();
        $user = auth()->user();
        if ( !$user->hasRole(['Sale'])) return response()->json([
            'message' => 'Chỉ tài khoản Sale mới có quyền thực hiện hành động này'
        ], 401);

        $validator = \Validator::make($postData, [
            'oppty' => 'required',
            'product' => 'required',
            'start_date' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => implode(",",$validator->messages()->all())],422);
        } else {
            try {
                $objectData = [
                    'opportunityId' => $postData['oppty'],
                    'productId' => $postData['product'],
                    'startDate' => $postData['start_date'],
                    'checkInClubIds' => $postData['checkInClubIds']??[],
                ];
                $responseData = ApexRest::post('app-api/v1/create-contract-trial/', $objectData);
                $content = $responseData->getBody()->getContents();
                $result = json_decode($content, true);

                if ( $result['success'] == false ) {
                    $errors = implode('; ', $result['error']);
                    return response()->json(['message' => $errors, 'data' => $objectData], 422);
                } else {
                    $newContract = $result['result'];
                    //save contract
                    unset($newContract['attributes']);
                    $newItem = new Contract();
                    $newItem->fill($newContract);
                    $newItem->CreatedDate = date('Y-m-d H:i:s');
                    $newItem->IsDeleted = 0;
                    $newItem->save();

                    //log
                    activity('sale_pt_app')
                        ->causedBy($newItem)
                        ->withProperties(['Contract' => $newItem->toArray()])
                        ->log('Contract| create new Contract  #'.$newContract['Id']);

                    return response()->json(['message' => 'Đã thêm Contract thành công', 'data' => $newContract], 200);
                }

            } catch (ClientException $e) {
                $mess = $e->getResponse()->getBody()->getContents();
                $mess1 = json_decode($mess);
                return response()->json(['message' => $mess1[0]->message], 422);
            }
        }
    }
}
