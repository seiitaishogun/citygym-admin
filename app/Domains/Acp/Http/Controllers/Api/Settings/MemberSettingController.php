<?php
/**
 * @author tmtuan
 * created Date: 20-Jan-21
 */
namespace App\Domains\Acp\Http\Controllers\Api\Settings;

use App\Models\Settings;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;

class MemberSettingController extends ApiController {
    public function listBtnSettings(Request $request) {
        $input = $request->all();

        $stData = Settings::where('group', 'member_app')
            ->where('item', 'btn_iframe')
            ->get()->first();
        $data = json_decode($stData->value);
        $data = array_values((array) $data);
        if ( empty($stData) ) return response()->json(['message' => 'No iten Found!'], 404);


        else return response()->json($data,200);
    }

    public function listMemoSettings(Request $request)
    {
        $input = $request->all();

        $stData = Settings::where('group', 'member_app')
            ->where('item', 'greeting_memo')
            ->get()->first();
        if ( empty($stData) ) return response()->json(['message' => 'No iten Found!'], 404);


        else return response()->json(json_decode($stData->value),200);
    }

    public function listContactSettings(Request $request)
    {
        $stData = Settings::where('group', 'member_app')
            ->where('item', 'contact')
            ->get()->first();
        if ( empty($stData) ) return response()->json(['message' => 'No iten Found!'], 404);


        else return response()->json(json_decode($stData->value),200);
    }
}
