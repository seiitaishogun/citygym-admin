<?php
/**
 * @author tmtuan
 * created Date: 10-Dec-20
 */

namespace App\Domains\Crm\Http\Controllers\Api\Contract;

use App\Domains\Crm\Models\Contract;
use App\Domains\Crm\Models\ContractBenefit;
use App\Domains\Crm\Models\ContractClub;
use App\Domains\Crm\Models\ContractGift;
use App\Domains\Crm\Models\ContractProduct;
use App\Domains\Crm\Models\ContractPromotion;
use App\Domains\Crm\Models\ContractSale;
use App\Domains\Crm\Models\ContractSuspend;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SfContractController extends ApiController
{
    public function createContract(Request $request)
    {
        $postData = $request->post();
        $returnData = [];

        foreach ($postData as $item) {
            //log request
            activity('salesforce_api')
                ->withProperties(['Contract' => $item])
                ->log('Request | createContract - '.$request->path());


            $ctitem = Contract::find($item['Id']);
            if (!empty($ctitem)) {
                try {
                    unset($item['Id']);
                    $ctitem->fill($item);
                    $ctitem->LastModifiedDate = $item['LastModifiedDate'] ?? date('Y-m-d H:i:s');
                    $ctitem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['contract' => $item])
                        ->log('Contract| Update contract success #' . $ctitem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update contract success',
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
                try {
                    //set date
                    $this->setDefaultDate($item);
                    $contract = Contract::create($item);

                    //log
                    activity('salesforce_api')
                        ->causedBy($contract)
                        ->withProperties(['contract' => $item])
                        ->log('Contract| create new contract #' . $item['Id']);

                    $returnData[] = [
                        'success' => true,
                        'error' => "Create success",
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $item['Id']
                    ];
                    continue;
                }
            }

        }

        return response()->json($returnData, 200);

    }

    public function createContractBenefit(Request $request)
    {
        $postData = $request->post();
        $returnData = [];
        foreach ($postData as $item) {
            //log request
            activity('salesforce_api')
                ->withProperties(['ContractBenefit' => $item])
                ->log('Request | createContractBenefit - '.$request->path());


            $ctitem = ContractBenefit::find($item['Id']);
            if (!empty($ctitem)) {
                try {
                    unset($item['Id']);
                    $ctitem->fill($item);
                    $ctitem->LastModifiedDate = $item['LastModifiedDate'] ?? date('Y-m-d H:i:s');
                    $ctitem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['contract_benefit' => $item])
                        ->log('Contract benefit| Update contract benefit success #' . $ctitem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update contract benefit success',
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
                try {
                    //set date
                    $this->setDefaultDate($item);

                    $ctBenefit = ContractBenefit::create($item);

                    //log
                    activity('salesforce_api')
                        ->causedBy($ctBenefit)
                        ->withProperties(['contract_benefit' => $item])
                        ->log('Contract| create new contract benefit');

                    $returnData[] = [
                        'success' => true,
                        'error' => "Create success",
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['contract_benefit' => $item])
                        ->log('Contract| create new contract benefit Fail <br> Message: '.$e->getMessage());
                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $item['Id']
                    ];
                    continue;
                }
            }

        }
        return response()->json($returnData, 200);
    }

    public function createContractClub(Request $request)
    {
        $postData = $request->post();
        $returnData = [];
        foreach ($postData as $item) {
            //log request
            activity('salesforce_api')
                ->withProperties(['ContractClub' => $item])
                ->log('Request | createContractClub - '.$request->path());


            $ctitem = ContractClub::find($item['Id']);
            if (!empty($ctitem)) {
                try {
                    unset($item['Id']);
                    $ctitem->fill($item);
                    $ctitem->LastModifiedDate = $item['LastModifiedDate'] ?? date('Y-m-d H:i:s');
                    $ctitem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['contract_club' => $item])
                        ->log('Contract Sale| Update contract club success #' . $ctitem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update contract club success',
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
                try {
                    //set date
                    $this->setDefaultDate($item);

                    $insertItem = ContractClub::create($item);
                    //log
                    activity('salesforce_api')
                        ->causedBy($insertItem)
                        ->withProperties(['contract_club' => $item])
                        ->log('Contract Club| create new contract club success #'.$item['Id']);
                    $returnData[] = [
                        'success' => true,
                        'error' => "Create success",
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['contract_club' => $item])
                        ->log('Contract Club| create new contract club Fail <br> Message: '.$e->getMessage());
                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $item['Id']
                    ];
                    continue;
                }
            }

        }
        return response()->json($returnData, 200);
    }

    public function createContractGift(Request $request)
    {
        $postData = $request->post();
        $returnData = [];
        foreach ($postData as $item) {
            //log request
            activity('salesforce_api')
                ->withProperties(['ContractGift' => $item])
                ->log('Request | createContractGift - '.$request->path());


            $ctitem = ContractGift::find($item['Id']);
            if (!empty($ctitem)) {
                try {
                    unset($item['Id']);
                    $ctitem->fill($item);
                    $ctitem->LastModifiedDate = $item['LastModifiedDate'] ?? date('Y-m-d H:i:s');
                    $ctitem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['contract_gift' => $item])
                        ->log('Contract gift| Update contract gift success #' . $ctitem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update contract gift success',
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
                try {
                    //set date
                    $this->setDefaultDate($item);

                    $insertItem = ContractGift::create($item);
                    //log
                    activity('salesforce_api')
                        ->causedBy($insertItem)
                        ->withProperties(['contract_gift' => $item])
                        ->log('Contract Gift| create new contract gift success #'.$item['Id']);

                    $returnData[] = [
                        'success' => true,
                        'error' => "Create success",
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['contract_gift' => $item])
                        ->log('Contract Gift| create new contract gift Fail <br> Message: '.$e->getMessage());
                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $item['Id']
                    ];
                    continue;
                }
            }
        }
        return response()->json($returnData, 200);
    }

    public function createContractProduct(Request $request)
    {
        $postData = $request->post();
        $returnData = [];
        foreach ($postData as $item) {
            //log request
            activity('salesforce_api')
                ->withProperties(['ContractProduct' => $item])
                ->log('Request | createContractProduct - '.$request->path());

            $ctitem = ContractProduct::find($item['Id']);
            if (!empty($ctitem)) {
                try {
                    unset($item['Id']);
                    $ctitem->fill($item);
                    $ctitem->LastModifiedDate = $item['LastModifiedDate'] ?? date('Y-m-d H:i:s');
                    $ctitem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['contract_product' => $item])
                        ->log('Contract product| Update contract product success #' . $ctitem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update contract product success',
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
                try {
                    //set date
                    $this->setDefaultDate($item);

                    $insertItem = ContractProduct::create($item);
                    //log
                    activity('salesforce_api')
                        ->causedBy($insertItem)
                        ->withProperties(['contract_product' => $item])
                        ->log('Contract Product| create new contract product #'.$item['Id']);
                    $returnData[] = [
                        'success' => true,
                        'error' => "Create success",
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['contract_product' => $item])
                        ->log('Contract Product| create new contract product Fail <br> Message: '.$e->getMessage());
                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $item['Id']
                    ];
                    continue;
                }
            }
        }
        return response()->json($returnData, 200);
    }

    public function createContractPromotion(Request $request)
    {
        $postData = $request->post();
        $returnData = [];
        foreach ($postData as $item) {
            //log request
            activity('salesforce_api')
                ->withProperties(['ContractPromotion' => $item])
                ->log('Request | createContractPromotion - '.$request->path());


            $ctitem = ContractPromotion::find($item['Id']);
            if (!empty($ctitem)) {
                try {
                    unset($item['Id']);
                    $ctitem->fill($item);
                    $ctitem->LastModifiedDate = $item['LastModifiedDate'] ?? date('Y-m-d H:i:s');
                    $ctitem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['contract_promotion' => $item])
                        ->log('Contract promotion| Update contract promotion success #' . $ctitem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update contract promotion success',
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

                $returnData[] = [
                    'success' => false,
                    'error' => "This item is exist",
                    'result' => $item['Id']
                ];
                continue;
            } else {
                try {
                    //set date
                    $this->setDefaultDate($item);

                    $insertItem = ContractPromotion::create($item);
                    //log
                    activity('salesforce_api')
                        ->causedBy($insertItem)
                        ->withProperties(['contract_promotion' => $item])
                        ->log('Contract Promotion| create new contract promotion success #'.$item['Id']);
                    $returnData[] = [
                        'success' => true,
                        'error' => "Create success",
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['contract_promotion' => $item])
                        ->log('Contract Promotion| create new contract promotion Fail <br> Message: '.$e->getMessage());
                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $item['Id']
                    ];
                    continue;
                }
            }
        }
        return response()->json($returnData, 200);
    }

    public function createContractSale(Request $request)
    {
        $postData = $request->post();
        $returnData = [];
        foreach ($postData as $item) {
            //log request
            activity('salesforce_api')
                ->withProperties(['ContractSale' => $item])
                ->log('Request | createContractSale - '.$request->path());


            $ctitem = ContractSale::find($item['Id']);
            if (!empty($ctitem)) {
                DB::beginTransaction();
                try {
                    unset($item['Id']);
                    $ctitem->fill($item);
                    $ctitem->LastModifiedDate = $item['LastModifiedDate'] ?? date('Y-m-d H:i:s');
                    $ctitem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['contract_sale' => $item])
                        ->log('Contract Sale| Update contract sale success #' . $ctitem->Id);
                    DB::commit();
                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update contract sale success',
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

                    $insertItem = ContractSale::create($item);
                    //log
                    activity('salesforce_api')
                        ->causedBy($insertItem)
                        ->withProperties(['contract_sale' => $item])
                        ->log('Contract| create new contract sale success #'.$item['Id']);
                    $returnData[] = [
                        'success' => true,
                        'error' => "Create success",
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['contract_sale' => $item])
                        ->log('Contract Sale| create new contract sale Fail <br> Message: '.$e->getMessage());
                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $item['Id']
                    ];
                    continue;
                }
            }
        }
        return response()->json($returnData, 200);
    }

    /**
     * Edit an Contract_sale item
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function editContractSale($id, Request $request) {
        $postData = $request->all();

        //log request
        activity('salesforce_api')
            ->withProperties(['ContractSale' => $postData])
            ->log('Request | createContractSale - '.$request->path());


        $item = ContractSale::find($id);
        if ( empty($item) ) return response()->json([
            'success' => false,
            'error' => 'No item found',
            'result' => $id
        ], 200);
        else {
            DB::beginTransaction();
            try {
                unset($postData['Id']);
                $item->fill($postData);
                $item->save();

                //log
                activity('salesforce_api')
                    ->withProperties(['contract_sale' => $item])
                    ->log('Contract Sale| edit contract sale success #'.$id);
                DB::commit();
                return response()->json([
                    'success' => true,
                    'error' => 'Update success',
                    'result' => $id
                ], 200);
            }catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                    'result' => $id
                ], 200);
            }

        }

    }

    /**
     * delete a contractSale object
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteContractSale($id, Request $request) {
        //log request
        activity('salesforce_api')
            ->withProperties(['Contract-Sale-Id' => $id])
            ->log('Request | deleteContractSale - '.$request->path());

        $item = ContractSale::find($id);
        if ( empty($item) ) return response()->json([
            'success' => false,
            'error' => 'No item found',
            'result' => $id
        ], 200);
        else {
            $item->delete();
            //log
            activity('salesforce_api')
                ->withProperties(['contract_sale' => $item])
                ->log('Contract Sale| delete contract sale success #'.$id);

            return response()->json([
                'success' => true,
                'error' => 'Record delete success!',
                'result' => $id
            ], 200);
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function mdeleteContractSale(Request $request) {
        $postData = $request->all();

        //log request
        activity('salesforce_api')
            ->withProperties(['ContractSale' => $postData])
            ->log('Request | mdeleteContractSale - '.$request->path());


        $returnData = [];
        foreach ($postData as $item) {
            $delItem = ContractSale::find($item['Id']);
            if (empty($delItem)) $returnData[] = [
                'success' => false,
                'error' => 'No item found',
                'result' => $item['Id']
            ];
            else {
                $delItem->delete();
                //log
                activity('salesforce_api')
                    ->withProperties(['contract_sale' => $item])
                    ->log('Contract Sale| delete contract sale success #' . $item['Id']);

                $returnData[] = [
                    'success' => true,
                    'error' => 'Record delete success!',
                    'result' => $item['Id']
                ];
            }
        }
        return response()->json($returnData, 200);
    }

    public function createContractSuspend(Request $request)
    {
        $postData = $request->post();
        $returnData = [];
        foreach ($postData as $item) {
            //log request
            activity('salesforce_api')
                ->withProperties(['ContractSuspend' => $item])
                ->log('Request | createContractSuspend - '.$request->path());

            $ctitem = ContractSuspend::find($item['Id']);
            if (!empty($ctitem)) {
                $returnData[] = [
                    'success' => false,
                    'error' => "This item is exist",
                    'result' => $item['Id']
                ];
                continue;
            } else {
                try {
                    //set date
                    $this->setDefaultDate($item);

                    $insertItem = ContractSuspend::create($item);
                    //log
                    activity('salesforce_api')
                        ->causedBy($insertItem)
                        ->withProperties(['contract_suspend' => $item])
                        ->log('Contract| create new contract suspend success #'.$item['Id']);
                    $returnData[] = [
                        'success' => true,
                        'error' => "Create success",
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['contract_suspend' => $item])
                        ->log('Contract Suspend| create new contract suspend Fail <br> Message: '.$e->getMessage());
                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $item['Id']
                    ];
                    continue;
                }
            }
        }
        return response()->json($returnData, 200);
    }

    public function deleteContract(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['Contract' => $item])
                ->log('Request | deleteContract - '.$request->path());


            $chkItem = Contract::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['Contract' => $item])
                        ->log('Contract| Delete Contract success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete Contract success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['Contract' => $item])
                        ->log('Delete Contract Fail | '.$e->getMessage());

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

    public function deleteContractBenefit(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['ContractBenefit' => $item])
                ->log('Request | deleteContractBenefit - '.$request->path());


            $chkItem = ContractBenefit::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['ContractBenefit' => $item])
                        ->log('ContractBenefit| Delete ContractBenefit success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete ContractBenefit success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['ContractBenefit' => $item])
                        ->log('Delete ContractBenefit Fail | '.$e->getMessage());

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

    public function deleteContractClub(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['ContractClub' => $item])
                ->log('Request | deleteContractClub - '.$request->path());


            $chkItem = ContractClub::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['ContractClub' => $item])
                        ->log('ContractClub| Delete ContractClub success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete ContractClub success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['ContractClub' => $item])
                        ->log('Delete ContractClub Fail | '.$e->getMessage());

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

    public function deleteContractGift(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['ContractGift' => $item])
                ->log('Request | deleteContractGift - '.$request->path());


            $chkItem = ContractGift::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['ContractGift' => $item])
                        ->log('ContractGift| Delete ContractGift success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete ContractGift success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['ContractGift' => $item])
                        ->log('Delete ContractGift Fail | '.$e->getMessage());

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

    public function deleteContractProduct(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['ContractProduct' => $item])
                ->log('Request | deleteContractProduct - '.$request->path());


            $chkItem = ContractProduct::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['ContractProduct' => $item])
                        ->log('ContractProduct| Delete ContractProduct success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete ContractProduct success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['ContractProduct' => $item])
                        ->log('Delete ContractProduct Fail | '.$e->getMessage());

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

    public function deleteContractPromotion(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['ContractPromotion' => $item])
                ->log('Request | deleteContractPromotion - '.$request->path());


            $chkItem = ContractPromotion::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['ContractPromotion' => $item])
                        ->log('ContractPromotion| Delete ContractPromotion success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete ContractPromotion success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['ContractPromotion' => $item])
                        ->log('Delete ContractPromotion Fail | '.$e->getMessage());

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

    public function deleteContractSuspend(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['ContractSuspend' => $item])
                ->log('Request | deleteContractSuspend - '.$request->path());


            $chkItem = ContractSuspend::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['ContractSuspend' => $item])
                        ->log('ContractSuspend| Delete ContractSuspend success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete ContractSuspend success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['ContractSuspend' => $item])
                        ->log('Delete ContractSuspend Fail | '.$e->getMessage());

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
