<?php
/**
 * @author tmtuan
 * created Date: 22-Apr-21
 */

namespace App\Domains\Crm\Http\Controllers\Backend\SfClass;

use App\Domains\Crm\Models\SfClass;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClassController extends Controller {

    public function order() {
        $class = SfClass::with(['group' => function($qr){
            $qr->select(['Id', 'Name']);
        }])
                ->where('IsDeleted', 0)
                ->orderBy('thutu')->get();
        return view('backend.salesforce.sfclass.order', ['classes' => $class]);
    }

    public function orderAction(Request $request) {
        $postData = $request->post();

        if ( !empty($postData['id']) ) {
            foreach ( $postData['id'] as $pos => $id) {
                $classData = SfClass::where('Id', $id)->first();
                if ( isset($classData->Id) ) {
                    $classData->thutu = $postData['thutu'][$pos];
                    $classData->save();
                }

            }
            return redirect()->back()->withFlashSuccess(__('crm.update_order_success'));
        } else return redirect()->back();
    }

}
