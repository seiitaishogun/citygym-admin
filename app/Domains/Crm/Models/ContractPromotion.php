<?php
/**
 * @author tmtuan
 * created Date: 25-Nov-20
 */

namespace App\Domains\Crm\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ContractPromotion extends Model {
    protected $table = 'salesforce_Contract_Promotion__c';
    protected $primaryKey = 'Id';
    protected $casts = ['Id' => 'string'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Id', 'OwnerId', 'IsDeleted',   'Name',    'RecordTypeId',    'CreatedDate', 'CreatedById', 'LastModifiedDate',    'LastModifiedById',
        'SystemModstamp',  'Contract__c', 'Saleman__c',  'Promotion_Item__c',   'Amount__c',   'Discount__c', 'Can_Print__c',    'Applied_To__c',
        'Cost_Of_FOC__c',  'Cost_Of_Gift__c', 'FOC_Product__c',  'Gift__c', 'Special_Price__c',    'Voucher__c',  'Opportunity_Line_Promotion__c',
        'Valid_From__c',   'Valid_To__c', 'FOC_Quantity__c', 'Quantity__c'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public $timestamps = false;

}
