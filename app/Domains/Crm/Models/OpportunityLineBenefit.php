<?php
/**
 * @author tmtuan
 * created Date: 25-Nov-20
 */

namespace App\Domains\Crm\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OpportunityLineBenefit extends Model {
    protected $table = 'salesforce_Opportunity_Line_Benefit__c';
    protected $primaryKey = 'Id';
    protected $casts = ['Id' => 'string'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Id', 'OwnerId', 'IsDeleted',   'Name',    'CreatedDate', 'CreatedById', 'LastModifiedDate',    'LastModifiedById',    'SystemModstamp',  'Opportunity__c',  'Benefit__c',  'isApply__c',  'Product__c',  'Benefit_Type__c', 'Quantity__c', 'Per__c',  'Gift__c', 'Cost__c', 'Value__c',    'Benefit_Default__c',  'Benefit_Optional__c', 'Benefit_Option_Quantity__c'
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

    public function Benefit()
    {
        return $this->hasOne('App\Domains\Crm\Models\Benefit','Id','Benefit__c');
    }

    public static function getByOpptyId($oppty_id = null){
        if (!$oppty_id) {
            return [];
        }        
        $page = 1; 
        $limit = 100;

        $selectColumns = [
            'opli.*',
            'p.Name as Benefit_name',
            'p.Description_c as Benefit_description',
        ];
        $sql = 'SELECT ' . implode(',', $selectColumns) . ' ';

        $sql .= 'FROM salesforce_Opportunity_Line_Benefit__c opli ';
        $sql .= 'LEFT JOIN salesforce_Opportunity op ON (opli.Opportunity__c = op.Id) ';
        $sql .= 'LEFT JOIN salesforce_Benefit__c p ON (opli.Benefit__c = p.Id) ';
        $sql .= 'WHERE opli.Opportunity__c = "'.$oppty_id.'" ';
        $sql .= 'AND opli.IsDeleted = 0 ';

        // $sql .= 'ORDER BY sc.Start__c DESC ';
        $sql .= 'LIMIT ' . ((int) $limit * ((int) $page - 1)) . ', ' . (int) $limit;

        $rs = DB::select($sql);

        return $rs;
    }     

}
