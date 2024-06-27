<?php
/**
 * @author tmtuan
 * created Date: 25-Nov-20
 */

namespace App\Domains\Crm\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PricebookEntry extends Model {
    protected $table = 'salesforce_PricebookEntry';
    protected $primaryKey = 'Id';
    protected $casts = ['Id' => 'string'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Id', 'Name',    'Pricebook2Id',    'Product2Id',  'UnitPrice' ,  'IsActive',    'UseStandardPrice',    'CreatedDate', 'CreatedById', 'LastModifiedDate',
        'LastModifiedById',    'SystemModstamp',  'ProductCode', 'IsDeleted',   'IsArchived',  'Pricebook_Entry_Name__c', 'From_Quantity__c',    'To_Quantity__c',
        'Volatility_Rate__c',  'Product_Name__c', 'Query', 'results', 'operations'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public $timestamps = false;

    public static function getPricebook($page = 1,$quantity = null,$club_id = null){
        // if (!$account_id) {
        //     return [];
        // }
        // $page = 1;
        $limit = 100;

        $selectColumns = [
            'pbe.Id Pricebook_entry_id',
            'pbe.Name',
            'pbe.Pricebook2Id',
            'pbe.Product2Id',
            'pbe.UnitPrice',
            'pbe.ProductCode',
            'pbe.Pricebook_Entry_Name__c',
            'pbe.From_Quantity__c',
            'pbe.To_Quantity__c',
            'pbe.Volatility_Rate__c',
            'pbe.Product_Name__c',
            'pbe.Product_Name__c',

            // 'pb2.Name pricebook_name',
            // 'pb2.Name From__c',
            // 'pb2.Name To__c',
            // 'pb2.Name Remark__c',
            // 'pb2.Name From_Quantity__c',

            'p2.Name AS product_name',
        ];
        $sql = 'SELECT ' . implode(',', $selectColumns) . ' ';

        $sql .= 'FROM salesforce_PricebookEntry pbe ';
        $sql .= 'LEFT JOIN salesforce_Product2 p2 ON (pbe.Product2Id = p2.Id) ';
        $sql .= 'LEFT JOIN salesforce_Pricebook2 pb2 ON (pbe.Pricebook2Id = pb2.Id) ';
        if ($club_id) {
            $sql .= 'LEFT JOIN salesforce_Club_Applied_Pricebook__c cap ON (pb2.Id = cap.Price_Book__c) ';
        }

        $condition = "WHERE ";
        if ($quantity) {
            $sql .= $condition.'pbe.From_Quantity__c <= "'.$quantity.'" ';
            $condition = "AND ";
            $sql .= $condition.'pbe.To_Quantity__c >= "'.$quantity.'" ';
        }

        if ($club_id) {
            $sql .= $condition.'cap.Club__c = "'.$club_id.'" ';
        }
        $sql .= $condition.'pbe.IsDeleted = 1 ';
        $sql .= 'ORDER BY pbe.CreatedDate DESC ';
        $sql .= 'LIMIT ' . ((int) $limit * ((int) $page - 1)) . ', ' . (int) $limit;

        $rs = DB::select($sql);


        return $rs;
    }

}
