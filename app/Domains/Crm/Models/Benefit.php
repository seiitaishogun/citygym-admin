<?php
/**
 * @author tmtuan
 * created Date: 25-Nov-20
 */

namespace App\Domains\Crm\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Benefit extends Model {
    protected $table = 'salesforce_Benefit__c';
    protected $primaryKey = 'Id';
    protected $casts = ['Id' => 'string'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Id', 'OwnerId', 'IsDeleted',   'Name',    'CreatedDate', 'CreatedById', 'LastModifiedDate',    'LastModifiedById',    'SystemModstamp',  'LastViewedDate',
        'LastReferencedDate',  'Benefit_Code__c', 'Benefit_Type__c', 'Quantity__c', 'Guest_Type__c',   'Same_Time_Checkin__c',    'Per__c',  'Cost__c', 'Display_In_Checkin__c',
        'Gift__c', 'Product__c',  'Default__c',  'Optional__c', 'Is_Rented__c','Description_c'
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
        return $this->belongsTo(ProductBenefit::class, 'Id');
    }
}
