<?php
/**
 * @author tmtuan
 * created Date: 22-Dec-20
 */
namespace App\Domains\Crm\Http\Controllers\Api\Oppty;

use App\Domains\Crm\Models\Opportunity;
use App\Domains\TmtSfObject\Classes\SObject;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EditOpptyController extends OpptyController {
    public function editOppty($id, Request $request) {
        $postData = $request->post();
        $user = auth()->user();
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);

        $query = Opportunity::where('Id', $id);

        if ( $user->hasRole('Sale') ) $query->where('Sales_Assign__c',$user->sf_account_id());
        else $query->where('PT_assign__c',$user->sf_account_id());

        $opptyItem = $query->get()->first();
        if ( empty($opptyItem) ) return response()->json(['message' => 'No item found!'], 404);

        $validator = \Validator::make($postData, [
            'Name' => 'sometimes|required|min:2',
        ]);
        if ($validator->fails()) {
            $messages = $validator->messages();
            return response()->json([
                'message' => $messages
            ], 422);
        }

        //update record in SF
        DB::beginTransaction();
        try {
            $response = SObject::update('Opportunity', $opptyItem->Id, $postData);
            if ( isset($response->status_code) && $response->status_code == 400 ) {
                return response()->json(['message' => $response->message, 'data' => $postData], $response->status_code);
            }

            $opptyItem->fill($postData);
            $opptyItem->sync_result = $response->status_code;
            if ($response->status_code == 200)
                $opptyItem->last_sync_success = date('Y-m-d H:i:s');
            $opptyItem->save();

            //log
            activity('sale_pt_app')
                ->withProperties(['Opportunity' => $opptyItem->toArray()])
                ->log('Opportunity| update Opportunity success  #'.$response);
            DB::commit();

            return response()->json([
                'message' => 'Cập nhật thành công',
                'oppty' => $opptyItem
            ], 200);
        } catch ( ClientException $e) {
            DB::rollBack();
//            $mess = $e->getMessage();
//            $pos = strpos($mess, 'FIELD_CUSTOM_VALIDATION_EXCEPTION');
//            if ( $pos !== false ) return response()->json(['message' => 'Không thể đổi trạng thái của Oppotunity!', 'status' => false], 200);
            $mess = $e->getResponse()->getBody()->getContents();
            $mess1 = json_decode($mess);

            return response()->json($mess1[0], 422);
        }

    }
}
