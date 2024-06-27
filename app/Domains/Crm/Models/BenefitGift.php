<?php
/**
 * @author tmtuan
 * created Date: 25-Nov-20
 */

namespace App\Domains\Crm\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class BenefitGift extends Model {
    protected $table = 'salesforce_Benefit_Gift__c';
    protected $primaryKey = 'Id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    'OwnerId', 
    'IsDeleted',   
    'Name',    
    'CreatedDate', 
    'CreatedById', 
    'LastModifiedDate',    
    'LastModifiedById',    
    'SystemModstamp',  
    'Gift__c'    
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

}
