<?php
/**
 * @author tmtuan
 * created Date: 07-Dec-20
 */
namespace App\Domains\Crm\Http\Controllers\Api\Lead;

use App\Domains\Crm\Models\Lead;
use App\Domains\TmtSfObject\Classes\SObject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateLeadController extends LeadController {
    public function addLead(Request $request) {
        $postData = $request->post();
        $user = auth()->user();
        if ( !$user->hasRole(['Sale'])) return response()->json([
            'message' => 'You don\'t have permission on this action'
        ], 401);

        if ( empty($user->id) ) {
            return response()->json([
                'message' => 'Invalid Request'
            ], 400);
        }

        $validator = \Validator::make($postData, [
            'FirstName' => 'required|min:2',
            'LastName' => 'required|min:2',
            'MobilePhone' => 'required|min:2',
        //            'Email' => 'required'
            ],
            [
                'FirstName.required' => 'Vui lòng nhập Tên',
                'LastName.required' => 'Vui lòng nhập Họ',
                'MobilePhone.required' => 'Vui lòng nhập số điện thoại',
        //                'Email.required' => 'Vui lòng nhập email',
            ]
        );
        if ($validator->fails())
        {
            $messages = $validator->messages();
            return response()->json([
                'message' => $messages
            ], 422);
        }
        // $postData['Status'] = 'New';
        $postData['Staff_Assignment__c'] = $user->sf_account_id();
        $postData['Created_By_App_User__c'] = $user->sf_account_id();
        $postData['Status'] = "Assigned Staff";

        //create schedule record in SF
        DB::beginTransaction();
        try {
            //sample code for stress test only
        //            if (isset($postData['Id'])) {
        //                //set date
        //                $newItem = new Lead();
        //                $this->setDefaultDate($postData);
        //                $newItem->fill($postData);
        //                $newItem->Id = $postData['Id'];
        //                $newItem->Name = $postData['FirstName'].' '.$postData['LastName'];;
        //                $newItem->save();
        //
        //                //log
        //                activity('sale_pt_app')
        //                    ->causedBy($newItem)
        //                    ->withProperties(['Lead' => $newItem->toArray()])
        //                    ->log('Lead| create new Lead Item  #'.$postData['Id']);
        //                DB::commit();
        //
        //                $lead = Lead::select('Id', 'FirstName', 'LastName', 'Phone', 'Status', 'Gender__c', 'DOB__c', 'Company', 'Status')->find($postData['Id']);
        //                return response()->json(['message' => 'Save Lead Success', 'lead' => $lead], 201);
        //            } else {

                $response = SObject::create('Lead', $postData);
                if ( isset($response->status_code) && $response->status_code == 400 ) {  //dd($response);
                    return response()->json(['message' => $response->message, 'data' => $postData], $response->status_code);
                }

                //set date
                $newItem = new Lead();
                $this->setDefaultDate($postData);
                $newItem->fill($postData);
                $newItem->Id = $response;
                $newItem->Name = $postData['FirstName'].' '.$postData['LastName'];;
                $newItem->save();

                //log
                activity('sale_pt_app')
                    ->causedBy($newItem)
                    ->withProperties(['Lead' => $newItem->toArray()])
                    ->log('Lead| create new Lead Item  #'.$response);
                DB::commit();

                $lead = Lead::select('Id', 'FirstName', 'LastName', 'Phone', 'Status', 'Gender__c', 'DOB__c', 'Company', 'Status')->find($response);
                return response()->json(['message' => 'Save Lead Success', 'lead' => $lead], 201);
        //            }

        } catch ( \Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 422);
        }

    }

    /**
     * Tạo Lead Corporate
     * @param Request $request
     * @return mixed
     */
    public function addBusinessLead(Request $request)
    {
        $postData = $request->post();
        $user = auth()->user();
        if ( !$user->hasRole(['Sale'])) return response()->json([
            'message' => 'You don\'t have permission on this action'
        ], 401);

        if ( empty($user->id) ) {
            return response()->json([
                'message' => 'Invalid Request'
            ], 400);
        }

        $validator = \Validator::make($postData, [
            'FirstName' => 'required|min:2',
            'LastName' => 'required|min:2',
            'MobilePhone' => 'required|min:2',
            'Company' => 'required'
        ],
        [
            'FirstName.required' => 'Vui lòng nhập Tên',
            'LastName.required' => 'Vui lòng nhập Họ',
            'MobilePhone.required' => 'Vui lòng nhập số điện thoại',
            'Company.required' => 'Vui lòng nhập tên công ty',
        ]
        );
        if ($validator->fails())
        {
            $messages = $validator->messages();
            return response()->json([
                'message' => $messages
            ], 422);
        }

        $postData['Staff_Assignment__c'] = $user->sf_account_id();
        $postData['Created_By_App_User__c'] = $user->sf_account_id();
        $postData['Status'] = "Assigned Staff";
        $postData['Lead_Type__c'] = "Corporate";

        try {
            $response = SObject::create('Lead', $postData);
            if ( isset($response->status_code) && $response->status_code == 400 ) {  //dd($response);
                return response()->json(['message' => $response->message, 'data' => $postData], $response->status_code);
            }

            //set date
            $newItem = new Lead();
            $this->setDefaultDate($postData);
            $newItem->fill($postData);
            $newItem->Id = $response;
            $newItem->Name = $postData['FirstName'].' '.$postData['LastName'];;
            $newItem->save();

            //log
            activity('sale_pt_app')
                ->causedBy($newItem)
                ->withProperties(['Lead' => $newItem->toArray()])
                ->log('Lead| create new Lead Item  #'.$response);


            $lead = Lead::select('Id', 'FirstName', 'LastName', 'Phone', 'Status', 'Gender__c', 'DOB__c', 'Company', 'Status')->find($response);
            return response()->json(['message' => 'Save Lead Success', 'lead' => $lead], 201);
        } catch ( \Exception $e) {
            return response()->json($e->getMessage(), 422);
        }
    }
}
