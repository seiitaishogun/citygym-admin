<?php
/**
 * @author tmtuan
 * created Date: 25-Nov-20
 */

namespace App\Domains\Crm\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;


class SfAcccount extends Model {
    protected $table = 'salesforce_Account';
    protected $primaryKey = 'Id';
    protected $casts = ['Id' => 'string'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Id', 'IsDeleted',  'MasterRecordId',  'Name',    'LastName',    'FirstName',   'Salutation',  'MiddleName',  'Suffix',  'Type',    'RecordTypeId',    'ParentId',
        'BillingStreet',   'BillingCity', 'BillingState',    'BillingPostalCode',   'BillingCountry',  'BillingLatitude', 'BillingLongitude',    'BillingGeocodeAccuracy',
        'BillingAddress',  'ShippingStreet',  'ShippingCity',    'ShippingState',   'ShippingPostalCode',  'ShippingCountry', 'ShippingLatitude',    'ShippingLongitude',
        'ShippingGeocodeAccuracy', 'ShippingAddress', 'Phone',   'AccountNumber',   'Website', 'PhotoUrl',    'Industry' ,   'NumberOfEmployees',   'Description',
        'OwnerId', 'CreatedDate', 'CreatedById', 'LastModifiedDate',    'LastModifiedById',    'SystemModstamp',  'LastActivityDate',    'LastViewedDate',
        'LastReferencedDate',  'PersonContactId', 'IsPersonAccount', 'PersonMailingStreet', 'PersonMailingCity',   'PersonMailingState',  'PersonMailingPostalCode',
        'PersonMailingCountry',    'PersonMailingLatitude',   'PersonMailingLongitude',  'PersonMailingGeocodeAccuracy',    'PersonMailingAddress',    'PersonMobilePhone',
        'PersonEmail', 'PersonTitle', 'PersonDepartment',    'PersonLastCURequestDate', 'PersonLastCUUpdateDate',  'PersonEmailBouncedReason',    'PersonEmailBouncedDate',
        'Jigsaw',  'JigsawCompanyId', 'AccountSource',   'SicDesc', 'Asset__c',    'StaffCode__c',    'DOB__c',  'Billing_Address_Ward__c', 'Shipping_Address_Ward__c',
        'Person_Mailing_Address_Ward__c',  'Person_Other_Address_Ward__c',    'Billing_Address_Full__c', 'Shipping_Address_Full__c',    'Person_Mailing_Address_Full__c',
        'Person_Other_Address_Full__c',    'Club__c', 'Job_Title__c',    'Partner_Code__c', 'Active__c',   'Gender__c',   'ParentID__c', 'Photo__c',    'Company_Name__c',
        'Age__c',  'Passport_No__c',  'Pref_Mobile__c',  'Title__c',    'Emergency_Contact_s_Name__c', 'Emergenct_Phone__c',  'Blacklist__c',    'Reason_Of_Blacklist__c',
        'Password__c', 'Source__c',   'Alert__c',    'Source_Name__c',  'TaxCode__c',  'Email__c',    'AccountId__c',  'Account_Type__c',  'Marriage__c', 'IsChild__c',  'ChildAge__c', 'IsVisit__c',
        'VisitDescription__c', 'IsPracticed__c',  'NonPracDescription__c',   'Refer__c',    'IsInjured__c',    'SpendOnSleep__c', 'SpendOnExercise__c',  'AlcoholUsage__c', 'IsSmoking__c',
        'IsCafein__c', 'IsUnhealthy__c',  'Drink__c',    'StressRate__c',   'ExcHabit__c', 'ExcTime__c',  'IsGuestPrivilledge__c',   'MostTarget__c',   'MostTargetOther__c',  'Reason__c',
        'Confuse__c',  'Importants__c',   'Expect__c',   'Ready__c',    'GFP_Created_Date__c', 'IsAppt__c',   'GFP_Created_By__c',   'Last_Login_Date__c',  'Shipping_Country__c',
        'Shipping_Province__c',    'MitekId__c',  'Allocation_Number__c',    'Last_Allocation_Number__c',   'Distribution_Order__c',   'Shipping_Street__c',  'Last_Distribution_Count__c',
        'Last_Assignee_Index__c',  'Shipping_Address_Full_Custom__c', 'Last_Assignee_Id__c', 'Shipping_District__c',    'Billing_Country__c',  'Billing_Province__c', 'Billing_District__c',
        'Billing_Street__c',   'Billing_Address_Full_Custom__c',  'Account_Record_Type_Conversion__c',   'Mitek_Log__c',    'ID_Card__c',  'MS_did_T_C__c',   'DS_s_confirmation__c',
        'Relationship__pc',    'Relationship_Account__pc',    'Country__pc', 'Province__pc',    'Street__pc',  'Ward__pc',    'Address_Full__pc',    'District__pc',
        'Code__c', 'Acc_Code__c', 'App_Username__c', 'Photo_Url__c', 'Nickname__c', 'Favorite_Class__c', 'Favorite_Class_Name__c'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public $timestamps = false;

    public static function findById($id = null) {
        if (isset($id) && !empty($id)) {
            return self::with('favoriteClass:Id,Name')->where('Id',$id)->first();
        }
        return [];
    }

    public function Schedule()
    {
        // return $this->belongsToMany('App\Domains\Crm\Models\Schedule','salesforce_Schedule_HV__c','Schedule__c','Account__c');
        return $this->belongsToMany('App\Domains\Crm\Models\Schedule','salesforce_Schedule_HV__c_ID','Schedule__c','Account__c');
    }

    public function Contract() {
        return $this->hasMany(Contract::class, 'AccountId', 'Id');
    }


}
