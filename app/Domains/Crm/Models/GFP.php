<?php
/**
 * @author tmtuan
 * created Date: 25-Nov-20
 */

namespace App\Domains\Crm\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class GFP extends Model {
    protected $table = 'salesforce_GFP__c';
    protected $primaryKey = 'Id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Id', 'OwnerId', 'IsDeleted',  'Name',    'CreatedDate', 'CreatedById', 'LastModifiedDate',    'LastModifiedById',    'SystemModstamp',  'IsAppt__c',   'Account__c',  'Full_Name__c',
        'Age__c',  'Gender__c',   'Marriage__c', 'IsChild__c',  'ChildAge__c', 'Phone__c' ,   'Id__c',   'Email__c',    'Address__c',  'IsVisit__c',  'Visit_Description__c',    'IsPracticed__c',
        'NonPracDescription__c',   'Refer__c',    'IsInjured__c',    'SpendOnSleep__c', 'SpendOnExercise__c',  'AlcoholUsage__c', 'IsSmoking__c',    'IsCafein__c', 'IsUnhealthy__c',  'Drink__c',
        'ExcHabit__c', 'IsGuestPrivilledge__c',   'MostTarget__c',   'MostTargetOther__c',  'Reason__c',   'Confuse__c',  'Importants__c',   'Expect__c',   'Ready__c'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
    public $timestamps = false;
}
