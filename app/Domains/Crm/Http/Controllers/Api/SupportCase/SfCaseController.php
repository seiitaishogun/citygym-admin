<?php
/**
 * @author tmtuan
 * created Date: 10-Dec-20
 */
namespace App\Domains\Crm\Http\Controllers\Api\SupportCase;

use App\Domains\Crm\Models\CaseContractTransfer;
use App\Domains\Crm\Models\SupportCase;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;

class SfCaseController extends ApiController {
    public function createCase(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['Case' => $item])
                ->log('Request | createCase - '.$request->path());

            $caseItem = SupportCase::find($item['Id']);
            if ( isset($caseItem->Id) || !empty($caseItem))
            {
                unset($item['Id']);
                $caseItem->fill($item);
                $caseItem->LastModifiedDate = date('Y-m-d H:i:s');
                $caseItem->save();
                //log
                activity('salesforce_api')
                    ->withProperties(['case' => $caseItem])
                    ->log('Case| Update Case success #' . $caseItem->Id);

                $returnData[] = [
                    'success' => true,
                    'error' => "Update success",
                    'result' => $caseItem->Id
                ];
                continue;
            }
            else
            {
                $spCase = SupportCase::create($item);
                //log
                activity('salesforce_api')
                    ->causedBy($spCase)
                    ->withProperties(['case' => $item])
                    ->log('Case| create new support case #'.$item['Id']);

                $returnData[] = [
                    'success' => true,
                    'error' => "Create success",
                    'result' => $item['Id']
                ];
            }

        }

        return response()->json($returnData, 200);

    }

    public function CaseContractTransfer(Request $request) {
        $postData = $request->post();
        $returnData = [];

        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['CaseContractTransfer' => $item])
                ->log('Request | CaseContractTransfer - '.$request->path());

            $spCaseContract_c = CaseContractTransfer::find($item['Id']);
            if (!empty($spCaseContract_c))
            {
                unset($item['Id']);
                $spCaseContract_c->fill($item);
                $spCaseContract_c->LastModifiedDate = date('Y-m-d H:i:s');
                $spCaseContract_c->save();
                //log
                activity('salesforce_api')
                    ->withProperties(['case' => $spCaseContract_c])
                    ->log('Case| Update Case contract transfer success #' . $item['Id']);

                $returnData[] = [
                    'success' => true,
                    'error' => "Update success",
                    'result' => $item['Id']
                ];
                continue;
            }
            else {
                $spCase = CaseContractTransfer::create($item);
                //log
                activity('salesforce_api')
                    ->causedBy($spCase)
                    ->withProperties(['case_contract_transfer' => $item])
                    ->log('Case| create new support case contract transfer #'.$item['Id']);

                $returnData[] = [
                    'success' => true,
                    'error' => "Create success",
                    'result' => $item['Id']
                ];
            }

        }

        return response()->json($returnData, 200);
    }

    public function deleteCase(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['Case' => $item])
                ->log('Request | deleteCase - '.$request->path());

            $chkItem = SupportCase::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['Case' => $item])
                        ->log('Case| Delete Case success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete Case success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['Case' => $item])
                        ->log('Delete Case Fail | '.$e->getMessage());

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

    public function deleteCaseTransfer(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['CaseTransfer' => $item])
                ->log('Request | deleteCaseTransfer - '.$request->path());

            $chkItem = CaseContractTransfer::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['CaseTransfer' => $item])
                        ->log('CaseTransfer| Delete CaseTransfer success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete CaseTransfer success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['CaseTransfer' => $item])
                        ->log('Delete CaseTransfer Fail | '.$e->getMessage());

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
