<?php
/**
 * @author tmtuan
 * created Date: 23-Dec-20
 */
namespace App\Domains\Crm\Http\Controllers\Api\SfClass;

use App\Domains\Crm\Models\ClassGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SfClassGroupController extends SfClassController {
    public function createClassGroup(Request $request) {
        $postData = $request->post();
        $returnData = [];

        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['ClassGroup' => $item])
                ->log('Request | createClassGroup - '.$request->path());

            $classGroupItem = ClassGroup::find($item['Id']);
            if (!empty($classGroupItem))
            {
                DB::beginTransaction();
                try {
                    unset($item['Id']);
                    $classGroupItem->fill($item);
                    $classGroupItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $classGroupItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['classGroup' => $item])
                        ->log('Class| Update class Group success #' . $classGroupItem->Id);
                    DB::commit();
                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update class Group success',
                        'result' => $classGroupItem->Id
                    ];
                } catch (\Exception $e) {
                    DB::rollBack();
                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $classGroupItem->Id
                    ];
                    continue;
                }

            } else {
                //set date
                $this->setDefaultDate($item);

                $newItem = ClassGroup::create($item);
                //log
                activity('salesforce_api')
                    ->causedBy($newItem)
                    ->withProperties(['classGroup' => $item])
                    ->log('Class Group| create new class group Id #'.$item['Id']);
                $returnData[] = [
                    'success' => true,
                    'error' => "Create success",
                    'result' => $item['Id']
                ];
            }
        }

        return response()->json($returnData, 200);
    }

    public function deleteClassGroup(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['ClassGroup' => $item])
                ->log('Request | deleteClassGroup - '.$request->path());

            $chkItem = ClassGroup::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['ClassGroup' => $item])
                        ->log('ClassGroup| Delete ClassGroup success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete ClassGroup success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['ClassGroup' => $item])
                        ->log('Delete ClassGroup Fail | '.$e->getMessage());

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
