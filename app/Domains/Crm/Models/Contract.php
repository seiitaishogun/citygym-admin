<?php
/**
 * @author tmtuan
 * created Date: 25-Nov-20
 */

namespace App\Domains\Crm\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model {
    protected $table = 'salesforce_Contract';
    protected $primaryKey = 'Id';
    protected $casts = ['Id' => 'string', 'Contract_Signed__c' => 'boolean'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Id', 'AccountId',   'Pricebook2Id',    'OwnerExpirationNotice',   'StartDate',   'EndDate', 'BillingStreet',   'BillingCity',
        'BillingState',    'BillingPostalCode',   'BillingCountry',  'BillingLatitude', 'BillingLongitude',    'BillingGeocodeAccuracy',
        'BillingAddress',  'ShippingStreet',  'ShippingCity',    'ShippingState',   'ShippingPostalCode',  'ShippingCountry', 'ShippingLatitude',
        'ShippingLongitude',   'ShippingGeocodeAccuracy', 'ShippingAddress', 'ContractTerm',    'OwnerId', 'Status',  'CompanySignedId',
        'CompanySignedDate',   'CustomerSignedId',    'CustomerSignedTitle', 'CustomerSignedDate',  'SpecialTerms',    'ActivatedById',
        'ActivatedDate',   'StatusCode',  'Description', 'RecordTypeId',    'IsDeleted',   'ContractNumber',  'LastApprovedDate',    'CreatedDate',
        'CreatedById', 'LastModifiedDate',    'LastModifiedById',    'SystemModstamp',  'LastActivityDate',    'LastViewedDate',  'LastReferencedDate',
        'Shipping_Country__c', 'Shipping_Province__c',    'Staff_List_Quantity__c',  'Contract_Number_Member__c',   'Contract_Number_PT__c',
        'Contract_Type__c',    'Opportunity__c',  'Parent__c',   'Shipping_Street__c',  'Contract_Original_Start_Date__c', 'Contract_Original_End_Date__c',
        'Print_Limit_Override__c', 'Print_Count__c',  'IsInstallment__c',    'Contract_Amount__c',  'Used_Session__c', 'Period_Amount__c',
        'Contract_Status__c',  'Selected_Product__c', 'Actual_Remaining_Session__c',    'Shipping_Ward__c',    'Shipping_Address_Full__c',
        'Actual_Used_Session__c',  'Remaining_Duration_Check_In__c',  'DS_Confirmed__c', 'Contract_Signed__c',
        'Card_Notice__c',  'IsTransferredContract__c',    'Ignore_Promotion_Expire__c',  'Card_Status__c',  'Sale_Compliance__c',  'CPCC__c',
        'Other_Sale__c',   'MS_Compliance__c',    'Other_MS__c', 'Full_Name__c',    'Member_Address__c',   'Issued_for__c',   'Account_RecordType__c',
        'Expired_Date_Old__c', 'Tax_Code__c', 'Issued_Or_Not__c',    'Reopen_Date_Old__c',  'Reopen_Date_New__c',  'Bypass_Apex__c',
        'Total_Payment_Status__c', 'PT_Assign__c',  'Staff_Account__c',  'Contract_Code__c',    'Stafflist_Type__c',   'Issued_Card_Quantity__c', 'Payment_Object__c',
        'Transfer_Amount__c',  'Splitted_Status__c' , 'Home_Club__c',    'Company_Name__c', 'Billing_Country__c',  'Billing_Province__c',
        'Billing_Ward__c', 'Billing_Street__c',   'Billing_Address_Full__c', 'Billing_District__c', 'Shipping_District__c',    'Promotion_Amount__c',
        'Actual_Amount__c',    'Paid_Amount__c',  'Account_Receivable__c',   'Remaining_Duration_Days__c',  'Total_Check_In__c',   'Total_Duration_Days__c',
        'Cash_Voucher_Amount__c',  'Price_Per_Duration__c',   'Remaining_Amount__c', 'Unit_Of_Measure__c',  'Original_Start_Date_Set__c',  'Original_End_Date_Set__c',
        'DiscountAmount__c' ,  'First_Card_Issued_Date__c',   'Transferred_Amount__c', 'Remaining_Duration_Formatted__c', 'Selected_Product_Name', 'Is_ID_Provided__c',
        'Contract_Number__c', 'Contract_Number_Searchable__c', 'Name', 'Sale_Note__c', 'Product_Type__c', 'Product_Name__c','Total_Session_Auto__c','Actual_Used_Session__c','Remaining_Session__c','Used_Session_From_Import__c','Days_Since_Last_Burnshow__c', 'Days_Since_Last_Checkin__c', 'Last_Check_in_Time__c'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public $timestamps = false;

    /**
     * @var array
     */
//    protected $appends = [
//        'product_name_view',
//    ];
//
//    public function getProductNameView() {
//        return str_replace('_',' ', $this->Selected_Product_Name);
//    }

    public function Account() {
        return $this->belongsTo(SfAcccount::class,'AccountId','Id');
    }

    public function Benefit() {
        return $this->hasMany(ContractBenefit::class, 'Contract__c', 'Id');
    }

    public function ContractProduct() {
        return $this->hasMany(ContractProduct::class, 'Contract__c', 'Id');
    }

    public function Club() {
        return $this->hasMany(ContractClub::class, 'Contract__c', 'Id');
    }

    public function Gift() {
        return $this->hasMany(ContractGift::class, 'Contract__c', 'Id');
    }

    public function Product() {
        return $this->hasMany(ContractProduct::class, 'Contract__c', 'Id');
    }

    public function Promotion() {
        return $this->hasMany(ContractPromotion::class, 'Contract__c', 'Id');
    }

}
