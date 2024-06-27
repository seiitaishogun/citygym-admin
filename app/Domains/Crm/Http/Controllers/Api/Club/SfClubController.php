<?php
/**
 * @author tmtuan
 * created Date: 08-Dec-20
 */

namespace App\Domains\Crm\Http\Controllers\Api\Club;

use App\Domains\Crm\Models\Club;
use App\Domains\Crm\Models\ClubAccessProduct;
use App\Domains\Crm\Models\ClubAppliedPricebook;
use App\Domains\Crm\Models\ClubAppliedPromotions;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Support\Facades\DB;

class SfClubController extends ApiController {

    public function createClub(Request $request) {
        $postData = $request->post();
        $returnData = [];

        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['Club' => $item])
                ->log('Request | createClub - '.$request->path());


            $clubItem = Club::find($item['Id']);
            if (!empty($clubItem)) {
                DB::beginTransaction();
                try {
                    unset($item['Id']);
                    $clubItem->fill($item);
                    $clubItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $clubItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['club' => $item])
                        ->log('Club| Update Club success #' . $clubItem->Id);
                    DB::commit();
                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update Club success',
                        'result' => $clubItem->Id
                    ];
                } catch (\Exception $e) {
                    DB::rollBack();
                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $clubItem->Id
                    ];
                    continue;
                }
            } else {
                //set date
                $this->setDefaultDate($item);
                $club = Club::create($item);
                //log
                activity('salesforce_api')
                    ->causedBy($club)
                    ->withProperties(['club' => $item])
                    ->log('Club| create new club #'. $item['Id']);

                $returnData[] = [
                    'success' => true,
                    'error' => "Create success",
                    'result' => $item['Id']
                ];
            }
        }

        return response()->json($returnData, 200);
    }

    public function deleteClub(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['Club' => $item])
                ->log('Request | deleteClub - '.$request->path());


            $chkItem = Club::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['Club' => $item])
                        ->log('Club| Delete Club success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete Club success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['Club' => $chkItem])
                        ->log($e->getMessage());

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

    public function createClubAccessProduct(Request $request) {
        $postData = $request->post();
        $returnData = [];

        foreach ($postData as $item) {
            //log request
            activity('salesforce_api')
                ->withProperties(['ClubAccessProduct' => $item])
                ->log('Request | createClubAccessProduct - '.$request->path());


            $clubAccessPdItem = ClubAccessProduct::find($item['Id']);
            if (!empty($clubAccessPdItem)) {
                DB::beginTransaction();
                try {
                    unset($item['Id']);
                    $clubAccessPdItem->fill($item);
                    $clubAccessPdItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $clubAccessPdItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['ClubAccessProduct' => $item])
                        ->log('ClubAccessProduct| Update Club Access Product success #' . $clubAccessPdItem->Id);
                    DB::commit();
                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update Club Access Product success',
                        'result' => $clubAccessPdItem->Id
                    ];
                } catch (\Exception $e) {
                    DB::rollBack();
                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $clubAccessPdItem->Id
                    ];
                    continue;
                }
                continue;
            } else {
                //set date
                $this->setDefaultDate($item);
                //insert data
                $clubAccessItem = ClubAccessProduct::create($item);
                //log
                activity('salesforce_api')
                    ->causedBy($clubAccessItem)
                    ->withProperties(['ClubAccessProduct' => $item])
                    ->log('ClubAccessProduct| create new Club Access Product #'.$item['Id']);
                $returnData[] = [
                    'success' => true,
                    'error' => "Create success",
                    'result' => $item['Id']
                ];
            }
        }

        return response()->json($returnData, 200);
    }

    public function deleteClubAccessProduct(Request $request){
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['ClubAccessProduct' => $item])
                ->log('Request | deleteClubAccessProduct - '.$request->path());


            $chkItem = ClubAccessProduct::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['Club' => $item])
                        ->log('Club| Delete Club success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete Club success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['Club' => $chkItem])
                        ->log($e->getMessage());

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

    public function createClubAppliedPromotion(Request $request) {
        $postData = $request->post();
        $returnData = [];

        foreach ($postData as $item) {
            //log request
            activity('salesforce_api')
                ->withProperties(['ClubAppliedPromotion' => $item])
                ->log('Request | createClubAppliedPromotion - '.$request->path());


            $clubAppliedPromotion = ClubAppliedPromotions::find($item['Id']);
            if (!empty($clubAppliedPromotion)) {
                DB::beginTransaction();
                try {
                    unset($item['Id']);
                    $clubAppliedPromotion->fill($item);
                    $clubAppliedPromotion->LastModifiedDate = date('Y-m-d H:i:s');
                    $clubAppliedPromotion->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['ClubAppliedPromotion' => $item])
                        ->log('ClubAppliedPromotion| Update Club Applied Promotion Item success #' . $clubAppliedPromotion->Id);
                    DB::commit();
                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update Club Applied Promotion Item success',
                        'result' => $clubAppliedPromotion->Id
                    ];
                } catch (\Exception $e) {
                    DB::rollBack();
                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $clubAppliedPromotion->Id
                    ];
                    continue;
                }
                continue;
            } else {
                //set date
                $this->setDefaultDate($item);
                //insert data
                $clubAppliedPromotionItem = ClubAppliedPromotions::create($item);
                //log
                activity('salesforce_api')
                    ->causedBy($clubAppliedPromotionItem)
                    ->withProperties(['ClubAppliedPromotion' => $item])
                    ->log('ClubAppliedPromotion| create new Applied Promotion Item #'.$item['Id']);
                $returnData[] = [
                    'success' => true,
                    'error' => "Create success",
                    'result' => $item['Id']
                ];
            }
        }

        return response()->json($returnData, 200);
    }

    public function deleteClubAppliedPromotion(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['ClubAppliedPromotion' => $item])
                ->log('Request | deleteClubAppliedPromotion - '.$request->path());


            $chkItem = ClubAppliedPromotions::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['ClubAppliedPromotions' => $item])
                        ->log('ClubAppliedPromotions| Delete Club Applied Promotion success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete Club Applied Promotion success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['ClubAppliedPromotions' => $chkItem])
                        ->log($e->getMessage());

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

    public function createClubAppliedPricebook(Request $request) {
        $postData = $request->post();
        $returnData = [];

        foreach ($postData as $item) {
            //log request
            activity('salesforce_api')
                ->withProperties(['ClubAppliedPricebook' => $item])
                ->log('Request | createClubAppliedPricebook - '.$request->path());


            $clubAppliedPricebook = ClubAppliedPricebook::find($item['Id']);
            if (!empty($clubAppliedPricebook)) {
                DB::beginTransaction();
                try {
                    unset($item['Id']);
                    $clubAppliedPricebook->fill($item);
                    $clubAppliedPricebook->LastModifiedDate = date('Y-m-d H:i:s');
                    $clubAppliedPricebook->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['ClubAppliedPricebook' => $item])
                        ->log('ClubAppliedPricebook| Update Club Applied Pricebook Item success #' . $clubAppliedPricebook->Id);
                    DB::commit();
                    $returnData[] = [
                        'success' => true,
                        'error' => 'Update Club Applied Pricebook Item success',
                        'result' => $clubAppliedPricebook->Id
                    ];
                } catch (\Exception $e) {
                    DB::rollBack();
                    $returnData[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'result' => $clubAppliedPricebook->Id
                    ];
                    continue;
                }
                continue;
            } else {
                //set date
                $this->setDefaultDate($item);
                //insert data
                $clubAppliedPricebookItem = ClubAppliedPricebook::create($item);
                //log
                activity('salesforce_api')
                    ->causedBy($clubAppliedPricebookItem)
                    ->withProperties(['ClubAppliedPricebook' => $item])
                    ->log('ClubAppliedPricebook| create new Applied Pricebook Item #'.$item['Id']);
                $returnData[] = [
                    'success' => true,
                    'error' => "Create success",
                    'result' => $item['Id']
                ];
            }
        }

        return response()->json($returnData, 200);
    }

    public function deleteClubAppliedPricebook(Request $request) {
        $postData = $request->post();
        $returnData = [];
        foreach ( $postData as $item ) {
            //log request
            activity('salesforce_api')
                ->withProperties(['ClubAppliedPricebook' => $item])
                ->log('Request | deleteClubAppliedPricebook - '.$request->path());


            $chkItem = ClubAppliedPricebook::find($item['Id']);
            if (!empty($chkItem)) {
                try {
                    $chkItem->IsDeleted = 1;
                    $chkItem->LastModifiedDate = date('Y-m-d H:i:s');
                    $chkItem->save();

                    //log
                    activity('salesforce_api')
                        ->withProperties(['ClubAppliedPricebook' => $item])
                        ->log('ClubAppliedPricebook| Delete Club Applied Pricebook success #' . $chkItem->Id);

                    $returnData[] = [
                        'success' => true,
                        'error' => 'Delete Club Applied Pricebook success',
                        'result' => $item['Id']
                    ];
                } catch (\Exception $e) {
                    //log
                    activity('salesforce_api')
                        ->withProperties(['ClubAppliedPricebook' => $chkItem])
                        ->log($e->getMessage());

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
