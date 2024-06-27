<?php
/**
 * @author tmtuan
 * created Date: 25-Nov-20
 */

namespace App\Domains\Crm\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model {
    protected $table = 'salesforce_Promotion__c';
    protected $primaryKey = 'Id';
    protected $casts = ['Id' => 'string'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Id', 'OwnerId', 'IsDeleted',   'Name',    'CreatedDate', 'CreatedById', 'RecordTypeId', 'LastModifiedDate',    'LastModifiedById',    'SystemModstamp',
        'LastViewedDate',  'LastReferencedDate',  'Code__c',   'Promotion_Quantity_Target__c',    'Promotion_Amount_Target__c',  'Decision_Code__c',
        'Valid_From__c',   'Valid_To__c', 'Is_Registered__c',    'Success_Registered_Date__c',  'Remark__c',   'Status__c',   'Product__c',  'Promotion_Optional_Quantity__c',
        'Applied_To__c', 'Term_And_Condition__c'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public $timestamps = false;

    public function club()
    {
        return $this->hasMany(ClubAppliedPromotions::class, 'Promotion__c', 'Id' );
    }

    public function promotion_item()
    {
        return $this->hasMany(PromotionItem::class, 'Promotion__c', 'Id' );
    }
}
