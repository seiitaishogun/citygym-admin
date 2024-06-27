<?php
/**
 * @author tmtuan
 * created Date: 09-Dec-20
 */
namespace App\Domains\Crm\Http\Controllers\Api\SfClass;

use App\Domains\Crm\Models\Account;
use App\Domains\Crm\Models\SfClass;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SfClassController extends ApiController{

    public function createClass(Request $request) {
        $postData = $request->post();
        $returnData = [];

        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['Class' => $item])
                ->log('Request | createClass - '.$request->path());

            $classItem = SfClass::find($item['Id']);
            if (!empty($classItem))
            {
                DB::beginTransaction();
                try {
                    unset($item['Id']);
                    $classItem->fill($item);
                    $classItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $classItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['class' => $item])
                        ->log('Class| Update class success #' . $classItem->Id);
                    DB::commit();
                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update class success',
                        'result' => $classItem->Id
                    ];
                } catch (\Exception $e) {
                    DB::rollBack();
                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $classItem->Id
                    ];
                    continue;
                }

            } else {
                //set date
                $this->setDefaultDate($item);

                $accItem = SfClass::create($item);
                //log
                activity('salesforce_api')
                    ->causedBy($accItem)
                    ->withProperties(['class' => $item])
                    ->log('Class| create new class Id #'.$item['Id']);
                $returnData[] = [
                    'success' => true,
                    'error' => "Create success",
                    'result' => $item['Id']
                ];
            }

        }

        return response()->json($returnData, 200);
    }

    public function deleteClass(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['Class' => $item])
                ->log('Request | deleteClass - '.$request->path());


            $chkItem = SfClass::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['SfClass' => $item])
                        ->log('SfClass| Delete SfClass success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete SfClass success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['SfClass' => $item])
                        ->log('Delete SfClass Fail | '.$e->getMessage());

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
