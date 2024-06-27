<?php
/**
 * @author tmtuan
 * created Date: 25-Nov-20
 */

namespace App\Domains\Crm\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ProductPromotion extends Model {
    protected $table = 'salesforce_Product_Promotion__c';
    protected $primaryKey = 'Id';
    protected $casts = ['Id' => 'string'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Id', 'OwnerId', 'IsDeleted',   'Name',    'CreatedDate', 'CreatedById', 'LastModifiedDate',    'LastModifiedById',    'SystemModstamp',  'Promotion_Item__c',
        'Product__c',  'Promotion_Record_Type__c',    'Fix_Optional__c', 'Guest_Privilledge_Applied__c',    'Quantity_for_Guest__c',   'Promotion_Item_Name__c',
        'Promotion_item_Quantity__c',  'Query', 'results', 'operations'

    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public $timestamps = false;

    public function Product() {
        return $this->hasOne(Product2::class, 'Id', 'Product__c');
    }

}
