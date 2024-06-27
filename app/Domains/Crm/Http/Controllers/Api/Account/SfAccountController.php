<?php
/**
 * @author tmtuan
 * created Date: 09-Dec-20
 */
namespace App\Domains\Crm\Http\Controllers\Api\Account;

use App\Domains\Auth\Models\User;
use App\Domains\Crm\Http\Controllers\Traits\getAccountPhoto;
use App\Domains\Crm\Models\SfAcccount;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class SfAccountController extends ApiController{
    use getAccountPhoto;

    public function createSfAcccount(Request $request) {
        $postData = $request->post();
        $returnData = [];

        foreach ( $postData as $item ) {
            $item['BillingAddress'] = json_encode($item['BillingAddress']);
            //log request
            activity('salesforce_api')
                ->withProperties(['Acccount' => $item])
                ->log('Request | createSfAcccount - '.$request->path());

            $accItem = SfAcccount::find($item['Id']);
            if (!empty($accItem)) {
                DB::beginTransaction();
                try {
                    /**
                     * Sử lý thêm phần load ảnh avata về cho user, url ảnh lấy từ field Photo_Url__c
                     */
                    //check user
                    $user = DB::table('user_sf_account')->where('sf_account_id', $accItem->Id)->first();
                    if ( isset($user->user_id ) ) {
                        try {
                            if ($accItem->Photo_Url__c != $item['Photo_Url__c'] || $accItem->Photo_Url__c == '') $this->getPhotoFromUrl($item, $user->user_id);
                            $updateData = [
                                'name' => $item['Name'],
                                'username' => $item['App_Username__c'],
                                'last_name' => $item['LastName'],
                                'first_name' => $item['FirstName'],
                            ];
                            User::where('id', $user->user_id)
                                ->update($updateData);
                        } catch (\Exception $e) {
                            continue;
                        }
                    }

                    unset($item['Id']);
                    $accItem->fill($item);
                    $accItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $accItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['account' => $item])
                        ->log('Account| Update account success #' . $accItem->Id);
                    DB::commit();
                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update account success',
                        'result' => $accItem->Id
                    ];
                } catch (\Exception $e) {
                    DB::rollBack();
                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $accItem->Id
                    ];
                    continue;
                }
                continue;
            } else {
                try {
                    //set date
                    $this->setDefaultDate($item);

                    $accItem = SfAcccount::create($item);
                    //log
                    activity('salesforce_api')
                        ->causedBy($accItem)
                        ->withProperties(['account' => $item])
                        ->log('Account| create new account Id #'.$item['Id']);
                    $returnData[] = [
                        'success' => true,
                        'error' => "Create new account success",
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
}
