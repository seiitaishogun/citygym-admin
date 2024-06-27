<?php
/**
 * @author tmtuan
 * created Date: 25-Nov-20
 */

namespace App\Domains\Crm\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CaseContractTransfer extends Model {
    protected $table = 'salesforce_Case_Contract_Transfer__c';
    protected $primaryKey = 'Id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Id', 'OwnerId',	'IsDeleted',	'Name',	'CreatedDate',	'CreatedById',	'LastModifiedDate',	'LastModifiedById',	'SystemModstamp',	'Case__c',	'Contract__c'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public $timestamps = false;

}
