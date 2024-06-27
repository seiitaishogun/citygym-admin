<?php
/**
 * @author tmtuan
 * created Date: 23-Dec-20
 */
namespace App\Domains\Crm\Http\Controllers\Api\SfClass;

use App\Domains\Crm\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SfClassRoomController extends SfClassController {
    public function createClassRoom(Request $request) {
        $postData = $request->post();
        $returnData = [];

        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['ClassRoom' => $item])
                ->log('Request | createClassRoom - '.$request->path());

            $classRoomItem = Classroom::find($item['Id']);
            if (!empty($classRoomItem))
            {
                DB::beginTransaction();
                try {
                    unset($item['Id']);
                    $classRoomItem->fill($item);
                    $classRoomItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $classRoomItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['classRoom' => $item])
                        ->log('Class Room| Update class Room success #' . $classRoomItem->Id);
                    DB::commit();
                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update class Room success',
                        'result' => $classRoomItem->Id
                    ];
                } catch (\Exception $e) {
                    DB::rollBack();
                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $classRoomItem->Id
                    ];
                    continue;
                }
            } else {
                //set date
                $this->setDefaultDate($item);

                $newItem = Classroom::create($item);
                //log
                activity('salesforce_api')
                    ->causedBy($newItem)
                    ->withProperties(['classRoom' => $item])
                    ->log('Class Room| create new class room Id #'.$item['Id']);
                $returnData[] = [
                    'success' => true,
                    'error' => "Create success",
                    'result' => $item['Id']
                ];
            }

        }

        return response()->json($returnData, 200);
    }

    public function deleteClassRoom(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['ClassRoom' => $item])
                ->log('Request | deleteClassRoom - '.$request->path());

            $chkItem = Classroom::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['Classroom' => $item])
                        ->log('Classroom| Delete Classroom success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete Classroom success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['Classroom' => $item])
                        ->log('Delete Classroom Fail | '.$e->getMessage());

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
