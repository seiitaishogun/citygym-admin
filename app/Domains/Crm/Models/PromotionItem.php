<?php
/**
 * @author tmtuan
 * created Date: 25-Nov-20
 */

namespace App\Domains\Crm\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PromotionItem extends Model {
    protected $table = 'salesforce_Promotion_Item__c';
    protected $primaryKey = 'Id';
    protected $casts = ['Id' => 'string'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Id', 'IsDeleted',   'Name',    'RecordTypeId',    'CreatedDate', 'CreatedById', 'LastModifiedDate',    'LastModifiedById',
        'SystemModstamp',  'Code__c',  'Discount__c', 'Applied_To__c',   'Valid_From__c',   'Valid_To__c', 'Remark__c',
        'Amount__c',   'Special_Price__c',    'Quantity__c', 'Gift__c', 'Voucher__c',  'Cost_Of_Gift__c', 'FOC_Product__c',  'FOC_Quantity__c',
        'Promotion__c',    'Cost_Of_FOC__c',  'Record_Type_Name__c', 'Benefit__c',  'Benefit_Quantity__c'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public $timestamps = false;

    public function ProductPromotion()
    {
        return $this->hasMany(ProductPromotion::class, 'Promotion_Item__c', 'Id' );
    }

    public function Benefit() {
        return $this->hasOne(Benefit::class, 'Id', 'Benefit__c');
    }

    public function Promotion() {
        return $this->belongsTo(Promotion::class,  'Promotion__c', 'Id');
    }

    public function Gift() {
        return $this->hasOne(Gift::class, 'Id', 'Gift__c');
    }

}
