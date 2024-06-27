<?php
/**
 * @author tmtuan
 * created Date: 25-Nov-20
 */

namespace App\Domains\Crm\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Club extends Model
{
    protected $table = 'salesforce_Club__c';
    protected $primaryKey = 'Id';
    protected $casts = ['Id' => 'string'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Id', 'OwnerId', 'IsDeleted', 'Name', 'CreatedDate', 'CreatedById', 'LastModifiedDate', 'LastModifiedById', 'SystemModstamp',
        'LastViewedDate', 'LastReferencedDate', 'Club_Address__c', 'Club_Phone__c', 'Email__c', 'Tax_Code__c', 'Company_Name__c',
        'Allocation_Number__c', 'Is_TM__c', 'Is_B2B__c', 'Hide_In_Print_View__c', 'Last_Allocation_Number__c', 'Distribution_Order__c', 'Last_Assignee_Index__c',
        'Last_Distribution_Count__c', 'Last_Assignee_Id__c', 'Product__c'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public $timestamps = false;
}
