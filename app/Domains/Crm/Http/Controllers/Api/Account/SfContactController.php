<?php
/**
 * @author tmtuan
 * created Date: 25-Dec-20
 */
namespace App\Domains\Crm\Http\Controllers\Api\Account;

use App\Domains\Crm\Models\Contact;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SfContactController extends ApiController {
    public function createContact(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['Contact' => $item])
                ->log('Request | createContact - '.$request->path());


            $chkItem = Contact::find($item['Id']);
            if (!empty($chkItem)) {
                DB::beginTransaction();
                try {
                    unset($item['Id']);
                    $chkItem->fill($item);
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['contact' => $item])
                        ->log('Contact| Update Contact success #' . $chkItem->Id);
                    DB::commit();
                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update Contact success',
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

                    $newItem = Contact::create($item);
                    //log
                    activity('salesforce_api')
                        ->causedBy($newItem)
                        ->withProperties(['contact' => $item])
                        ->log('Contact| create new contact #' . $item['Id']);

                    $returnData[] = [
                        'success' => true,
                        'error' => "Create new contact success",
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

    public function deleteContact(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['Contact' => $item])
                ->log('Request | deleteContact - '.$request->path());


            $chkItem = Contact::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['contact' => $item])
                        ->log('Contact| Delete Contact success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete Contact success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['contact' => $item])
                        ->log('Delete Contact Fail | '.$e->getMessage());

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
