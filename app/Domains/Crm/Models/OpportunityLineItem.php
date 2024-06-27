<?php
/**
 * @author tmtuan
 * created Date: 25-Nov-20
 */

namespace App\Domains\Crm\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OpportunityLineItem extends Model {
    protected $table = 'salesforce_OpportunityLineItem';
    protected $primaryKey = 'Id';
    protected $casts = ['Id' => 'string'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Id', 'OpportunityId',   'SortOrder',   'PricebookEntryId',    'Product2Id',  'ProductCode', 'Name',    'Quantity',    'TotalPrice',
        'UnitPrice',   'ListPrice',   'ServiceDate', 'Description', 'CreatedDate', 'CreatedById', 'LastModifiedDate',    'LastModifiedById',
        'SystemModstamp',  'IsDeleted',   'Stafflist_Quantity__c'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public $timestamps = false;

    // public function Opportunity()
    // {
    //     return $this->hasOne('App\Domains\Crm\Models\Opportunity','Id','OpportunityId');
    // }

    // public function Product()
    // {
    //     return $this->hasOne('App\Domains\Crm\Models\Product2','Id','Product2Id');
    // }


    public static function getByOpptyId($oppty_id = null){
        if (!$oppty_id) {
            return [];
        }
        $page = 1;
        $limit = 100;

        $selectColumns = [
            'opli.*, p.Name as pd_name, p.ProductCode as pd_code, p.Unit_Of_Measure__c as pd_Unit_Of_Measure__c, p.Club_Optional_Quantity__c as Club_Optional_Quantity__c',
        ];
        $sql = 'SELECT ' . implode(',', $selectColumns) . ' ';

        $sql .= 'FROM salesforce_OpportunityLineItem opli ';
        $sql .= 'LEFT JOIN salesforce_Opportunity op ON (opli.OpportunityId = op.Id) ';
        $sql .= 'LEFT JOIN salesforce_Product2 p ON (opli.Product2Id = p.Id) ';
        $sql .= 'WHERE opli.OpportunityId = "'.$oppty_id.'" ';
        $sql .= 'AND opli.IsDeleted = 0 ';

        // $sql .= 'ORDER BY sc.Start__c DESC ';
        $sql .= 'LIMIT ' . ((int) $limit * ((int) $page - 1)) . ', ' . (int) $limit;

        $rs = DB::select($sql);

        return $rs;
    }

}
