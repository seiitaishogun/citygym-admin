<?php
/**
 * @author tmtuan
 * created Date: 10/11/2021
 * project: citygym-admin
 */
namespace App\Domains\Crm\Http\Controllers\Api\Oppty;

use App\Domains\TmtSfObject\Classes\ApexRest;

class ProductCorporateController extends OpptyController {
    public function listCorpData($id)
    {
        try {
            $responseData = ApexRest::get('app-api/v1/opportunity-add-corporate-product/'.$id);
            $content = $responseData->getBody()->getContents();
            $result = json_decode($content, true);

            if ( $result['success'] == false ) {
                $errors = implode('; ', $result['error']);
                return response()->json(['message' => $errors], 422);
            } else {
                return response()->json($result['result'], 200);
            }

        } catch (ClientException $e) {
            $mess = $e->getResponse()->getBody()->getContents();
            $mess1 = json_decode($mess);
            return response()->json(['message' => $mess1[0]->message], 422);
        }
    }
}
