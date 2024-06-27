<?php
/**
 * @author tmtuan
 * created Date: 24-Dec-20
 */
namespace App\Domains\Crm\Http\Controllers\Api\Account;

use App\Domains\Crm\Models\Bank;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SfBankController extends ApiController {
    public function createBank(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['Bank' => $item])
                ->log('Request | createBank - '.$request->path());


            $chkItem = Bank::find($item['Id']);
            if (!empty($chkItem)) {
                DB::beginTransaction();
                try {
                    unset($item['Id']);
                    $chkItem->fill($item);
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['bank' => $item])
                        ->log('Bank| Update Bank success #' . $chkItem->Id);
                    DB::commit();
                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update Bank success',
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

                    $newItem = Bank::create($item);
                    //log
                    activity('salesforce_api')
                        ->causedBy($newItem)
                        ->withProperties(['bank' => $item])
                        ->log('Bank| create new bank #' . $item['Id']);

                    $returnData[] = [
                        'success' => true,
                        'error' => "Create new bank account success",
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


    public function deleteBank(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['Bank' => $item])
                ->log('Request | deleteBank - '.$request->path());


            $chkItem = Bank::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['bank' => $item])
                        ->log('Bank| Delete Bank success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete Bank success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['Bank' => $item])
                        ->log('Delete Bank Fail | '.$e->getMessage());

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
