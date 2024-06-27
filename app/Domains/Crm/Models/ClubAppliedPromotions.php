<?php
/**
 * @author tmtuan
 * created Date: 25-Nov-20
 */

namespace App\Domains\Crm\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ClubAppliedPromotions extends Model {
    protected $table = 'salesforce_Club_Applied_Promotions__c';
    protected $primaryKey = 'Id';
    protected $casts = ['Id' => 'string'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Id', 'OwnerId', 'IsDeleted',   'Name',    'CreatedDate', 'CreatedById', 'LastModifiedDate',    'LastModifiedById',    'SystemModstamp',
        'Club__c', 'Promotion__c',    'Promotion_Name__c',   'Promotion_Optional_Quantity__c',  'Valid_From__c',   'Valid_To__c', 'Club_Name__c'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public $timestamps = false;

    public function promotions()
    {
        return $this->hasMany(Promotion::class, 'Id', 'Promotion__c');
    }
}
