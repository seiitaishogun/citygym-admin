<?php
/**
 * @author tmtuan
 * created Date: 28-Dec-20
 */
namespace App\Domains\Crm\Http\Controllers\Api\Contract;

use App\Domains\Crm\Models\Contract;
use App\Domains\Crm\Models\ContractSale;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Domains\TmtSfObject\Classes\SObject;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ClientErrorResponseException;

class EditContractController extends ContractController {
    public function editContract($id, Request $request)
    {
        $user = auth()->user();
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);
                
        $postData = $request->post();
        $user = auth()->user();
        $ContractSale = ContractSale::where('IsDeleted', 0)
        ->where('Sales__c', $user->sf_account_id())
        ->where('Contract__c', $id)
        ->get()->first();
        
        

        if (empty($ContractSale)) {
            return response()->json([
                'status' => false,
                'message' => 'Contract does not exist or you don\'t have permission to edit this lead!'
            ], 422);
        }

        $item = Contract::where('Id', $id)->get()->first();        

        DB::beginTransaction();
            try {
                $response = SObject::update('Contract', $id, $postData);
                if ( isset($response->status_code) && $response->status_code == 400 ) return response()->json(['message' => $response->message, 'data' => $postData], $response->status_code);

                if ( isset($postData['MiddleName']) && isset($postData['FirstName']) && isset($postData['LastName']) ) {
                    $postData['Name'] = (isset($postData['MiddleName'])) ? $postData['LastName'].' '.$postData['MiddleName'].' '.$postData['FirstName'] : $postData['LastName'].' '.$postData['FirstName'];
                }

                $item->fill($postData);
                $item->LastModifiedDate = date('Y-m-d H:i:s');
                $item->save();

                //log
                activity('sale_pt_app')
                    ->withProperties(['Contract' => $item->toArray()])
                    ->log('Contract| update Contract success  #'.$response);
                DB::commit();

                return response()->json([
                    'message' => 'Cập nhật thành công',
                    'status' => true,
                    'contract' => $item
                ], 200);
            } catch ( ClientException $e ) {
                DB::rollBack();
                $mess = $e->getResponse()->getBody()->getContents();
                $mess1 = json_decode($mess);

                return response()->json(['message' => $mess1[0]->message], 200);
            }        


        return response()->json([
            'message' => 'Cập nhật thành công',
            'contract' => $item
        ], 200);
    }
}
