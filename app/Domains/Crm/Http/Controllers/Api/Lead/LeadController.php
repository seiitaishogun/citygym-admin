<?php
/**
 * @author tmtuan
 * created Date: 01-Dec-20
 */
namespace App\Domains\Crm\Http\Controllers\Api\Lead;

use App\Domains\Crm\Models\Lead;
use App\Domains\TmtSfObject\Classes\ApexRest;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;


class LeadController extends ApiController {
    public $status = [ 'New',
        'Assigned Club',
        'Assigned TL',
        'Assigned Staff',
        'Consult',
        'Consider',
        'Appt',
        'Lost',
        'Converted'];

        public function listLeadWithPaginate(Request $request) { // create 01/04/2021
            $user = auth()->user();
            if ( !$user->hasRole(['Sale'])) return response()->json([
                'message' => 'You don\' have permission on this action'
            ], 401);
            $per_page = $input['per_page'] ?? 15;
            $leadData = Lead::with(['StaffAssign' => function($qr){
                $qr->select(['Id', 'Name']);
            }])
            ->where('IsDeleted', 0)
                            ->where('Staff_Assignment__c', $user->sf_account_id());
            $input = $request->query();
            if(isset($input['startDate']) || isset($input['endDate'])){
                $from = empty($input['startDate']) ? date('Y-m-d H:i:s',strtotime($input['endDate'].'last month')) : date('Y-m-d H:i:s',strtotime($input['startDate']));
                $to = empty($input['endDate']) ? date('Y-m-d H:i:s',strtotime($input['startDate'].'next month')) : date('Y-m-d H:i:s',strtotime($input['endDate'].' 23 hours 59 minutes 59 seconds'));
            //            print_r($to);
                $leadData->whereBetween('CreatedDate', [$from, $to]);
            }
            if(isset($input['search'])){
                $searchKey = $input['search'];
                $leadData->where(function ($query) use ($searchKey) {
                        $query->where('Name', 'like', '%'.$searchKey.'%')
                              ->orWhere('MobilePhone', 'like', '%'.$searchKey.'%');
                            });
            }
            if(isset($input['stage']) && $input['stage'] != 'All'){
                $leadData->where('Status', $input['stage']);
            }
            if(isset($input['source'])){
                $leadData->where('Source__c', $input['source']);
            }
            $leadData->orderBy('CreatedDate','desc');

            $data = $leadData->paginate($per_page);
            if($data){
            return response()->json($data, 200);
            } else {
                return response()->json([], 404);
            }
        }

        public function listLead(Request $request) {
            $user = auth()->user();
            if ( !$user->hasRole(['Sale'])) return response()->json([
                'message' => 'You don\' have permission on this action'
            ], 401);

            $leadData = Lead::with(['StaffAssign' => function($qr){
                $qr->select(['Id', 'Name']);
            }])
            ->where('IsDeleted', 0)
                            ->where('Staff_Assignment__c', $user->sf_account_id());
            $input = $request->query();
            if(isset($input['startDate']) || isset($input['endDate'])){
                $from = empty($input['startDate']) ? date('Y-m-d H:i:s',strtotime($input['endDate'].'last month')) : date('Y-m-d H:i:s',strtotime($input['startDate']));
                $to = empty($input['endDate']) ? date('Y-m-d H:i:s',strtotime($input['startDate'].'next month')) : date('Y-m-d H:i:s',strtotime($input['endDate'].' 23 hours 59 minutes 59 seconds'));
            //            print_r($to);
                $leadData->whereBetween('CreatedDate', [$from, $to]);
            }
            if(isset($input['search'])){
                $searchKey = $input['search'];
                $leadData->where(function ($query) use ($searchKey) {
                        $query->where('Name', 'like', '%'.$searchKey.'%')
                              ->orWhere('MobilePhone', 'like', '%'.$searchKey.'%');
                            });
            }
            if(isset($input['stage']) && $input['stage'] != 'All'){
                $leadData->where('Status', $input['stage']);
            }
            if(isset($input['source'])){
                $leadData->where('Source__c', $input['source']);
            }
            $leadData->orderBy('CreatedDate','desc');
            if($leadData->get()){
                return response()->json($leadData->get(), 200);
            } else {
                return response()->json([], 404);
            }
        }

    public function countStatusLead(Request $request) {
        $user = auth()->user();
        if ( !$user->hasRole(['Sale'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);

        $leadData = Lead::where('Staff_Assignment__c', $user->sf_account_id())->where('IsDeleted', 0);
        $leadData->select('Status', \DB::raw('count(*) as countStatus'))->groupBy('Status');
        if($leadData->get()){
            return response()->json($leadData->get(), 200);
        } else {
            return response()->json([], 404);
        }
    }

    public function listLostReason() {
        $reason = [
            'Xa CLB hơn 5km',
            'Dưới 18 tuổi',
            'Không thể liên lạc nhiều lần',
            'Nhầm số - Không nhu cầu',
            'Không đăng ký - Không nhu cầu',
            'Thuê bao sai'
        ];
        return response()->json($reason, 200);
    }

    /**
     * [Oct-08-2021] - Convert Lead Corporate to Oppty
     * Task [ https://beunik.atlassian.net/browse/CIT-633?focusedCommentId=44479 ]
     * @param $id
     * @return mixed
     */
    public function convertLead($id)
    {
        $user = auth()->user();
        if ( !$user->hasRole(['Sale'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);

        $leadItem = Lead::find($id);

        /**
         * Thực hiện gọi API lên SF để convert Lead
         * https://app.swaggerhub.com/apis/Raca/CITIGYM_Salesforce_API/1.0.0?loggedInWithGitHub=true#/Lead/convertLead
         */
        $recordData = [
            'leadId' => $id
        ];

        try {
            $response = ApexRest::post('app-api/v1/convert-lead', $recordData);
            $respBody = json_decode($response->getBody()->getContents());
            if ( $respBody->success == 'true' ) {

                //log
                activity('salesforce_api')
                    ->log('Lead| Convert Lead Success #' . $id);

                $returnData = [
                    'success' => true,
                    'result' => $respBody->result,
                    'message' => 'Convert Lead thành công!'
                ];
                return response()->json($returnData, 200);
            } else {
                $errs = $respBody->error;
                $resErr = ( is_array($errs) ) ? implode(',', $errs) : $errs;

                return response()->json(['success' => false, 'message' => $resErr], $response->getStatusCode());
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

    }

}
