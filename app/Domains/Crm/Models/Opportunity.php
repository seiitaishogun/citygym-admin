<?php
/**
 * @author tmtuan
 * created Date: 25-Nov-20
 */

namespace App\Domains\Crm\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Opportunity extends Model {
    protected $table = 'salesforce_Opportunity';
    protected $primaryKey = 'Id';
    protected $casts = ['Id' => 'string'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Id', 'IsDeleted',   'AccountId',   'RecordTypeId',    'Name',    'Description', 'StageName',   'Amount',  'Probability', 'CloseDate',
        'Type',    'NextStep',    'LeadSource',  'IsClosed',    'IsWon',   'ForecastCategory',    'ForecastCategoryName',    'CampaignId',  'HasOpportunityLineItem',
        'Pricebook2Id',    'OwnerId', 'CreatedDate', 'CreatedById', 'LastModifiedDate',    'LastModifiedById',    'SystemModstamp',  'LastActivityDate',
        'FiscalQuarter',   'FiscalYear',  'Fiscal',  'ContactId',   'LastViewedDate',  'LastReferencedDate',  'SyncedQuoteId',   'ContractId',  'HasOpenActivity',
        'HasOverdueTask',  'Budget_Confirmed__c', 'Discovery_Completed__c',  'ROI_Analysis_Completed__c',   'Loss_Reason__c',  'Visit_Code__c',
        'Primary_Campaign_Source__c',  'Promotion__c',    'IsAppt__c',   'Full_Name__c',    'Age__c',  'Gender__c',   'Marriage__c', 'IsChild__c',
        'IsInstallment__c',    'ChildAge__c', 'Phone__c',    'ID__c',   'Email__c',    'Address__c',  'IsVisit__c',  'VisitDescription__c', 'IsPracticed__c',
        'NonPracDescription__c',   'Refer__c',    'IsInjured__c',    'SpendOnSleep__c', 'SpendOnExercise__c',  'AlcoholUsage__c', 'IsSmoking__c',
        'IsCafein__c', 'IsUnhealthy__c',  'Drink__c',    'StressRate__c',   'ExcHabit__c', 'ExcTime__c',  'IsGuestPrivilledge__c',   'MostTarget__c',
        'MostTargetOther__c',  'Reason__c',   'Confuse__c',  'Importants__c',   'Expect__c',   'Ready__c',    'Account_RecordType__c',   'Club__c',
        'F_limitation__c', 'Payment_Object__c',   'Lead__c', 'Full_URL__c', 'UTM_Source__c',   'UTM_Medium__c',   'UTM_Campaign__c', 'UTM_Content__c',
        'Channel__c',  'Marketer__c', 'Campaign_Type__c',    'Content_Campaign__c', 'ID_Campaign__c',  'Target__c',   'ID_Ads_Set__c',   'Landing_Page_Code__c',
        'ID_Content__c',   'ID_Media__c', 'ID_Ads__c',   'Previous_URL__c', 'Shipping_Country__c', 'Shipping_Province__c',    'IsPrimary__c',
        'Shipping_Street__c',  'Shipping_Ward__c',    'Shipping_Address_Full__c',    'Billing_Country__c',  'Billing_Province__c', 'PT_assign__c',
        'Billing_Ward__c', 'Billing_Street__c',   'Billing_Address_Full__c', 'Billing_District__c', 'Parent__c',   'Shipping_District__c',
        'Sales_Assign__c', 'IsNoti__c',   'Staff_List_Quantity__c',  'Sales_Assign_Club__c',    'Source__c',   'Source_Name__c',  'Register_Quantity__c',
        'Contract_MB__c', 'Contract_Start_Date__c', 'Club_of_Received_Card__c', 'Referral_Promotion_Book__c', 'Referral_Info__c', 'Favorite_Class__c'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public $timestamps = false;

    public function SalesAssign()
    {
        return $this->hasOne('App\Domains\Crm\Models\SfAcccount','Id','Sales_Assign__c');
    }
    public function OppotunitySales()
    {
        return $this->hasMany('App\Domains\Crm\Models\OppotunitySales','Opportunity__c','Id');
    }

    public function PTAssign()
    {
        return $this->hasOne('App\Domains\Crm\Models\SfAcccount','Id','PT_assign__c');
    }
    public function AccountName()
    {
        return $this->hasOne('App\Domains\Crm\Models\SfAcccount','Id','AccountId');
    }

    public function RecordType()
    {
        return $this->belongsTo('App\Domains\Crm\Models\RecordType','RecordTypeId','Id');
        // return $this->hasOne('App\Domains\Crm\Models\RecordType','Id','RecordTypeId');
    }

    public function PrimaryCampaignSource()
    {
        return $this->hasOne('App\Domains\Crm\Models\Campaign','Id','CampaignId');
    }

    public function Club()
    {
        return $this->hasOne('App\Domains\Crm\Models\Club','Id','Club__c');
    }

    public function ContractTrial()
    {
        return $this->hasOne('App\Domains\Crm\Models\Contract','Id','Contract_Trial__c');
    }

    public function ContractMB()
    {
        return $this->hasOne('App\Domains\Crm\Models\Contract','Id','Contract_MB__c');
    }

    public function Contract()
    {
        return $this->hasOne('App\Domains\Crm\Models\Contract','Id','ContractId');
    }

    public function Lead()
    {
        return $this->hasOne('App\Domains\Crm\Models\Lead','Id','Lead__c');
    }

    public function Parent()
    {
        return $this->hasOne('App\Domains\Crm\Models\Opportunity','Id','Parent__c');
    }

    public function PriceBook()
    {
        return $this->hasOne('App\Domains\Crm\Models\Pricebook2','Id','Pricebook2Id');
    }

    public function PromotionBook()
    {
        return $this->hasOne('App\Domains\Crm\Models\Promotion','Id','Promotion__c');
    }

     public function HasProducts()
     {
         return $this->hasMany(OpportunityLineItem::class,'OpportunityId', 'Id');
     }

    public function Source()
    {
        return $this->hasOne('App\Domains\Crm\Models\Source','Id','Source__c');
    }

    public function HasClubApplied()
    {
        return $this->hasMany(ClubAppliedOpportunity::class,'Opportunity__c', 'Id');
    }

    public function ReferralPromotionBook()
    {
        return $this->hasOne('App\Domains\Crm\Models\Promotion','Id','Referral_Promotion_Book__c');
    }
}
