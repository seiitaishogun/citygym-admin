<?php
/**
 * @author tmtuan
 * created Date: 09-Dec-20
 */
namespace App\Domains\Crm\Http\Controllers\Api\Booking;

use App\Domains\Crm\Models\Task;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SfTaskController extends ApiController{

    public function createTask(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['Task' => $item])
                ->log('Request | createTask - '.$request->path());


            $chkItem = Task::find($item['Id']);
            if (!empty($chkItem)) {

                DB::beginTransaction();
                try {
                    unset($item['Id']);
                    $chkItem->fill($item);
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['task' => $item])
                        ->log('Task| Update Task success #' . $chkItem->Id);
                    DB::commit();
                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update Task success',
                        'result' => $chkItem->Id
                    ];
                } catch (\Exception $e) {
                    DB::rollBack();
                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $chkItem->Id
                    ];
                    continue;
                }
            } else {
                try {
                    //set date
                    $this->setDefaultDate($item);

                    $task = Task::create($item);
                    //log
                    activity('salesforce_api')
                        ->causedBy($task)
                        ->withProperties(['task' => $item])
                        ->log('Task| create new task #'.$item['Id']);
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

    public function deleteTask(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['Task' => $item])
                ->log('Request | deleteTask - '.$request->path());


            $chkItem = Task::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['Task' => $item])
                        ->log('Task| Delete Task success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete Task success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['contact' => $item])
                        ->log('Delete Task Fail | '.$e->getMessage());

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
