<?php
/**
 * @author tmtuan
 * created Date: 25-Nov-20
 */

namespace App\Domains\Crm\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ProductBenefit extends Model {
    protected $table = 'salesforce_Product_Benefit__c';
    protected $primaryKey = 'Id';
    protected $casts = ['Id' => 'string'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Id', 'OwnerId', 'IsDeleted',   'Name',    'CreatedDate', 'CreatedById', 'LastModifiedDate',    'LastModifiedById',    'SystemModstamp',  'Benefit__c',  'Product__c',  'Benefit_Type__c', 'Guest_Type__c',   'Quantity__c', 'Per__c',  'Same_Time_Checkin__c',    'Guest_Privilledge_Applied__c',    'Quantity_for_Guest__c',   'Is_Printable__c', 'Allow_Transfer__c'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public $timestamps = false;
    public function benefitA()
    {
        return $this->hasOne(Benefit::class, 'Id', 'Benefit__c');
    }
    public function benefit()
    {
        return $this->belongsTo(Product2::class, 'Id');
    }
}
