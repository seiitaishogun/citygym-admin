<?php
/**
 * @author tmtuan
 * created Date: 25-Nov-20
 */

namespace App\Domains\Crm\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Pricebook2 extends Model {
    protected $table = 'salesforce_Pricebook2';
    protected $primaryKey = 'Id';
    protected $casts = ['Id' => 'string'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Id', 'IsDeleted',   'Name',    'RecordTypeId',    'CreatedDate', 'CreatedById', 'LastModifiedDate',    'LastModifiedById',
        'SystemModstamp',  'LastViewedDate',  'LastReferencedDate',  'IsActive',    'IsArchived',  'Description', 'IsStandard',
        'From__c', 'To__c',   'Remark__c',   'From_Quantity__c',    'To_Quantity__c',  'Volatility_Rate__c',  'Decision_Code__c',
        'Pricebook_ID__c', 'Is_Public__c'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public $timestamps = false;

    public function item()
    {
        return $this->hasMany(PricebookEntry::class, 'Pricebook2Id', 'Id' );
    }

    public function recordType()
    {
        return $this->belongsTo(RecordType::class, 'RecordTypeId', 'Id' );
    }
}
