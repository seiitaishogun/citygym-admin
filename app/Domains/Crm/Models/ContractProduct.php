<?php
/**
 * @author tmtuan
 * created Date: 25-Nov-20
 */

namespace App\Domains\Crm\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ContractProduct extends Model {
    protected $table = 'salesforce_Contract_Product__c';
    protected $primaryKey = 'Id';
    protected $casts = ['Id' => 'string'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Id', 'IsDeleted',   'Name',    'CreatedDate', 'CreatedById', 'LastModifiedDate',    'LastModifiedById',    'SystemModstamp',  'Contract__c', 'Product__c',
        'Product_Type__c', 'Quantity__c', 'Club_Access__c',  'Unit_Price__c',   'Amount__c',   'Product_Name__c', 'PriceBookId__c',  'Friday_Hour_From__c' ,'Friday_Hour_To__c',
        'Friday_Minutes_From__c' , 'Friday_Minutes_To__c',    'Friday__c',   'Monday_Hour_From__c', 'Monday_Hour_To__c',   'Monday_Minutes_From__c',  'Monday_Minutes_To__c',
        'Monday__c',   'Saturday_Hour_From__c',   'Saturday_Hour_To__c', 'Saturday_Minutes_From__c',    'Saturday_Minutes_To__c',  'Saturday__c', 'Sunday_Hour_From__c',
        'Sunday_Hour_To__c',   'Sunday_Minutes_From__c',  'Sunday_Minutes_To__c',    'Sunday__c',   'Thurday_Hour_From__c' ,   'Thurday_Hour_To__c',  'Thurday_Minutes_From__c',
        'Thurday_Minutes_To__c' ,  'Thurday__c' , 'Tuesday_Hour_From__c',    'Tuesday_Hour_To__c',  'Tuesday_Minutes_From__c', 'Tuesday_Minutes_To__c',   'Tuesday__c',
        'Wednesday_Hour_From__c',  'Wednesday_Hour_To__c',    'Wednesday_Minutes_From__c',   'Wednesday_Minutes_To__c', 'Wednesday__c',    'Stafflist_Quantity__c',
        'Unit_Of_Measure__c',  'Quantity_Of_Unit__c', 'Period__c',   'Period_UoM__c',   'FeePerPeriod__c', 'Club_Optional_Quantity__c', 'Booking_Time__c',
        'Guest_Booking_Time__c', 'Session_Duration__c'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public $timestamps = false;

    public function productData() {
        return $this->belongsTo(Product2::class, 'Product__c', 'Id');
    }

}
