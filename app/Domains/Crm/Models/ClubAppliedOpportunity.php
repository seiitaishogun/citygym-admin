<?php
/**
 * @author tmtuan
 * created Date: 09-Mar-21
 */
namespace App\Domains\Crm\Models;

use Illuminate\Database\Eloquent\Model;

class ClubAppliedOpportunity extends Model {
    protected $table = 'salesforce_Club_Applied_Opportunity__c';
    protected $primaryKey = 'Id';
    protected $casts = ['Id' => 'string'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Id', 'OwnerId', 'IsDeleted', 'Name', 'CreatedDate', 'CreatedById', 'LastModifiedDate', 'LastModifiedById', 'SystemModstamp',  'Club_Name__c', 'Club__c',  'Opportunity__c'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public $timestamps = false;

    public function ClubInfo()
    {
        return $this->belongsTo(Club::class,'Club__c','Id');
    }
}
