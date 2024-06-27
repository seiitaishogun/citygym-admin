<?php
/**
 * @author tmtuan
 * created Date: 25-Nov-20
 */

namespace App\Domains\Crm\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model {
    protected $table = 'salesforce_Lead';
    protected $primaryKey = 'Id';
    protected $casts = ['Id' => 'string'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Id', 'IsDeleted', 'MasterRecordId',  'LastName',    'FirstName',   'Salutation',  'MiddleName',  'Suffix',  'Name',
        'Title',   'Company', 'Street',  'City',    'State',   'PostalCode',  'Country', 'Latitude',    'Longitude',
        'GeocodeAccuracy', 'Address', 'Phone',   'MobilePhone', 'Email',   'Website', 'PhotoUrl',    'LeadSource',
        'Status',  'Industry',    'Rating',  'NumberOfEmployees',   'OwnerId', 'IsConverted', 'ConvertedDate',
        'ConvertedAccountId',  'ConvertedContactId',  'ConvertedOpportunityId',  'IsUnreadByOwner', 'CreatedDate',
        'CreatedById', 'LastModifiedDate',    'LastModifiedById',    'SystemModstamp',  'LastActivityDate',
        'LastViewedDate',  'LastReferencedDate',  'Jigsaw',  'JigsawContactId', 'EmailBouncedReason',  'EmailBouncedDate',
        'Gender__c',   'DOB__c',  'Club_Assignment__c',  'Club_Interested__c',  'Tag__c',  'Source__c' ,  'Campaign__c',
        'Reception__c',    'PT_HLV__c',   'CPL__c',  'ID_Ads__c',   'Handle_Time__c',  'Waited_Time__c',  'Note__c', 'Lead_Type__c',
        'Full_Url__c', 'UTM_Source__c',   'UTM_Medium__c',   'UTM_Campaign__c', 'UTM_Content__c',  'Promotion_API__c',
        'Channel__c',  'Marketer__c', 'Campaign_Type__c',    'Content_Campaign__c', 'ID_Campaign__c',  'Target__c',   'ID_Ads_Set__c',
        'Landing_Page_Code__c',    'ID_Content__c',   'ID_Media__c', 'Previous_URL__c', 'Duplicated_Lead__c',  'Enable_Lead_Distribution__c',
        'Last_Club_Distribution_Run_Date__c',  'Run_Club_Distribution__c',    'Is_Duplicate__c', 'Shipping_Country__c', 'Shipping_Province__c',
        'Main_Source__c',  'Run_Team_Leader_Distribution__c', 'Run_Staff_Distribution__c' ,  'Last_Team_Leader_Distribution_Run_Date__c',
        'Last_Staff_Distribution_Run_Date__c', 'Shipping_Street__c',  'Last_Club_Distribution_Run_By__c',    'Last_Team_Leader_Distribution_Run_By__c',
        'Last_Staff_Distribution_Run_By__c',   'Source_Name__c',  'Shipping_Ward__c',    'Shipping_Address_Full__c',    'Team_Leader_Assignment__c',
        'Staff_Assignment__c', 'Billing_Country__c',  'Billing_Province__c', 'MitekId__c',  'Billing_Ward__c', 'Billing_Street__c',
        'Billing_Address_Full__c', 'Billing_District__c', 'Shipping_District__c',    'Mitek_Log__c',    'Last_Withdraw_Run_By__c',
        'Last_Withdraw_Run_Date__c',   'Arrival_Time__c', 'Departure_Time__c',   'UTM_term__c', 'Promotion__c',    'Phone_View__c',
        'Lost_Reason__c', 'Description', 'Created_By_App_User__c', 'Team_Leader_Assignment__c', 'Address_Full__pc', 'Tax_Identification_Number__c'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public $timestamps = false;

    public function StaffAssign() {
        return $this->hasOne(SfAcccount::class, 'Id', 'Staff_Assignment__c');
    }
}
