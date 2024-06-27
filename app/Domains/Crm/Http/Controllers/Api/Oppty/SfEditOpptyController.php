<?php
/**
 * @author tmtuan
 * created Date: 11-Jan-21
 */
namespace App\Domains\Crm\Http\Controllers\Api\Oppty;

use App\Domains\Crm\Models\Opportunity;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SfEditOpptyController extends SfOpptyController {
    public function updateOppty($id, Request $request ) {
        $postData = $request->all();
        $returnData = [];

        //log request
        activity('salesforce_api')
            ->withProperties(['Oppty' => $postData])
            ->log('Request | updateOppty - '.$request->path());

        $item = Opportunity::find($id);
        if ( empty($item) ) $returnData = [
            'success' => false,
            'error' => 'No item found',
            'result' => $id
        ];
        else {
            if ( isset($postData['Id'])) unset($postData['id']);
            if ( isset($postData['CreatedDate'])) unset($postData['CreatedDate']);

            $item->update($postData);
            $item->LastModifiedDate = Carbon::now();
            $item->save();
            $returnData = [
                'success' => true,
                'error' => 'Update success',
                'result' => $id
            ];

            //log
            activity('salesforce_api')
                ->withProperties(['oppty' => $postData])
                ->log('Oppty| update oppty success #' . $id);
        }
        return response()->json($returnData, 200);
    }

}
