<?php
/**
 * @author tmtuan
 * created Date: 09-Mar-21
 */
namespace App\Domains\Crm\Http\Controllers\Api\Oppty;

use App\Domains\Crm\Models\ClubAppliedOpportunity;
use App\Domains\TmtSfObject\Classes\ApexRest;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;

class ClubAccessController extends OpptyController {

    public function listClubAccess($id, Request $request) {
        $user = auth()->user();
        //Son Add Role Sale,Pt
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);
        try {
            $responseData = ApexRest::get('app-api/v1/opportunity-club-access/'.$id);
            $content = $responseData->getBody()->getContents();
            $result = json_decode($content, true);

            if ( $result['success'] == false ) {
                $errors = implode('; ', $result['error']);
                return response()->json(['message' => $errors], 422);
            } else {
                return response()->json(['data' => $result['result']], 200);
            }

        } catch (ClientException $e) {
            $mess = $e->getResponse()->getBody()->getContents();
            $mess1 = json_decode($mess);
            return response()->json(['message' => $mess1[0]->message], 422);
        }
    }

    public function saveClubAccess(Request $request) {
        $postData = $request->post();
        $user = auth()->user();
        if ( !$user->hasRole(['Sale'])) return response()->json([
            'message' => 'Chỉ tài khoản Sale mới có quyền thực hiện hành động này'
        ], 401);

        $validator = \Validator::make($postData, [
            'oppty' => 'required',
            'club_ids' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => implode(",",$validator->messages()->all())],422);
        } else {
            try {
                $clubIds = array_filter(explode(';', $postData['club_ids']));
                $objectData = [
                    'opportunityId' => $postData['oppty'],
                    'clubIds' => $clubIds
                ];

                $responseData = ApexRest::post('app-api/v1/opportunity-club-access/', $objectData);
                $content = $responseData->getBody()->getContents();
                $result = json_decode($content, true);

                if ( $result['success'] == false ) {
                    $errors = implode('; ', $result['error']);
                    return response()->json(['message' => $errors, 'data' => $postData], 422);
                } else {
                    $newClubAccess = $result['result']['newClubAccess'];
                    if ( !empty($newClubAccess) ) {
                        foreach ( $newClubAccess as $club ) {
                            $newItem = new ClubAppliedOpportunity();
                            $newItem->fill($club);
                            $newItem->CreatedDate = date('Y-m-d H:i:s');
                            $newItem->IsDeleted = 0;
                            $newItem->save();

                            $clubAccessItem = ClubAppliedOpportunity::with('ClubInfo')->find($club['Id']);
                            $clubData[] = $clubAccessItem;
                            //log
                            activity('sale_pt_app')
                                ->causedBy($newItem)
                                ->withProperties(['ClubAppliedOpportunity' => $newItem->toArray()])
                                ->log('ClubAppliedOpportunity| create new Club Applied Opportunity  #'.$club['Id']);
                        }
                    } else {
                        $clubData = [];
                        foreach ( $clubIds as $item ) {
                            $clit = ClubAppliedOpportunity::with('ClubInfo')
                                    ->where('Club__c',$item)
                                    ->where('Opportunity__c',$postData['oppty'])
                                    ->where('IsDeleted', 0)
                                    ->get()->first();
                            $clubData[] = $clit;
                        }
                    }

                    return response()->json(empty(array_filter($clubData)) ? [] : $clubData, 200);
                }

            } catch (ClientException $e) {
                $mess = $e->getResponse()->getBody()->getContents();
                $mess1 = json_decode($mess);
                return response()->json(['message' => $mess1[0]->message], 422);
            }
        }
    }
}
