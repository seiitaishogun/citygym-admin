<?php
/**
 * @author tmtuan
 * created Date: 03-Dec-20
 */
namespace App\Domains\TmtSfObject\Http\Controllers\Backend;

use App\Domains\Auth\Models\User;
use App\Domains\TmtSfObject\Classes\SObject;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Domains\TmtSfObject\Models\SalesforceModel as sfModel;
use Illuminate\Support\Facades\Artisan;

class SalesforceGetObject extends Controller {
    public function getObjectTable() {
        Artisan::call('view:clear');
        $sf_tbl = ['Schedule__c'];
        $data = [];

        return view('backend.sfObject.get_object_table', ['data' => $data]);

    }

    public function getObjectAction(Request $request) {
        Artisan::call('view:clear');
        $postData = $request->post();
        if ( empty($postData['object']) ) return redirect()->back()->withErrors(__('sfObject.empty_object_name'));

        $objects = array_filter(explode('|', $postData['object']));

        $data = [];
        if ( isset($postData['is_display']) && $postData['is_display'] == 1) {
            foreach ($objects as $item) {
                try {
                    $objectDescribeResult = SObject::describe($item);
                } catch (\Exception $e) {
                    return redirect()->back()->withErrors($e->getMessage());
                }

                if ($objectDescribeResult) {
                    $data['result'][$item] = $objectDescribeResult['fields'];
                }
            }
        } else {
            foreach ($objects as $item) {
                try {
                    $objectDescribeResult = SObject::describe($item);
                } catch (\Exception $e) {
                    return redirect()->back()->withErrors($e->getMessage());
                }

                if ( !Schema::hasTable('salesforce_'.$item) ) { //checking for table exist
                    Schema::create('salesforce_'.$item, function ($table) use ($item) {
                        $currbl = 'salesforce_'.$item;
                        $table->engine = 'InnoDB';

                        sfModel::tmtAutoCloneSalesforceSchema($table, $currbl, $item);

                    });
                    $data['result'][] = "<p>Đã tạo table {$item} thành công!</p>";
                }
            }
        }
        return view('backend.sfObject.get_object_table', ['data' => $data]);
    }

}
