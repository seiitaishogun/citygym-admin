<?php
/**
 * @author tmtuan
 * created Date: 07-Dec-20
 */

namespace App\Domains\Crm\Http\Controllers\Api\Lead;

use App\Domains\Crm\Models\Lead;
use App\Domains\TmtSfObject\Classes\SObject;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Exception\ClientErrorResponseException;

class EditLeadController extends LeadController
{
    public function editLead($id, Request $request)
    {
        $postData = $request->post();
        $user = auth()->user();
        if ( !$user->hasRole(['Sale'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);

        $lead = Lead::where('Id', $id)
                    ->with(['StaffAssign' => function($qr){
                        $qr->select(['Id', 'Name']);
                    }])
                    ->where('Staff_Assignment__c', $user->sf_account_id())
                    ->get()->first();

        if (empty($lead)) {
            return response()->json([
                'status' => false,
                'message' => 'Lead does not exist or you don\'t have permission to edit this lead!',
                'lead' => $lead
            ], 422);
        }

        $validator = \Validator::make($postData, [
                'FirstName' => 'sometimes|required|min:2',
                'LastName' => 'sometimes|required|min:2',
        //                'Name' => 'sometimes|required|min:2',
        //                'Company' => 'sometimes|required|min:2',
            ],
            [
                'FirstName.required' => 'Vui lòng nhập Tên',
                'LastName.required' => 'Vui lòng nhập Họ',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->messages();
            return response()->json([
                'status' => false,
                'message' => $messages,
                'lead' => $lead
            ], 422);
        } else {
            //update record in SF
            DB::beginTransaction();
            try {
                unset($postData['Address']);
                unset($postData['Name']);
                $response = SObject::update('Lead', $lead->Id, $postData);
                if ( isset($response->status_code) && $response->status_code == 400 ) return response()->json(['message' => $response->message, 'data' => $postData], $response->status_code);

                if ( isset($postData['MiddleName']) && isset($postData['FirstName']) && isset($postData['LastName']) ) {
                    $postData['Name'] = (isset($postData['MiddleName'])) ? $postData['LastName'].' '.$postData['MiddleName'].' '.$postData['FirstName'] : $postData['LastName'].' '.$postData['FirstName'];
                }

                $lead->fill($postData);
                $lead->LastModifiedDate = date('Y-m-d H:i:s');
                $lead->sync_result = $response->status_code;
                if ($response->status_code == 200)
                    $lead->last_sync_success = date('Y-m-d H:i:s');
                $lead->save();

                //log
                activity('sale_pt_app')
                    ->withProperties(['Lead' => $lead->toArray()])
                    ->log('Lead| update Lead success  #'.$response);
                DB::commit();

                $newLead = Lead::with(['StaffAssign' => function($qr){
                        $qr->select(['Id', 'Name']);
                    }])
                    ->where('Id', $id)
                    ->where('Staff_Assignment__c', $user->sf_account_id())
                    ->get()->first();

                return response()->json([
                    'message' => 'Cập nhật thành công',
                    'status' => true,
                    'lead' => $newLead
                ], 200);
            } catch ( ClientException $e ) {
                DB::rollBack();
        //                $mess = $e->getMessage();
                $mess = $e->getResponse()->getBody()->getContents();
                $mess1 = json_decode($mess);
        //                $pos = strpos($mess, 'FIELD_CUSTOM_VALIDATION_EXCEPTION');
        //                if ( $pos !== false ) return response()->json(['message' => 'Không thể đổi trạng thái của Lead đã được Converted', 'status' => false], 200);
        //
        //                $pos = strpos($mess, 'REQUIRED_FIELD_MISSING');
        //                if ( $pos !== false ) return response()->json(['message' => 'Thiếu trường dữ liệu bắt buộc', 'status' => false], 200);

                return response()->json(['message' => $mess1[0]->message, 'lead' => $lead], 200);
            }

        }
    }

    public function listStatus() {
        return response()->json($this->status, 200);
    }
}
