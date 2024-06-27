<?php
/**
 * @author tmtuan
 * created Date: 28-Dec-20
 */
namespace App\Domains\Crm\Http\Controllers\Api\Contract;

use App\Domains\Crm\Models\Contract;
use App\Domains\Crm\Models\ContractSale;
use App\Domains\TmtSfObject\Classes\SObject;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\sfconnect\sfconnect;

class ContractSaleController extends ContractController {

    public function listSaleContract(Request $request) {
        $user = auth()->user();
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);

        $input = $request->query();


        /**********************************************************************************/
        /**
         * old code - query contract direct form SF
         */
        $ContractSale = ContractSale::where('IsDeleted', 0)
                        ->where('Sales__c', $user->sf_account_id())->get();

        if ( empty($ContractSale->toArray()) ) return response()->json(['message' => 'No items Found'], 404);
        $fieldIN = "";
        if($ContractSale){

            foreach ($ContractSale as $item) {
                if($fieldIN == "")
                    $fieldIN.= "'".$item->Contract__c."'";
                else
                $fieldIN.= ","."'".$item->Contract__c."'";
            }
        }

       $sfconnect = new sfconnect();
       $query = "SELECT " . $this->selectString . ",Account.Id,Account.Name,Account.LastName,Account.FirstName,Account.Salutation,Account.MiddleName,Account.Suffix,Account.Type,Account.Phone,Account.AccountNumber,
       Account.PhotoUrl,Opportunity__r.Name,Account.Acc_Code__c,Account.PersonMobilePhone,Account.Code__c";
        // $query = "SELECT " . $selectString ;
        $query .= " FROM Contract ";

        $query .= " WHERE Id IN  (".$fieldIN.")";
        $query .= " AND IsDeleted = false";
        // $query .= " WHERE AccountId = '" . $user->sf_account_id() . "'";
        // $query .= " WHERE Id = '" . $id . "'";

       // $query = "SELECT " . $selectString . "
       //  FROM Contract
       //  WHERE AccountId = '" . $user->sf_account_id() . "'";

        if ( isset($input['type']) && $input['type'] == 'account_number' ) {
            if ( isset($input['search_text']) && !empty($input['search_text']) ) {
                $search = $input['search_text'];
                $query .= " AND Account.Code__c LIKE '%".$search."%'";
            }
        }
        if ( isset($input['type']) && $input['type'] == 'contract_code' ) {
            if ( isset($input['search_text']) && !empty($input['search_text']) ) {
                $search = $input['search_text'];
                $query .= " AND Contract_Number__c LIKE '%".$search."%'";
            }
        }

        if ( isset($input['startDate']) && !empty($input['startDate']) && isset($input['endDate']) && !empty($input['endDate'])) {
            $startDate = Carbon::createFromFormat('m/d/Y H:i:s', $input['startDate'].' 00:00:00');
            $endDate = Carbon::createFromFormat('m/d/Y H:i:s', $input['endDate'].' 23:59:59');
            $query .= " AND CreatedDate >= ". $startDate->format('Y-m-d')."T00:00:00Z";
            $query .= " AND CreatedDate <= ". $endDate->format('Y-m-d')."T23:59:59Z";
        }

       try {
           $rs = $sfconnect->callQuery($query);
           return response()->json($rs, 200);
       } catch (Exception $e) {
           return response()->json('', 200);
       }

       return response()->json('', 200);
        /**********************************************************************************/


        //         $fieldSelect = [];
        //         foreach ($this->fieldSelect as $item) {
        //             $fieldSelect[] = 'salesforce_Contract.'.$item;
        //         }
        //         DB::enableQueryLog();
        //         $query = Contract::join('salesforce_Contract_Sale__c', 'salesforce_Contract_Sale__c.Contract__c', '=', 'salesforce_Contract.Id')
        //                         ->with(['Account' => function($md){
        //                             $md->select(['Id', 'Name', 'LastName', 'FirstName', 'Salutation', 'MiddleName', 'Suffix', 'Type', 'Phone', 'AccountNumber', 'PhotoUrl']);
        //                         }])
        //                         ->select($fieldSelect)
        //                         ->where('salesforce_Contract_Sale__c.IsDeleted', 0)
        //                         ->where('salesforce_Contract_Sale__c.Sales__c', $user->sf_account_id());

        //         if ( isset($input['type']) && $input['type'] == 'account_number' ) {
        //             if ( isset($input['search_text']) && !empty($input['search_text']) ) {
        //                 $search = $input['search_text'];
        //                 $query->join('salesforce_Account', 'salesforce_Account.id', '=', 'salesforce_Contract.AccountId')->where('salesforce_Account.Code__c', 'like', '%' .$search. '%');
        // //                $query->with(['Account' => function ($qr) use ($search) {
        // //                    $qr->where('AccountNumber', 'like', '%' .$search. '%');
        // //                }]);
        //             }
        //         }

        //         if ( isset($input['type']) && $input['type'] == 'contract_code' ) {
        //             if ( isset($input['search_text']) && !empty($input['search_text']) ) {
        //                 $query->where('salesforce_Contract.ContractNumber' , 'like', '%' .$input['search_text']. '%');
        //             }
        //         }

        //         if ( isset($input['startDate']) && !empty($input['startDate']) && isset($input['endDate']) && !empty($input['endDate'])) {
        //             $startDate = Carbon::createFromFormat('m/d/Y H:i:s', $input['startDate'].' 00:00:00');
        //             $endDate = Carbon::createFromFormat('m/d/Y H:i:s', $input['endDate'].' 23:59:59');

        //             $query->where('salesforce_Contract.CreatedDate', '>=', $startDate->format('Y-m-d H:i:s'))
        //                 ->where('salesforce_Contract.CreatedDate', '<=', $endDate->format('Y-m-d H:i:s'));
        //         }
        //         $contracts = $query->get();
        // //        dd(DB::getQueryLog());
        //         if ( empty($contracts->toArray()) ) return response()->json(['message' => 'No items Found'], 404);
        //         return response()->json($contracts, 200);
    }

    /**
     * Lấy danh sách hợp đồng theo Sale ID
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listSaleContractLocal(Request $request) {
        $user = auth()->user();
        $input = $request->query();

        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);


        //        if ( isset($input['debug']) ) { // thử nghiệm gọi trực tiếp trên SF thông qua REST API
        //            $ContractSale = ContractSale::where('IsDeleted', 0)
        //                ->where('Sales__c', $user->sf_account_id())->get();
        //
        //            if ( empty($ContractSale->toArray()) ) return response()->json(['message' => 'No items Found'], 404);
        //            $fieldIN = "";
        //            if($ContractSale){
        //
        //                foreach ($ContractSale as $item) {
        //                    if($fieldIN == "")
        //                        $fieldIN.= "'".$item->Contract__c."'";
        //                    else
        //                        $fieldIN.= ","."'".$item->Contract__c."'";
        //                }
        //            }
        //            $query = "SELECT " . $this->selectString . ",Account.Id,Account.Name,Account.LastName,Account.FirstName,Account.Salutation,Account.MiddleName,Account.Suffix,Account.Type,Account.Phone,Account.AccountNumber,
        //       Account.PhotoUrl,Opportunity__r.Name,Account.Acc_Code__c,Account.PersonMobilePhone,Account.Code__c";
        //            // $query = "SELECT " . $selectString ;
        //            $query .= " FROM Contract ";
        //
        //            $query .= " WHERE Id IN  (".$fieldIN.")";
        //            $query .= " AND IsDeleted = false";
        //
        //            $response = SObject::query($query);
        //            dd($response, [$query, $user->sf_account_id()]);
        //        }
        //DB::enableQueryLog();
        /**********************************************************************************/
        /**
         * new code use object contract from DB
         */
        $fieldsSelect = ['salesforce_Contract.Id', 'AccountId', 'Account_Receivable__c', 'Actual_Amount__c', 'ContractNumber', 'Contract_Number__c', 'Contract_Signed__c',
        'Contract_Status__c', 'salesforce_Contract.CreatedDate', 'EndDate', 'StartDate', 'salesforce_Contract.Description', 'Full_Name__c', 'Paid_Amount__c',
            'Status', 'StatusCode', 'Total_Payment_Status__c', 'Sale_Compliance__c', 'Opportunity_Name__c', 'Remaining_Amount__c', 'Remaining_Duration_Formatted__c',
            'IsInstallment__c', 'Other_Sale__c', 'Ignore_Promotion_Expire__c', 'Card_Status__c', 'Sale_Compliance__c', 'Contract_Number_Searchable__c','Sale_Note__c','Days_Since_Last_Checkin__c', 'Last_Check_in_Time__c'];
        if ( $user->hasRole('PT') )
            array_push($fieldsSelect, "Remaining_Session__c");
        $contractData = Contract::leftJoin('salesforce_Contract_Sale__c', 'salesforce_Contract_Sale__c.Contract__c', '=', 'salesforce_Contract.Id')
                        ->leftJoin('salesforce_Account', 'salesforce_Account.Id', '=', 'salesforce_Contract.AccountId')
            ->with(['Account' => function($qr){
                $qr->select(['Id', 'AccountNumber', 'LastName', 'Name', 'PhotoUrl', 'Salutation', 'MiddleName', 'Suffix', 'Type', 'Phone', 'PersonMobilePhone', 'FirstName', 'Acc_Code__c',
                'Code__c']);
            }])
            ->select($fieldsSelect)
            ->where('salesforce_Contract.IsDeleted', 0)
            ->where('salesforce_Contract_Sale__c.Sales__c', $user->sf_account_id());

        //        if ( isset( $input['debug'] ) ) {
        //            $fieldsSelect = ['salesforce_Contract.Id', 'AccountId', 'Contract_Number_Searchable__c','Sale_Note__c'];
        //
        //            $contractData1 = Contract::leftJoin('salesforce_Contract_Sale__c', 'salesforce_Contract_Sale__c.Contract__c', '=', 'salesforce_Contract.Id')
        //                ->leftJoin('salesforce_Account', 'salesforce_Account.Id', '=', 'salesforce_Contract.AccountId')
        //
        //                ->select($fieldsSelect)
        //                ->where('salesforce_Contract.IsDeleted', 0)
        //                ->where('salesforce_Contract_Sale__c.Sales__c', $user->sf_account_id());
        //            $rs = $contractData1->get();
        //            dd($rs);
        //        }

        if ( isset($input['type']) && $input['type'] == 'account_number' ) {
            if ( isset($input['search_text']) && !empty($input['search_text']) ) {
                $search = $input['search_text'];
                $contractData->where('salesforce_Account.Code__c', 'like', "%{$search}%");
            }
        }
        if ( isset($input['type']) && $input['type'] == 'contract_code' ) {
            if ( isset($input['search_text']) && !empty($input['search_text']) ) {
                $search = $input['search_text'];
                $contractData->where('Contract_Number__c', 'like', "%{$search}%");
            }
        }

        if ( isset($input['type']) && $input['type'] == 'contract_enddate' && isset($input['endDateStart']) && !empty($input['endDateStart']) && isset($input['endDateEnd']) && !empty($input['endDateEnd'])) {
            $filterStatus = ['Terminated', 'Cancelled By DS', 'Cancelled', 'Deactivated', 'Draft'];
            $endDate1 = Carbon::createFromFormat('m/d/Y H:i:s', $input['endDateStart'].' 00:00:00');
            $endDate2 = Carbon::createFromFormat('m/d/Y H:i:s', $input['endDateEnd'].' 23:59:59');
            $contractData->whereNotIn('Status', $filterStatus);
            $contractData->where(function ($query) use ($endDate1, $endDate2){
                $query->where('EndDate', '>=', $endDate1->format('Y-m-d')."T00:00:00Z" )
                    ->where('EndDate', '<=', $endDate2->format('Y-m-d')."T23:59:59Z" );
            });
        }

        if ( isset($input['type']) && $input['type'] == 'workout_history') {
            $contractData->where('Status', 'Activated');
            if ( $user->hasRole('PT') )
                $contractData->where('Remaining_Session__c', '>' ,0);
            
            if( isset($input['lastCheckin']) && !empty($input['lastCheckin'])){
                $dayLastChekin = $input['lastCheckin'];
                // $contractData->where(function ($query) use ($dayLastChekin){
                //     $query->where('Days_Since_Last_Checkin__c', '>=', $dayLastChekin );
                // });
                $contractData->where('Days_Since_Last_Checkin__c', '>=', $dayLastChekin);
            } else {
                $contractData->where('Days_Since_Last_Checkin__c', '>', 0);
            }
        }

        if ( isset($input['startDate']) && !empty($input['startDate']) && isset($input['endDate']) && !empty($input['endDate'])) {
            $startDate = Carbon::createFromFormat('m/d/Y H:i:s', $input['startDate'].' 00:00:00');
            $endDate = Carbon::createFromFormat('m/d/Y H:i:s', $input['endDate'].' 23:59:59');
            $contractData->where(function ($query) use ($startDate, $endDate){
                $query->where('salesforce_Contract.CreatedDate', '>=', $startDate->format('Y-m-d')."T00:00:00Z" )
                    ->where('salesforce_Contract.CreatedDate', '<=', $endDate->format('Y-m-d')."T23:59:59Z" );
            });
        }

        try { //if ( isset( $input['debug'] ) ) dd(DB::getQueryLog());
            $rs = $contractData->orderBy('salesforce_Contract.LastModifiedDate', 'DESC')->get(); //dd(DB::getQueryLog());
            return response()->json($rs, 200);
        } catch (Exception $e) {
            return response()->json('', 200);
        }
    }

    public function addSale(Request $request) {
        $user = auth()->user();
        //Son Add Role Sale,Pt
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);        
        $postData = $request->post();

        $validator = \Validator::make($postData, [
            'Contract__c' => 'required',
            'Sales__c' => 'required',
        ],
            [
                'Contract__c.required' => 'Vui lòng nhập Contract Id',
                'Sales__c.required' => 'Vui lòng nhập Sale Id',
            ]
        );
        if ($validator->fails())
        {
            return response()->json([
                'message' => implode(",",$validator->messages()->all())
            ], 422);
        }

        try {
            $response = SObject::create('Contract_Sale__c', $postData);
            if ( isset($response->status_code) && $response->status_code == 400 ) {  //dd($response);
                return response()->json(['message' => $response->message, 'data' => $postData], $response->status_code);
            }
            $newItem = new ContractSale();
            $newItem->fill($postData);
            $newItem->Id = $response;
            $newItem->save();
            //log
            activity('sale_pt_app')
                ->causedBy($newItem)
                ->withProperties(['Contract_Sale' => $newItem->toArray()])
                ->log('Contract_Sale| create new Contract Sale Item  #'.$response);

            return response()->json([
                'message' => 'Add Sale Success!'
            ], 200);
        }catch ( \Exception $e) {
            return response()->json($e->getMessage(), 422);
        }
    }
}
