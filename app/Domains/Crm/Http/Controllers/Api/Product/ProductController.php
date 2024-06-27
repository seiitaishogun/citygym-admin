<?php
/**
 * @author tmtuan
 * created Date: 01-Dec-20
 */
namespace App\Domains\Crm\Http\Controllers\Api\Product;

use App\Domains\Crm\Models\RecordType;
use App\Http\Controllers\Api\ApiController;
use App\Domains\Crm\Models\Product2;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ProductController extends ApiController {
    private $selectedField = [
        'salesforce_Product2.Id as ProId', 'salesforce_Product2.Name as ProName',  'ProductCode', 'Description', 'IsActive',    'CreatedDate', 'CreatedById', 'LastModifiedDate',    'LastModifiedById',
        'SystemModstamp',  'Family',  'RecordTypeId',    'ExternalDataSourceId',    'ExternalId',  'DisplayUrl',  'QuantityUnitOfMeasure',
        'IsDeleted',   'IsArchived',  'LastViewedDate',  'LastReferencedDate',  'StockKeepingUnit',    'Product__c',  'Membership_Term__c',
        'MBS_Type__c', 'Product_Type__c', 'Period_UoM__c',   'Period__c',   'FeePerPeriod__c', 'Unit_Of_Measure__c',  'Quantity_Of_Unit__c',
        'Card_Issued_Quantity__c', 'Unqualified_Checkin_Number__c',   'Remark__c',   'Is_Issued_Card__c',   'From_Quantity__c',    'To_Quantity__c',
        'Maximum_Session__c',  'Enable__c',   'Monday__c',   'Tuesday__c',  'Wednesday__c',    'Friday__c',   'Saturday__c', 'Sunday__c',   'Thurday__c',
        'Monday_Hour_From__c', 'Monday_Hour_To__c',   'Monday_Minutes_From__c',  'Monday_Minutes_To__c',    'Tuesday_Hour_From__c',    'Tuesday_Hour_To__c',
        'Tuesday_Minutes_From__c', 'Tuesday_Minutes_To__c',   'Wednesday_Hour_From__c',  'Wednesday_Minutes_From__c',   'Wednesday_Hour_To__c',
        'Wednesday_Minutes_To__c', 'Thurday_Hour_From__c',    'Thurday_Hour_To__c',  'Thurday_Minutes_From__c', 'Thurday_Minutes_To__c',   'Friday_Hour_From__c',
        'Friday_Hour_To__c',   'Friday_Minutes_From__c',  'Friday_Minutes_To__c',    'Saturday_Hour_From__c',   'Saturday_Hour_To__c', 'Saturday_Minutes_From__c',
        'Saturday_Minutes_To__c',  'Sunday_Hour_From__c', 'Sunday_Hour_To__c',   'Sunday_Minutes_From__c',  'Sunday_Minutes_To__c',    'Cost_Of_FOC__c',
        'Stafflist_Quantity__c',   'Benefit_Option_Quantity__c',  'IsFOC__c',    'Service_Type__c', 'Session_Duration_Minute__c',  'Gift_Optional_Quantity__c',
        'Club_Access__c',  'Club_Optional_Quantity__c',   'Is_Rented__c',    'Quantity__c', 'Stafflist_Required__c',
    ];

    /**
     * Lấy danh sách các sản phẩm trên App Sale/PT theo RecordType với Sobject=Product2
     * Nếu là Sale: Lấy danh sách các sản phẩm có RecordType= (Product MB, Product Trial)
     * Nếu là PT: lấy danh sách các sản phẩm có RecordType=Product PT
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listProducts(Request $request) {
        $input = $request->query();
        $per_page = $input['per_page'] ?? 15;

        if ( empty($input['type']) ) return response()->json(['message' => 'Invalid Request!'], 404);

        $typeName = [];
        switch ($input['type']) {
            case 'Sale':
                $rcData = RecordType::whereIn('salesforce_Record_Type.DeveloperName', ['Product_MB', 'Product_Trial'])
                    ->get()->toArray();
                foreach ($rcData as $item) {
                    $typeName[] = $item['Id'];
                }
                break;
            case 'PT':
                $rcData = RecordType::whereIn('salesforce_Record_Type.DeveloperName', ['Product_PT'])
                    ->get()->toArray();
                foreach ($rcData as $item) {
                    $typeName[] = $item['Id'];
                }
                break;
        }

        $query = Product2::with(['benefit', 'benefit.benefitA:Id,Name'])
            ->with('promotion')
            ->with('gift')
            ->with(['recordType' => function($qr) {
                $qr->select(['Id', 'Name']);
            }])
            ->where('IsDeleted', 0)
            ->where('IsActive', 1)
            ->whereIn('RecordTypeId', $typeName);

        if( isset($input['date']) && !empty($input['date']) ){
            $startDate = Carbon::createFromFormat('m/d/Y H:i:s', $input['date'].' 00:00:00');

            $query->where('CreatedDate', '>', $startDate )
                ->where('CreatedDate', '<', $startDate->copy()->endOfDay() );
        }

        $data = $query->paginate($per_page);
        if ( $data ) return response()->json($data, 200);
    	else return response()->json(['message' => 'No data found!'], 404);
    }

    public function get($id,Request $request) {
    	$data = Product2::with('benefit')
                    ->with('promotion')
                    ->with('gift')
                    ->with(['recordType' => function($qr) {
                        $qr->select(['Id', 'Name']);
                    }])
                    ->where('IsDeleted', 0)
                    ->where('Id', $id)->first();

    	if ( $data ) return response()->json([
            'data' => $data
        ], 200);
        else return response()->json(['message' => 'No data found!'], 404);
    }

}
