<?php
/**
 * @author tmtuan
 * created Date: 25-Nov-20
 */

namespace App\Domains\Crm\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Product2 extends Model {
    protected $table = 'salesforce_Product2';
    protected $primaryKey = 'Id';
    protected $casts = ['Id' => 'string'];


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Id', 'Name',  'ProductCode', 'Description', 'IsActive',    'CreatedDate', 'CreatedById', 'LastModifiedDate',    'LastModifiedById',
        'SystemModstamp',  'Family',  'RecordTypeId',    'ExternalDataSourceId',    'ExternalId',  'DisplayUrl',  'QuantityUnitOfMeasure',
        'IsDeleted',   'IsArchived',  'LastViewedDate',  'LastReferencedDate',  'StockKeepingUnit',    'Product__c',  'Membership_Term__c',
        'MBS_Type__c', 'Product_Type__c', 'Period_UoM__c',   'Period__c',   'FeePerPeriod__c', 'Unit_Of_Measure__c',  'Quantity_Of_Unit__c',
        'Card_Issued_Quantity__c', 'Unqualified_Checkin_Number__c',   'Remark__c',   'Is_Issued_Card__c',   'From_Quantity__c',    'To_Quantity__c',
        'Maximum_Session__c',  'Enable__c',   'Monday__c',   'Tuesday__c',  'Wednesday__c',    'Friday__c',   'Saturday__c', 'Sunday__c',   'Thurday__c',
        'Monday_Hour_From__c', 'Monday_Hour_To__c',   'Monday_Minutes_From__c',  'Monday_Minutes_To__c',    'Tuesday_Hour_From__c',    'Tuesday_Hour_To__c',
        'Tuesday_Minutes_From__c', 'Tuesday_Minutes_To__c',   'Wednesday_Hour_From__c',  'Wednesday_Minutes_From__c',   'Wednesday_Hour_To__c',
        'Wednesday_Minutes_To__c', 'Thurday_Hour_From__c',    'Thurday_Hour_To__c',  'Thurday_Minutes_From__c', 'Thurday_Minutes_To__c',   'Friday_Hour_From__c',
        'Friday_Hour_To__c',   'Friday_Minutes_From__c',  'Friday_Minutes_To__c',    'Saturday_Hour_From__c',   'Saturday_Hour_To__c', 'Saturday_Minutes_From__c',
        'Saturday_Minutes_To__c',  'Sunday_Hour_From__c', 'Sunday_Hour_To__c',   'Sunday_Minutes_From__c',  'Sunday_Minutes_To__c',    'Cost_Of_FOC__c',
        'Stafflist_Quantity__c',   'Benefit_Option_Quantity__c',  'IsFOC__c',    'Service_Type__c', 'Session_Duration_Minute__c',  'Gift_Optional_Quantity__c',
        'Club_Access__c',  'Club_Optional_Quantity__c',   'Is_Rented__c',    'Quantity__c', 'Stafflist_Required__c', 'Booking_Time__c',
        'Guest_Booking_Time__c'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public $timestamps = false;

    public function benefit()
    {
        return $this->hasMany(ProductBenefit::class, 'Product__c', 'Id' );
    }

    public function promotion()
    {
        return $this->hasMany(ProductPromotion::class, 'Product__c', 'Id' );
    }

    public function gift()
    {
        return $this->hasMany(ProductGift::class, 'Product__c', 'Id' );
    }

    public function recordType()
    {
        return $this->belongsTo(RecordType::class, 'RecordTypeId', 'Id' );
    }
}
