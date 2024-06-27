<?php
/**
 * @author tmtuan
 * created Date: 25-Nov-20
 */

namespace App\Domains\Crm\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OpportunityLinePromotion extends Model
{
    protected $table = 'salesforce_Opportunity_Line_Promotion__c';
    protected $primaryKey = 'Id';
    protected $casts = ['Id' => 'string'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Id', 'OwnerId', 'IsDeleted', 'Name', 'RecordTypeId', 'CreatedDate', 'CreatedById', 'LastModifiedDate', 'LastModifiedById', 'SystemModstamp',
        'FOC_Product__c', 'Opportunity__c', 'isApply__c', 'Promotion_Item__c', 'Amount__c', 'Value__c', 'Discount__c', 'Applied_To__c', 'Cost_Of_FOC__c',
        'Gift__c', 'Voucher__c', 'Cost_Of_Gift__c', 'FOC_Quantity__c', 'Quantity__c', 'Special_Price__c', 'Valid_From__c', 'Valid_To__c', 'Record_Type_Name__c'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public $timestamps = false;

    public function PromotionItem()
    {
        return $this->hasOne('App\Domains\Crm\Models\PromotionItem', 'Id', 'Promotion_Item__c');
    }


    public function Product()
    {
        return $this->hasOne('App\Domains\Crm\Models\Product2', 'Id', 'FOC_Product__c');
    }

    public function Opportunity()
    {
        return $this->hasOne('App\Domains\Crm\Models\Opportunity', 'Id', 'Opportunity__c');
    }

    public function Gift()
    {
        return $this->hasOne('App\Domains\Crm\Models\Gift', 'Id', 'Gift__c');
    }

    /**
     * get promotion item by opty ID
     */
    public static function getByOpptyId($oppty_id = null, $record_type_name = '' ){
        if (!$oppty_id) {
            return [];
        }
        $page = 1;
        $limit = 100;

        $selectColumns = [
            'opli.*',
            'p.Name as P_Name'
        ];
        $sql = 'SELECT ' . implode(',', $selectColumns) . ' ';

        $sql .= 'FROM salesforce_Opportunity_Line_Promotion__c opli ';
        $sql .= 'LEFT JOIN salesforce_Opportunity op ON (opli.Opportunity__c = op.Id) ';
        $sql .= 'LEFT JOIN salesforce_Promotion_Item__c p ON (opli.Promotion_Item__c = p.Id) ';
        $sql .= 'LEFT JOIN salesforce_Gift__c g ON (opli.Gift__c = g.Id) ';
        $sql .= 'WHERE opli.Opportunity__c = "'.$oppty_id.'" ';
        
        /**
         * Nov-26-2021 - add more type to load promotion item
         */
        if ( $record_type_name != 'ref' ) $sql .= 'AND opli.Record_Type_Name__c != "Referral_Buyer" ';
        else $sql .= 'AND opli.Record_Type_Name__c = "Referral_Buyer" ';
        
        $sql .= 'AND opli.IsDeleted = 0 ';

        // $sql .= 'ORDER BY sc.Start__c DESC ';
        $sql .= 'LIMIT ' . ((int) $limit * ((int) $page - 1)) . ', ' . (int) $limit;

        $rs = DB::select($sql);

        return $rs;
    }
}
