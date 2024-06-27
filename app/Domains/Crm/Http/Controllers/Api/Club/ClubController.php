<?php
/**
 * @author tmtuan
 * created Date: 08-Jan-21
 */
namespace App\Domains\Crm\Http\Controllers\Api\Club;

use App\Domains\Crm\Models\Club;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;

class ClubController extends ApiController {

    /**
     * Oct-03-2021 - Task [ https://beunik.atlassian.net/browse/CIOS-192 ]
     * @param Request $request
     * @return mixed
     */
    public function listClubs(Request $request) {
        $input = $request->query();
        $per_page = $queryData['per_page'] ?? 15;

        $query = Club::select(['Id', 'Name', 'Club_Address__c', 'Club_Phone__c', 'Email__c', 'Tax_Code__c', 'Company_Name__c', 'Allocation_Number__c'])
            ->where('IsDeleted', 0)
            ->where('Hide_In_Print_View__c', 0);

        if ( isset($input['Is_TM']) ) $query->where('Is_TM__c', $input['Is_TM']);
        else $query->where('Is_TM__c', 0);

        /**
         * Oct-03-2021 - add condition Is_B2B__c
         */
        if ( isset($input['Is_B2B__c']) ) $query->where('Is_B2B__c', $input['Is_B2B__c']);
        else $query->where('Is_B2B__c', 0);

        if($result = $query->paginate($per_page)){
            return response()->json($result, 200);
        }
        return response()->json(['message' => 'No item found'], 404);
    }
}
