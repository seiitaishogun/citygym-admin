<?php
/**
 * @author tmtuan
 * created Date: 11-Dec-20
 */
namespace App\Domains\Crm\Http\Controllers\Api\Oppty;

use App\Domains\Crm\Models\AppNotification;
use App\Domains\Crm\Models\Opportunity;
use App\Domains\Crm\Models\OpportunityLineBenefit;
use App\Domains\Crm\Models\OpportunityLineGift;
use App\Domains\Crm\Models\OpportunityLineItem;
use App\Domains\Crm\Models\OpportunityLinePromotion;
use App\Domains\Crm\Models\UserSfAccount;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SfOpptyController extends ApiController {
    public function createOppty(Request $request) {
        $postData = $request->post();
        $returnData = [];

        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['Oppty' => $item])
                ->log('Request | createOppty - '.$request->path());


            $ctitem = Opportunity::find($item['Id']);
            if (!empty($ctitem) || isset($ctitem->Id))
            {
                DB::beginTransaction();
                try {
                    unset($item['Id']);
                    $ctitem->fill($item);
                    $ctitem->LastModifiedDate = date('Y-m-d H:i:s');
                    $ctitem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['oppty' => $item])
                        ->log('Oppty| Update Oppty success #' . $ctitem->Id);
                    DB::commit();
                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update Oppty success',
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
                //set date
                $this->setDefaultDate($item);
                try {
                    $opptyData = Opportunity::create($item);

                    //log
                    activity('salesforce_api')
                        ->causedBy($opptyData)
                        ->withProperties(['oppty' => $item])
                        ->log('Oppty| create new opportunity success #'.$item['Id']);

                    $returnData[] = [
                        'success' => true,
                        'error' => "Create success",
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    $returnData[] = [
                        'success' => false,
                        'error' => "Invalid form field for Opportunity table!",
                        'result' => $item['Id']
                    ];
                    continue;
                }
            }
        }

        return response()->json($returnData, 200);
    }

    public function createOpptyLineItem(Request $request) {
        $postData = $request->post();
        $returnData = [];

        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['OpptyLineItem' => $item])
                ->log('Request | createOpptyLineItem - '.$request->path());


            $chkItem = OpportunityLineItem::find($item['Id']);
            if (!empty($chkItem) || isset($chkItem->Id)) {
                try {
                    $bkItem = $item;
                    unset($item['Id']);
                    $chkItem->fill($item);
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['oppty_line_item' => $item,
                                    'oppty_line_item_input' => $bkItem])
                        ->log('oppty_line_item| Update opportunity line item success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update opportunity line item success',
                        'result' => $chkItem->Id
                    ];
                } catch (\Exception $e) {
                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $chkItem->Id
                    ];
                    continue;
                }

            } else {
                //set date
                $this->setDefaultDate($item);

                $newItem = OpportunityLineItem::create($item);
                //log
                activity('salesforce_api')
                    ->causedBy($newItem)
                    ->withProperties(['oppty_line_item' => $item])
                    ->log('oppty_line_item| create new opportunity line item #'.$item['Id']);
                $returnData[] = [
                    'success' => true,
                    'error' => "Create success",
                    'result' => $item['Id']
                ];
            }
        }

        return response()->json($returnData, 200);
    }

    public function createOpptyLineBenefit(Request $request) {
        $postData = $request->post();
        $returnData = [];

        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['OpptyLineBenefit' => $item])
                ->log('Request | createOpptyLineBenefit - '.$request->path());


            $chkItem = OpportunityLineBenefit::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    unset($item['Id']);
                    $chkItem->fill($item);
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['oppty_line_benefit' => $item])
                        ->log('oppty_line_benefit| Update opportunity line benefit success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update opportunity line benefit success',
                        'result' => $chkItem->Id
                    ];
                } catch (\Exception $e) {
                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $chkItem->Id
                    ];
                    continue;
                }

            } else {
                //set date
                $this->setDefaultDate($item);

                $newItem = OpportunityLineBenefit::create($item);
                //log
                activity('salesforce_api')
                    ->causedBy($newItem)
                    ->withProperties(['oppty_line_benefit' => $item])
                    ->log('Oppty| create new opportunity line benefit ID #'.$item['Id']);
                $returnData[] = [
                    'success' => true,
                    'error' => "Create success",
                    'result' => $item['Id']
                ];
            }
        }

        return response()->json($returnData, 200);

    }

    public function createOpptyLineGift(Request $request) {
        $postData = $request->post();
        $returnData = [];

        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['OpptyLineGift' => $item])
                ->log('Request | createOpptyLineGift - '.$request->path());


            $chkItem = OpportunityLineGift::find($item['Id']);
            if (!empty($chkItem))
            {
                try {
                    unset($item['Id']);
                    $chkItem->fill($item);
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['oppty_line_gift' => $item])
                        ->log('oppty_line_gift| Update opportunity line gift success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update opportunity line gift success',
                        'result' => $chkItem->Id
                    ];
                } catch (\Exception $e) {
                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $chkItem->Id
                    ];
                    continue;
                }
            } else {
                //set date
                $this->setDefaultDate($item);

                $newItem = OpportunityLineGift::create($item);
                //log
                activity('salesforce_api')
                    ->causedBy($newItem)
                    ->withProperties(['oppty_line_gift' => $item])
                    ->log('Oppty| create new opportunity line gift ID #'.$item['Id']);

                $returnData[] = [
                    'success' => true,
                    'error' => "Create success",
                    'result' => $item['Id']
                ];
            }
        }

        return response()->json($returnData, 200);

    }

    public function createOpptyLinePromotion(Request $request) {
        $postData = $request->post();
        $returnData = [];

        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['OpptyLinePromotion' => $item])
                ->log('Request | createOpptyLinePromotion - '.$request->path());


            $chkItem = OpportunityLinePromotion::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    unset($item['Id']);
                    $chkItem->fill($item);
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['oppty_line_promotion' => $item])
                        ->log('oppty_line_promotion| Update opportunity line Promotion success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update opportunity line Promotion success',
                        'result' => $chkItem->Id
                    ];
                } catch (\Exception $e) {
                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $chkItem->Id
                    ];
                    continue;
                }
            } else {
                //set date
                $this->setDefaultDate($item);

                $newItem = OpportunityLinePromotion::create($item);
                //log
                activity('salesforce_api')
                    ->causedBy($newItem)
                    ->withProperties(['oppty_line_promotion' => $item])
                    ->log('Oppty| create new opportunity line Promotion ID #'.$item['Id']);
                $returnData[] = [
                    'success' => true,
                    'error' => "Create success",
                    'result' => $item['Id']
                ];
            }
        }

         return response()->json($returnData, 200);
    }

    public function deleteOppty(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['Oppty' => $item])
                ->log('Request | deleteOppty - '.$request->path());


            $chkItem = Opportunity::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['Opportunity' => $item])
                        ->log('Opportunity| Delete Opportunity success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete Opportunity success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['Opportunity' => $item])
                        ->log('Delete Opportunity Fail | '.$e->getMessage());

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

    public function deleteOpptyLineItem(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['OpptyLineItem' => $item])
                ->log('Request | deleteOpptyLineItem - '.$request->path());


            $chkItem = OpportunityLineItem::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['OpportunityLineItem' => $item])
                        ->log('OpportunityLineItem| Delete OpportunityLineItem success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete OpportunityLineItem success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['OpportunityLineItem' => $item])
                        ->log('Delete OpportunityLineItem Fail | '.$e->getMessage());

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


    public function deleteOpptyLineBenefit(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['OpptyLineItemBenefit' => $item])
                ->log('Request | deleteOpptyLineBenefit - '.$request->path());


            $chkItem = OpportunityLineBenefit::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['OpportunityLineBenefit' => $item])
                        ->log('OpportunityLineBenefit| Delete OpportunityLineBenefit success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete OpportunityLineBenefit success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['OpportunityLineBenefit' => $item])
                        ->log('Delete OpportunityLineBenefit Fail | '.$e->getMessage());

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

    public function deleteOpptyLineGift(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['OpptyLineItemGift' => $item])
                ->log('Request | deleteOpptyLineGift - '.$request->path());


            $chkItem = OpportunityLineGift::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['OpportunityLineGift' => $item])
                        ->log('OpportunityLineGift| Delete OpportunityLineGift success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete OpportunityLineGift success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['OpportunityLineGift' => $item])
                        ->log('Delete OpportunityLineGift Fail | '.$e->getMessage());

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

    public function deleteOpptyLinePromotion(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['OpptyLineItemPromotion' => $item])
                ->log('Request | deleteOpptyLinePromotion - '.$request->path());


            $chkItem = OpportunityLinePromotion::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['OpportunityLinePromotion' => $item])
                        ->log('OpportunityLinePromotion| Delete OpportunityLinePromotion success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete OpportunityLinePromotion success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['OpportunityLinePromotion' => $item])
                        ->log('Delete OpportunityLinePromotion Fail | '.$e->getMessage());

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
