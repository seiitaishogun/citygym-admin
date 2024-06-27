<?php
/**
 * @author tmtuan
 * created Date: 11-Dec-20
 */
namespace App\Domains\Crm\Http\Controllers\Api\Lead;

use App\Domains\Crm\Models\Lead;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SfLeadController extends ApiController {
    public function createLead(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['Lead' => $item])
                ->log('Request | createLead - '.$request->path());


            $ctitem = Lead::find($item['Id']);
            if (!empty($ctitem)) {
                DB::beginTransaction();
                try {
                    unset($item['Id']);
                    $ctitem->fill($item);
                    $ctitem->LastModifiedDate = date('Y-m-d H:i:s');
                    $ctitem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['lead' => $item])
                        ->log('Lead| Update Lead success #' . $ctitem->Id);
                    DB::commit();
                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update Lead success',
                        'result' => $ctitem->Id
                    ];
                } catch (\Exception $e) {
                    DB::rollBack();
                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $ctitem->Id
                    ];
                    continue;
                }
            } else {
                try {
                    //set date
                    $this->setDefaultDate($item);

                    $leadData = Lead::create($item);
                    //log
                    activity('salesforce_api')
                        ->causedBy($leadData)
                        ->withProperties(['lead' => $item])
                        ->log('Lead| create new lead success #' . $item['Id']);

                    $returnData[] = [
                        'success' => true,
                        'error' => "Create new lead success",
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $item['Id']
                    ];
                }
            }
        }

        return response()->json($returnData, 200);
    }

    public function updateLead($id, Request $request ) {
        $postData = $request->all();
        $returnData = [];

        //log request
        activity('salesforce_api')
            ->withProperties(['Lead' => $postData])
            ->log('Request | updateLead - '.$request->path());


        $item = Lead::find($id);
        if ( empty($item) ) $returnData = [
            'success' => false,
            'error' => 'No item found',
            'result' => $id
        ];
        else {
            if ( isset($postData['Id'])) unset($postData['id']);
            if ( isset($postData['CreatedDate'])) unset($postData['CreatedDate']);
            if ( isset($postData['LastModifiedDate']) && !empty($postData['LastModifiedDate']) ) $postData['LastModifiedDate'] = Carbon::parse($postData['LastModifiedDate'])->format('Y-m-d H:i:s');

            $item->update($postData);
            $item->LastModifiedDate = Carbon::now();
            $item->save();
            $returnData = [
                'success' => true,
                'error' => 'Update success',
                'result' => $id
            ];

            //log
            activity('salesforce_api')
                ->withProperties(['lead' => $postData])
                ->log('Lead| update lead success #' . $id);
        }
        return response()->json($returnData, 200);
    }

    public function deleteLead(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['Lead' => $item])
                ->log('Request | deleteLead - '.$request->path());


            $chkItem = Lead::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['Lead' => $item])
                        ->log('Lead| Delete Lead success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete Lead success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['Lead' => $item])
                        ->log('Delete Lead Fail | '.$e->getMessage());

                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $chkItem->Id
                    ];
                    continue;
                }
            }
        }
        return response()->json($returnData, 200);
    }
}
