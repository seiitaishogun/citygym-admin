<?php
/**
 * @author tmtuan
 * created Date: 23-Dec-20
 */
namespace App\Domains\Crm\Http\Controllers\Api\Source;

use App\Domains\Crm\Models\Source;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;

class SourceController extends ApiController {
    public function listSource(Request $request) {
        $listSource = Source::where('IsDeleted', 0)->get();

        if ( empty($listSource->toArray()) ) return response()->json(['message' => 'No item found!'], 404);
        else return response()->json($listSource, 200);
    }
}
