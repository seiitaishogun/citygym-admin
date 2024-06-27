<?php
/**
 * @author tmtuan
 * created Date: 22-Dec-20
 */
namespace App\Domains\Crm\Http\Controllers\Api\SfClass;

use App\Domains\Crm\Models\ClassGroup;
use App\Domains\Crm\Models\SfClass;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;

class mbClassController extends ApiController {
    public function listClass(Request $request) {
        $class = SfClass::where('IsDeleted', 0)->orderBy('thutu')->get();
        if ( empty($class) ) return response()->json(['message' => 'No items Found'], 404);
        else return response()->json($class, 200);
    }

    public function getClass($id, Request $request) {
        $class = SfClass::with(array( 'Schedule' => function($query) {
                // $query->select('Id','Name', 'Class__c', 'Status__c', 'Start__c', 'End__c');
            }))->find($id, ['Id', 'Name']);

        if ( empty($class) ) return response()->json(['message' => 'No items Found'], 404);
        else return response()->json($class, 200);
    }

    public function listClassGroup(Request $request) {
        $group = ClassGroup::select(['Id', 'Name', 'Group_Code__c', 'Description__c', 'Club__c'])
                ->orderBy('LastModifiedDate', 'desc')
                ->where('IsDeleted', 0)->get();

        if ( empty($group) ) return response()->json(['message' => 'No items Found'], 404);
        else return response()->json($group, 200);
    }


}
