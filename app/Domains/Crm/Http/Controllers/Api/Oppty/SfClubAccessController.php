<?php
/**
 * @author tmtuan
 * created Date: 09-Mar-21
 */
namespace App\Domains\Crm\Http\Controllers\Api\Oppty;

use App\Domains\Crm\Models\ClubAppliedOpportunity;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;

class SfClubAccessController extends ApiController {
    public function createClubAccess( Request $request) {
        $postData = $request->post();
        $returnData = [];

        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['ClubAccess' => $item])
                ->log('Request | createClubAccess - '.$request->path());

            $ctitem = ClubAppliedOpportunity::find($item['Id']);
            if (!empty($ctitem)) {
                try {
                    unset($item['Id']);
                    $ctitem->fill($item);
                    $ctitem->LastModifiedDate = date('Y-m-d H:i:s');
                    $ctitem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['ClubAppliedOpportunity' => $item])
                        ->log('ClubAppliedOpportunity| Update Club Applied Opportunity success #' . $ctitem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update Club Applied Opportunity success',
                        'result' => $ctitem->Id
                    ];
                } catch (\Exception $e) {
                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $ctitem->Id
                    ];
                    continue;
                }
            } else {
                $this->setDefaultDate($item);
                try {
                    $newItem = ClubAppliedOpportunity::create($item);

                    //log
                    activity('salesforce_api')
                        ->causedBy($newItem)
                        ->withProperties(['ClubAppliedOpportunity' => $item])
                        ->log('ClubAppliedOpportunity| create new Club Applied Opportunity success #'.$item['Id']);

                    $returnData[] = [
                        'success' => true,
                        'error' => "Create success",
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    $returnData[] = [
                        'success' => false,
                        'error' => "Invalid form field for Club Applied Opportunity table!",
                        'result' => $item['Id']
                    ];
                    continue;
                }
            }
        }
        return response()->json($returnData, 200);
    }

    public function deleteClubAccess(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['ClubAccess' => $item])
                ->log('Request | deleteClubAccess - '.$request->path());


            $chkItem = ClubAppliedOpportunity::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['ClubAppliedOpportunity' => $item])
                        ->log('ClubAppliedOpportunity| Delete Club Applied Opportunity success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete Opportunity success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['ClubAppliedOpportunity' => $item])
                        ->log('Delete Club Applied Opportunity Fail | '.$e->getMessage());

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
