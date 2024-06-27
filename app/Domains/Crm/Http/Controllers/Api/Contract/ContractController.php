<?php
/**
 * @author tmtuan
 * created Date: 22-Dec-20
 */

namespace App\Domains\Crm\Http\Controllers\Api\Contract;

use App\Domains\Crm\Models\Contract;
use App\Domains\Crm\Models\ContractBenefit;
use App\Domains\Crm\Models\ContractProduct;
use App\Domains\Crm\Models\Opportunity;
use App\Domains\Crm\Models\SfAcccount;
use App\Domains\TmtSfObject\Classes\SObject;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use App\Services\sfconnect\sfconnect;
use Illuminate\Support\Facades\DB;

class ContractController extends ApiController
{
    public $fieldSelect = [
        'Id',
        'AccountId',
        'StartDate',
        'EndDate',
        'ContractNumber',
        'Status',
        'CreatedDate',
        'Full_Name__c',
        'Sale_Compliance__c',
        'Description'
    ];

    public $selectString = "Id,Name,AccountId,Pricebook2Id,OwnerExpirationNotice,StartDate,EndDate,BillingStreet,BillingCity,
        BillingState,BillingPostalCode,BillingCountry,BillingLatitude,BillingLongitude,BillingGeocodeAccuracy,
        BillingAddress,ShippingStreet,ShippingCity,ShippingState,ShippingPostalCode,ShippingCountry,ShippingLatitude,
        ShippingLongitude,ShippingGeocodeAccuracy,ShippingAddress,ContractTerm,OwnerId,Status,CompanySignedId,
        CompanySignedDate,CustomerSignedId,CustomerSignedTitle,CustomerSignedDate,SpecialTerms,ActivatedById,
        ActivatedDate,StatusCode,Description,RecordTypeId,IsDeleted,ContractNumber,LastApprovedDate,CreatedDate,
        CreatedById,LastModifiedDate,LastModifiedById,SystemModstamp,LastActivityDate,LastViewedDate,LastReferencedDate,
        Shipping_Country__c,Shipping_Province__c,Staff_List_Quantity__c,Contract_Number_Member__c,Contract_Number_PT__c,
        Contract_Type__c,Opportunity__c,Parent__c,Shipping_Street__c,Contract_Original_Start_Date__c,Contract_Original_End_Date__c,
        Print_Limit_Override__c,Print_Count__c,IsInstallment__c,Contract_Amount__c,Used_Session__c,Period_Amount__c,
        Contract_Status__c,Selected_Product__c,Shipping_Ward__c,
        Actual_Used_Session__c,Actual_Remaining_Session__c,Remaining_Duration_Check_In__c,DS_Confirmed__c,Contract_Signed__c,
        Card_Notice__c,IsTransferredContract__c,Ignore_Promotion_Expire__c,Card_Status__c,Sale_Compliance__c,CPCC__c,
        Other_Sale__c,MS_Compliance__c,Other_MS__c,Full_Name__c,Member_Address__c,Issued_for__c,
        Expired_Date_Old__c,Tax_Code__c,Issued_Or_Not__c,Reopen_Date_Old__c,Reopen_Date_New__c,Bypass_Apex__c,
        Total_Payment_Status__c,PT_Assign__c,Staff_Account__c,Contract_Code__c,Stafflist_Type__c,Issued_Card_Quantity__c,Payment_Object__c,
        Transfer_Amount__c,Splitted_Status__c,Home_Club__c,Company_Name__c,Billing_Country__c,Billing_Province__c,
        Billing_Ward__c,Billing_Street__c,Billing_Address_Full__c,Billing_District__c,Shipping_District__c,Promotion_Amount__c,
        Actual_Amount__c,Paid_Amount__c,Account_Receivable__c,Remaining_Duration_Days__c,Total_Check_In__c,Total_Duration_Days__c,
        Cash_Voucher_Amount__c,Price_Per_Duration__c,Remaining_Amount__c,Unit_Of_Measure__c,Original_Start_Date_Set__c,Original_End_Date_Set__c,
        DiscountAmount__c,First_Card_Issued_Date__c,Transferred_Amount__c,Contract_Number__c,Total_Session_Auto__c";

// Shipping_Address_Full__c,Account_RecordType__c

    public function listContract(Request $request)
    {
        $user = auth()->user();
        //        $sfconnect = new sfconnect();

        //
        //        $query = "SELECT " . $this->selectString . "
        //         FROM Contract
        //         WHERE AccountId = '" . $user->sf_account_id() . "'";
        //
        //        try {
        //            $rs = $sfconnect->callQuery($query);
        //            return response()->json($rs, 200);
        //        } catch (Exception $e) {
        //            return response()->json('', 200);
        //        }
        //
        //        return response()->json('', 200);
        $input = $request->get('test');
        if (isset($input)) {
            $sfid = $user->sf_account_id();
            $sql = "SELECT Id, Name, AccountId, Account.Id, Account.Name, Account.Code__c,
( select id,name, Benefit_Type__c, Guest_Privilledge_Applied__c, Quantity_for_Guest__c from Contract_Benefits__r)
FROM Contract WHERE Contract.AccountId = '{$sfid}' ";

            $response = SObject::query($sql);
            dd($response, [$sql, $user->sf_account_id()]);
        } else {
            $contracts = Contract::select(['Id', 'StartDate', 'EndDate', 'Status', 'ActivatedDate', 'StatusCode', 'ContractNumber',
                'Contract_Number_PT__c', 'Contract_Amount__c', 'Total_Payment_Status__c',
                'Actual_Amount__c', 'CreatedDate', 'Paid_Amount__c', 'Account_Receivable__c', 'Contract_Number__c', 'Name',
                'Product_Name__c', 'AccountId', 'Product_Type__c', 'Used_Session__c', 'Total_Duration_Days__c',
                'Remaining_Amount__c', 'Remaining_Duration_Days__c',
                'Used_Session_From_Import__c', 'Actual_Used_Session__c', 'Total_Session__c', 'Actual_Remaining_Session__c'])
//                ->with(['Account' => function($qr) {
//                    $qr->select(['Id', 'Name', 'Code__c']);
//                }])
//                ->with(['Benefit' => function($qr) {
//                    $qr->select(['Id', 'Name', 'Quantity__c', 'Benefit_Type__c']);
//                }])
//                ->with(['ContractProduct' => function($qr) {
//                    $qr->select(['Id', 'Name']);
//                }])
                ->where('AccountId', $user->sf_account_id())->where('IsDeleted', 0)->get();

            $contracts = $contracts->toArray();

            for ($i = 0; $i < count($contracts); $i++) {
                $tmp = ContractBenefit::select(['Id', 'Name', 'Quantity__c', 'Benefit_Type__c'])->where('Contract__c', $contracts[$i]['Id'])->get();
                $tmp2 = SfAcccount::select(['Id', 'Name', 'Code__c'])->where('Id', $contracts[$i]['AccountId'])->first();
                $tmp3 = ContractProduct::select(['Id', 'Name'])->where('Contract__c', $contracts[$i]['Id'])->get();
                $contracts[$i]['benefit'] = $tmp->toArray();
                $contracts[$i]['account'] = $tmp2->toArray();
                $contracts[$i]['contract_product'] = $tmp3->toArray();
            }

            if (empty($contracts)) return response()->json(['message' => 'No items Found'], 404);
            else return response()->json($contracts, 200);
        }

    }

    public function submitContract($id, Request $request)
    {
        $user = auth()->user();
        //Son Add Role Sale,Pt
        if (!$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);
        $contract = Contract::where('Id', $id)
            ->where('OwnerId', $user->sf_account_id())
            ->where('IsDeleted', 0)
            ->get()->first();
        if (empty($contract)) return response()->json(['message' => 'No items Found'], 404);

        /**
         * code api goi len SF de convert hdong
         */
        return response()->json(['message' => 'Hợp đồng đang được duyệt. Bạn vui lòng chờ phản hồi từ hệ thống'], 200);
    }

    /**
     * Get contract information by Id from SF
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getContract($id, Request $request)
    {
        $user = auth()->user();
        //Son Add Role Sale,Pt
        if (!$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);

        //         $contract = Contract::with(['Account' => function($md){
        //                                  $md->select(['Id', 'Name', 'LastName', 'FirstName', 'Salutation', 'MiddleName', 'Suffix', 'Type', 'Phone', 'AccountNumber', 'PhotoUrl','Code__c']);
        //                              }])
        // //                             ->with(['Benefit' => function($md){
        // //                                 $md->select(['Id', 'Name', 'Quantity__c', 'Contract__c']);
        // //                             }])
        // //                             ->with(['Gift' => function($md){
        // //                                 $md->select(['Id', 'Name', 'Value__c', 'Is_Apply__c', 'Contract__c']);
        // //                             }])
        // //                             ->with(['Product' => function($md){
        // //                                 $md->select(['Id', 'Name', 'Product_Type__c', 'Quantity__c', 'Club_Access__c', 'Unit_Price__c','Amount__c', 'Product_Name__c', 'Contract__c']);
        // //                             }])
        // //                             ->with(['Promotion' => function($md){
        // //                                 $md->select(['Id', 'Name', 'Amount__c', 'Discount__c', 'Contract__c', 'Voucher__c', 'Valid_From__c', 'Valid_To__c', 'Quantity__c']);
        // //                             }])
        //                              ->find($id);
        //          if ( empty($contract) ) return response()->json(['message' => 'No items Found'], 404);
        //          return response()->json($contract, 200);

        /**********************************************************************************/
        /**
         * old code - query contract direct form SF
         */
        $sfconnect = new sfconnect();
        $query = "SELECT " . $this->selectString . ",Account.Id,Account.Name,Account.LastName,Account.FirstName,Account.Salutation,Account.MiddleName,Account.Suffix,Account.Type,Account.Phone,Account.AccountNumber,Account.PhotoUrl,Opportunity__r.Name,Account.Acc_Code__c,Account.PersonMobilePhone,Account.Code__c";
        // $query = "SELECT " . $selectString ;
        $query .= " FROM Contract ";

        $query .= " WHERE Id = '" . $id . "'";

        try {
            $rs = $sfconnect->callQuery($query);
            return response()->json($rs[0] ?? $rs, 200);
        } catch (Exception $e) {
            return response()->json('', 200);
        }

        return response()->json('', 200);

    }

    /**
     * Get Contract data by $id
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getContractLocal($id, Request $request)
    {
        $user = auth()->user();
        //Son Add Role Sale,Pt
        if (!$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);
        /**********************************************************************************/
        /**
         * new code use object contract from DB
         */
        DB::enableQueryLog();
        $fieldsSelect = ['salesforce_Contract.Id', 'AccountId', 'Account_Receivable__c', 'Actual_Amount__c', 'ContractNumber', 'Contract_Number__c', 'Contract_Signed__c',
            'Contract_Status__c', 'salesforce_Contract.CreatedDate', 'EndDate', 'StartDate', 'salesforce_Contract.Description', 'Full_Name__c', 'Paid_Amount__c', 'Status', 'StatusCode', 'Total_Payment_Status__c',
            'Sale_Compliance__c', 'Opportunity_Name__c', 'Remaining_Amount__c', 'Remaining_Duration_Formatted__c', 'IsInstallment__c', 'Other_Sale__c',
            'Ignore_Promotion_Expire__c', 'Card_Status__c', 'Sale_Compliance__c', 'Contract_Number_Searchable__c', 'Sale_Note__c', 'salesforce_Contract.Actual_Remaining_Session__c', 'salesforce_Contract.Total_Duration_Days__c', 'salesforce_Contract.Remaining_Amount__c', 'salesforce_Contract.Remaining_Duration_Days__c', 'salesforce_Contract.Total_Session_Auto__c', 'salesforce_Contract.Remaining_Session__c', 'salesforce_Contract.Used_Session_From_Import__c'];

        $contract = Contract::with(['Account' => function ($qr) {
            $qr->select(['Id', 'AccountNumber', 'LastName', 'Name', 'PhotoUrl', 'Salutation', 'MiddleName', 'Suffix', 'Type', 'Phone', 'FirstName', 'Acc_Code__c',
                'Code__c']);
        }])
            ->select($fieldsSelect)
            ->where('salesforce_Contract.IsDeleted', 0)->find($id);
        $query = $request->query();

        if (isset($query['debug'])) dd(DB::getQueryLog());

        if (!isset($contract->Id)) return response()->json(['success' => false, 'error' => 'No item found'], 404);
        else {
            return response()->json($contract, 200);
        }
    }

    public static function getSFContract($id)
    {
        $selectString = "Id,Name,AccountId,Pricebook2Id,OwnerExpirationNotice,StartDate,EndDate,BillingStreet,BillingCity,
BillingState,BillingPostalCode,BillingCountry,BillingLatitude,BillingLongitude,BillingGeocodeAccuracy,
BillingAddress,ShippingStreet,ShippingCity,ShippingState,ShippingPostalCode,ShippingCountry,ShippingLatitude,
ShippingLongitude,ShippingGeocodeAccuracy,ShippingAddress,ContractTerm,OwnerId,Status,CompanySignedId,
CompanySignedDate,CustomerSignedId,CustomerSignedTitle,CustomerSignedDate,SpecialTerms,ActivatedById,
ActivatedDate,StatusCode,Description,RecordTypeId,IsDeleted,ContractNumber,LastApprovedDate,CreatedDate,
CreatedById,LastModifiedDate,LastModifiedById,SystemModstamp,LastActivityDate,LastViewedDate,LastReferencedDate,
Shipping_Country__c,Shipping_Province__c,Staff_List_Quantity__c,Contract_Number_Member__c,Contract_Number_PT__c,
Contract_Type__c,Opportunity__c,Parent__c,Shipping_Street__c,Contract_Original_Start_Date__c,Contract_Original_End_Date__c,
Print_Limit_Override__c,Print_Count__c,IsInstallment__c,Contract_Amount__c,Used_Session__c,Period_Amount__c,
Contract_Status__c,Selected_Product__c,Shipping_Ward__c,
Actual_Used_Session__c,Actual_Remaining_Session__c,Remaining_Duration_Check_In__c,DS_Confirmed__c,Contract_Signed__c,
Card_Notice__c,IsTransferredContract__c,Ignore_Promotion_Expire__c,Card_Status__c,Sale_Compliance__c,CPCC__c,
Other_Sale__c,MS_Compliance__c,Other_MS__c,Full_Name__c,Member_Address__c,Issued_for__c,
Expired_Date_Old__c,Tax_Code__c,Issued_Or_Not__c,Reopen_Date_Old__c,Reopen_Date_New__c,Bypass_Apex__c,
Total_Payment_Status__c,PT_Assign__c,Staff_Account__c,Contract_Code__c,Stafflist_Type__c,Issued_Card_Quantity__c,Payment_Object__c,
Transfer_Amount__c,Splitted_Status__c,Home_Club__c,Company_Name__c,Billing_Country__c,Billing_Province__c,
Billing_Ward__c,Billing_Street__c,Billing_Address_Full__c,Billing_District__c,Shipping_District__c,Promotion_Amount__c,
Actual_Amount__c,Paid_Amount__c,Account_Receivable__c,Remaining_Duration_Days__c,Total_Check_In__c,Total_Duration_Days__c,
Cash_Voucher_Amount__c,Price_Per_Duration__c,Remaining_Amount__c,Unit_Of_Measure__c,Original_Start_Date_Set__c,Original_End_Date_Set__c,
DiscountAmount__c,First_Card_Issued_Date__c,Transferred_Amount__c";

        //$opts = Opportunity::where('Status', 'Closed Won')->where('AccountId', $user->sf_account_id)->get();

        $sfconnect = new sfconnect();

//        $query = "SELECT " . $selectString . ", (SELECT Id as CTBenefit, Name, Quantity__c, Contract__c FROM Contract_Benefits__r),
//         (SELECT Id as CTGift, Name, Value__c, Is_Apply__c, Contract__c FROM Contract_Gifts__r),
//         (SELECT Id as CTPromo, Name, Amount__c, Discount__c, Contract__c, Voucher__c, Valid_From__c, Valid_To__c, Quantity__c FROM Contract_Promotions__r),
//         (SELECT Id as CTPdt, Name, Product_Type__c, Quantity__c, Club_Access__c, Unit_Price__c,Amount__c, Product_Name__c, Contract__c FROM Contract_Products__r), Account.Name,Account.LastName,Account.FirstName,Account.Salutation,Account.MiddleName,Account.Suffix,Account.Type,Account.Phone,Account.AccountNumber,Account.PhotoUrl";
        $query = "SELECT " . $selectString;
        $query .= " FROM Contract ";
        $query .= " WHERE Id = '" . $id . "'";
        try {
            $rs = $sfconnect->callQuery($query);
            if (isset($rs['success']) && $rs['success'] == false) return [];

            return $rs[0] ?? $rs;
        } catch (Exception $e) {
            return [];
        }
        return [];

    }

    public static function getSFContracts($ids, $debug = false)
    {
        $sfconnect = new sfconnect();

        /**
         * Aug-12-2021 - remove field Staff_List_Quantity__c do trên SF đã remove field [ https://beunik.atlassian.net/browse/CIT-629 ]
         * Aug-13-2-21 - remove field Remaining_Duration_Check_In__c, Stafflist_Type__c, Total_Check_In__c do không sử dụng [ https://beunik.atlassian.net/browse/CIT-629?focusedCommentId=43189 ]
         */
        $selectString = "Id,Name,AccountId,Pricebook2Id,OwnerExpirationNotice,StartDate,EndDate,BillingStreet,BillingCity,
BillingState,BillingPostalCode,BillingCountry,BillingLatitude,BillingLongitude,BillingGeocodeAccuracy,
BillingAddress,ShippingStreet,ShippingCity,ShippingState,ShippingPostalCode,ShippingCountry,ShippingLatitude,
ShippingLongitude,ShippingGeocodeAccuracy,ShippingAddress,ContractTerm,OwnerId,Status,CompanySignedId,
CompanySignedDate,CustomerSignedId,CustomerSignedTitle,CustomerSignedDate,SpecialTerms,ActivatedById,
ActivatedDate,StatusCode,Description,RecordTypeId,IsDeleted,ContractNumber,LastApprovedDate,CreatedDate,
CreatedById,LastModifiedDate,LastModifiedById,SystemModstamp,LastActivityDate,LastViewedDate,LastReferencedDate,
Shipping_Country__c,Shipping_Province__c,Contract_Number_Member__c,Contract_Number_PT__c,
Contract_Type__c,Opportunity__c,Parent__c,Shipping_Street__c,Contract_Original_Start_Date__c,Contract_Original_End_Date__c,
Print_Limit_Override__c,Print_Count__c,IsInstallment__c,Contract_Amount__c,Used_Session__c,Period_Amount__c,
Contract_Status__c,Selected_Product__c,Shipping_Ward__c,
Actual_Used_Session__c,Actual_Remaining_Session__c,DS_Confirmed__c,Contract_Signed__c,
Card_Notice__c,IsTransferredContract__c,Ignore_Promotion_Expire__c,Card_Status__c,Sale_Compliance__c,CPCC__c,
Other_Sale__c,MS_Compliance__c,Other_MS__c,Full_Name__c,Member_Address__c,Issued_for__c,
Expired_Date_Old__c,Tax_Code__c,Issued_Or_Not__c,Reopen_Date_Old__c,Reopen_Date_New__c,Bypass_Apex__c,
Total_Payment_Status__c,PT_Assign__c,Staff_Account__c,Contract_Code__c,Issued_Card_Quantity__c,Payment_Object__c,
Transfer_Amount__c,Splitted_Status__c,Home_Club__c,Company_Name__c,Billing_Country__c,Billing_Province__c,
Billing_Ward__c,Billing_Street__c,Billing_Address_Full__c,Billing_District__c,Shipping_District__c,Promotion_Amount__c,
Actual_Amount__c,Paid_Amount__c,Account_Receivable__c,Remaining_Duration_Days__c,Total_Duration_Days__c,
Cash_Voucher_Amount__c,Price_Per_Duration__c,Remaining_Amount__c,Unit_Of_Measure__c,Original_Start_Date_Set__c,Original_End_Date_Set__c,
DiscountAmount__c,First_Card_Issued_Date__c,Transferred_Amount__c";

        $query = "SELECT " . $selectString . ",Account.Id,Account.Name,Account.LastName,Account.FirstName,Account.Salutation,Account.MiddleName,Account.Suffix,Account.Type,Account.Phone,Account.AccountNumber,Account.PhotoUrl,Opportunity__r.Name";
        $query .= " FROM Contract ";
        $query .= " WHERE Id IN  (" . $ids . ")";

        try {
            $rs = $sfconnect->callQuery($query);
            if ($debug) dd($rs, ['tmt']);
            return $rs;
        } catch (Exception $e) {
            return [];
        }
    }

    public function list_member_no_action(Request $request)
    {
        $user = auth()->user();
        $day = $request->day;

        $member = Contract::select('Status', 'Actual_Remaining_Session__c', 'CreatedDate', 'AccountId', 'Days_Since_Last_Burnshow__c')
            ->with(['Account' => function($qr) {
                $qr->select(['Id', 'Name', 'PersonMobilePhone', 'LastName', 'Code__c', 'PersonEmail', 'Account_Type__c', 'Age__c', 'Favorite_Class_Name__c']);
            }])
            ->where('Days_Since_Last_Burnshow__c', '>=', $day)
            ->whereIn('Status', ['Activated', 'Suspend', 'Expired'])
            ->where('Actual_Remaining_Session__c', '>', 0)
            ->where('PT_Assign__c', $user->sf_account_id())
            ->orderBy('Days_Since_Last_Burnshow__c')
            ->get();

        return response()->json($member);
    }
}
