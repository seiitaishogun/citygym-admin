<?php
/**
 * @author tmtuan
 * created Date: 19-May-21
 */
namespace App\Domains\Crm\Http\Controllers\Api\Oppty;

use App\Domains\Auth\Models\User;
use App\Domains\Crm\Models\Opportunity;
use App\Domains\Crm\Models\OppotunitySales;
use App\Domains\TmtSfObject\Classes\SObject;
use Illuminate\Http\Request;

class OpptySaleController extends OpptyController {

    /**
     * Lấy danh sách Oppty sale theo Oppty ID
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listOpptySale(Request $request) {
        $user = auth()->user();
        //Son Add Role Sale,Pt
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);        
        $optyId = $request->get('opty');

        if ( empty($optyId) ) return \response()->json(['message' => 'Invalid Oppotunity Id'], 422);

        $sales = OppotunitySales::where('Opportunity__c', $optyId)
                ->where('IsDeleted', 0)->get();

        $responseData = [];
        foreach ($sales as $item) {
            $saleData = User::join('user_sf_account', 'user_sf_account.user_id', '=', 'users.id')
                ->select(['users.id', 'username', 'user_sf_account.sf_account_id', 'name', 'last_name', 'first_name', 'phone'])
                ->where('user_sf_account.sf_account_id', $item->Sales__c)
                ->first();
            if ( isset($saleData->id) ) {
                $saleData->opptySale = $item->Id;
                $responseData[] = $saleData;
            }
        }
        return response()->json($responseData);
    }

    /**
     * Thêm 1 sale vào oppty
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addSale(Request $request) {
        $postData = $request->post();
        $user = auth()->user();
        //Son Add Role Sale,Pt
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);
        $validator = \Validator::make($postData, [
            'Opportunity__c' => 'required',
            'Sales__c' => 'required',
        ],
            [
                'Opportunity__c.required' => 'Vui lòng nhập Oppty Id',
                'Sales__c.required' => 'Vui lòng nhập Sale Id',
            ]
        );
        if ($validator->fails())
        {
            return response()->json([
                'message' => implode(",",$validator->messages()->all())
            ], 422);
        }
        //check sale assign
        $oppty = Opportunity::where('Id', $postData['Opportunity__c'])
                ->where('Sales_Assign__c', $user->sf_account_id())
                ->where('IsDeleted', 0)->first();

        if ( !isset($oppty->Id) ) return response()->json([
            'message' => 'Bạn không có quyền thêm Sale vào Oppty này!'
        ], 422);

        try {
            $response = SObject::create('Opportunity_Sales__c', $postData);
            if ( isset($response->status_code) && $response->status_code == 400 ) {  //dd($response);
                return response()->json(['message' => $response->message, 'data' => $postData], $response->status_code);
            }
            $newItem = new OppotunitySales();
            $newItem->fill($postData);
            $newItem->IsDeleted = 0;
            $newItem->Id = $response;
            $newItem->save();
            //log
            activity('sale_pt_app')
                ->causedBy($newItem)
                ->withProperties(['Opportunity_Sales' => $newItem->toArray()])
                ->log('Opportunity_Sales| create new Opportunity Sales Item  #'.$response);

            return response()->json([
                'message' => 'Add Sale Success!'
            ], 200);
        }catch ( \Exception $e) {
            return response()->json($e->getMessage(), 422);
        }
    }

    /**
     * Xóa 1 sale ra khỏi opty
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteOptySale(Request $request) {
        $postData = $request->post();
        $user = auth()->user();
        //Son Add Role Sale,Pt
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);        
        $validator = \Validator::make($postData, [
            'Opportunity__c' => 'required',
            'Sales__c' => 'required',
        ],
            [
                'Opportunity__c.required' => 'Vui lòng nhập Oppty Id',
                'Sales__c.required' => 'Vui lòng nhập Sale Id',
            ]
        );
        if ($validator->fails())
        {
            return response()->json([
                'message' => implode(",",$validator->messages()->all())
            ], 422);
        }

        //check sale assign
        $oppty = Opportunity::where('Id', $postData['Opportunity__c'])
            ->where('Sales_Assign__c', $user->sf_account_id())
            ->where('IsDeleted', 0)
            ->first();

        if ( !isset($oppty->Id) ) return response()->json([
            'message' => 'Bạn không có quyền xóa Sale trong Oppty này!'
        ], 422);
        //try to remove sale
        $optySale = OppotunitySales::where('Opportunity__c', $postData['Opportunity__c'])
                ->where('Sales__c', $postData['Sales__c'])
                ->first();
        if ( !isset($optySale->Id) ) return response()->json([
            'message' => 'Sale với Id #'.$postData['Sales__c'].' không có trong Oppty này!'
        ], 422);

        try {
            $response = SObject::delete('Opportunity_Sales__c', $optySale->Id);
            if ( isset($response->status_code) && $response->status_code == 400 ) {  //dd($response);
                return response()->json(['message' => $response->message, 'data' => $postData], $response->status_code);
            }
            $optySale->IsDeleted = 1;
            $optySale->save();

            //log
            activity('sale_pt_app')
                ->causedBy($optySale)
                ->withProperties(['Opportunity_Sales' => $optySale->toArray()])
                ->log('Opportunity_Sales| delete Opportunity Sales Item  #'.$response);

            return response()->json([
                'message' => 'Delete Sale Success!'
            ], 200);
        }catch ( \Exception $e) {
            return response()->json($e->getMessage(), 422);
        }

    }

}
