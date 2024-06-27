<?php
/**
 * @author tmtuan
 * created Date: 25-Nov-20
 */

namespace App\Domains\Crm\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SfClass extends Model {
    protected $table = 'salesforce_Class__c';
    protected $primaryKey = 'Id';
    protected $casts = ['Id' => 'string'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Id', 'OwnerId', 'IsDeleted',   'Name',    'CreatedDate', 'CreatedById', 'LastModifiedDate',    'LastModifiedById',   'SystemModstamp',  'LastViewedDate',
        'LastReferencedDate',  'Class_Code__c',   'Class_Group__c', 'Color__c', 'thutu'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public $timestamps = false;

    public function Schedule() {
        return $this->hasMany(Schedule::class, 'Class__c', 'Id');
    }

    public function group() {
        return $this->belongsTo( ClassGroup::class, 'Class_Group__c', 'Id');
    }
}
