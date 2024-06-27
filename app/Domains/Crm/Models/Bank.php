<?php
/**
 * @author tmtuan
 * created Date: 25-Nov-20
 */

namespace App\Domains\Crm\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $table = 'salesforce_Bank__c';
    protected $primaryKey = 'Id';
    protected $casts = ['Id' => 'string'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Id',
        'OwnerId',
        'IsDeleted',
        'Name',
        'CreatedDate',
        'CreatedById',
        'LastModifiedDate',
        'LastModifiedById',
        'SystemModstamp',
        'LastViewedDate',
        'LastReferencedDate',
        'Bank_Code__c',
        'Bank_Interest_Installment__c'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
    public $timestamps = false;

}
