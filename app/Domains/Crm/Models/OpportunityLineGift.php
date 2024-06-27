<?php
/**
 * @author tmtuan
 * created Date: 25-Nov-20
 */

namespace App\Domains\Crm\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OpportunityLineGift extends Model {
    protected $table = 'salesforce_Opportunity_Line_Gift__c';
    protected $primaryKey = 'Id';
    protected $casts = ['Id' => 'string'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Id', 'OwnerId', 'IsDeleted',   'Name',    'CreatedDate', 'CreatedById', 'LastModifiedDate',    'LastModifiedById',    'SystemModstamp',  'Value__c',    'Gift__c', 'Product__c',  'Opportunity__c',  'isApply__c',  'Quantity__c'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public $timestamps = false;

    public function Product()
    {
        return $this->hasOne('App\Domains\Crm\Models\Product2','Id','Product__c');
    }

    public function Opportunity()
    {
        return $this->hasOne('App\Domains\Crm\Models\Opportunity','Id','Opportunity__c');
    }

    public function Gift()
    {
        return $this->hasOne('App\Domains\Crm\Models\Gift','Id','Gift__c');
    }

    public static function getByOpptyId($oppty_id = null){
        if (!$oppty_id) {
            return [];
        }        
        $page = 1; 
        $limit = 100;

        $selectColumns = [
            'opli.Id as Id', 'opli.OwnerId as OwnerId', 'opli.IsDeleted as IsDeleted',   'p.Name as Name',    'opli.CreatedDate as CreatedDate', 'opli.CreatedById as CreatedById', 'opli.LastModifiedDate as LastModifiedDate',    'opli.LastModifiedById as LastModifiedById',    'opli.SystemModstamp as SystemModstamp',  'opli.Value__c as Value__c',    'opli.Gift__c as Gift__c', 'opli.Product__c as Product__c',  'opli.Opportunity__c as Opportunity__c',  'opli.isApply__c as isApply__c',  'opli.Quantity__c as Quantity__c','p.Id as G_Id'
        ];
        $sql = 'SELECT ' . implode(',', $selectColumns) . ' ';

        $sql .= 'FROM salesforce_Opportunity_Line_Gift__c opli ';
        $sql .= 'LEFT JOIN salesforce_Opportunity op ON (opli.Opportunity__c = op.Id) ';
        $sql .= 'LEFT JOIN salesforce_Gift__c p ON (opli.Gift__c = p.Id) ';
        $sql .= 'WHERE opli.Opportunity__c = "'.$oppty_id.'" ';
        $sql .= 'AND opli.IsDeleted = 0 ';

        // $sql .= 'ORDER BY sc.Start__c DESC ';
        $sql .= 'LIMIT ' . ((int) $limit * ((int) $page - 1)) . ', ' . (int) $limit;

        $rs = DB::select($sql);

        return $rs;
    }     

}
