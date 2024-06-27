<?php
/**
 * @author tmtuan
 * created Date: 11-Dec-20
 */
namespace App\Domains\Crm\Http\Controllers\Api\PriceBook;

use App\Domains\Crm\Models\Pricebook2;
use App\Domains\Crm\Models\PricebookEntry;
use App\Domains\Crm\Models\RecordType;
use App\Domains\TmtSfObject\Classes\ApexRest;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\ClientException;

class PriceBookController extends ApiController {

    public function list(Request $request) {
        // $getData = $request->get();
        $page = $request->get('page')?$request->get('page'):1;
        $quantity = $request->get('quantity')?$request->get('quantity'):null;
        $club_id = $request->get('club_id')?$request->get('club_id'):null;
        // var_dump($page);die();
        // $data = Pricebook2::paginate(15);
        $data = PricebookEntry::getPricebook($page,$quantity,$club_id);
        return response()->json([
            'status' => true,
            'data' => $data
        ], 200);
    }

    public function listPricebooks(Request $request) {
        $input = $request->query();
        $per_page = $input['per_page'] ?? 15;

        $query = Pricebook2::with('item')
                ->with(['recordType' => function($qr) {
                    $qr->select(['Id', 'Name']);
                }])
                    ->where('Is_Public__c', 1);

        if ( isset($input['status']) && !empty($input['status']) ) {
            if($input['status'] == '1' || $input['status'] == 1)
                $query->where('IsActive', $input['status']);
            else{
                $query->where(function ($qr){
                    $qr->whereNull('IsActive');
                    $qr->orWhere('IsActive', '0');
                });
            }
        }

        if(isset($input['startDate']) || isset($input['endDate'])){
            $from = empty($input['startDate']) ? date('Y-m-d H:i:s',strtotime($input['endDate'].'last month')) : date('Y-m-d H:i:s',strtotime($input['startDate']));
            $to = empty($input['endDate']) ? date('Y-m-d H:i:s',strtotime($input['startDate'].'next month')) : date('Y-m-d H:i:s',strtotime($input['endDate']));

            $query->where(function ($qr) use ($from, $to){
                $qr->orWhere(function ($qrw) use ($from, $to){
                    $qrw->where('From__c', '>=',$from);
                    $qrw->where('To__c','<=', $to);
                });

                $qr->orWhere(function ($qrw) use ($from, $to){
                    $qrw->where('From__c', '<=',$from);
                    $qrw->where('To__c','>=', $from);
                });

                $qr->orWhere(function ($qrw) use ($from, $to){
                    $qrw->where('From__c', '<=',$to);
                    $qrw->where('To__c','>=', $to);
                });
            });
        }

        $data = $query->paginate($per_page);
        if ( empty($data) ) return response()->json([
            'message' => 'No data found!'
        ], 404);
        else return response()->json($data, 200);
    }

    public function getPricebook($pricebookId, Request $request) {
        $queryData = $request->query();

        $data = Pricebook2::with('item')
            ->with(['recordType' => function($qr) {
                $qr->select(['Id', 'Name']);
            }])
            ->where('IsDeleted', 0)
            ->get();

        if ( empty($data) ) return response()->json([
            'message' => 'No data found!'
        ], 404);
        else return response()->json($data, 200);
    }

    public function listCorpPricebook(Request $request)
    {


        $rcType = RecordType::select('Id')
            ->where(function ($qr){
                $qr->where('DeveloperName', 'Corporate');
            })
            ->where('SobjectType', 'Pricebook2')->first();

        $data = Pricebook2::with(['item' => function($qr){
                $qr->where('IsActive', 1);
            }])
            ->where('RecordTypeId', $rcType->Id)
            ->where('IsActive', 1)
            ->where('Is_Public__c', 1)
            ->where('IsDeleted', 0)
            ->get(); dd($data->toArray());
    }
}
