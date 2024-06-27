<?php
/**
 * @author tmtuan
 * created Date: 20-Jan-21
 */
namespace App\Domains\Acp\Http\Controllers\Backend\Settings;

use App\Models\Settings;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ButtonSettingController extends Controller {

    public function ButtonAppSettings(Request $request) {
        Artisan::call('view:clear');
        $stData = Settings::where('group', 'member_app')
                    ->where('item', 'btn_iframe')
                    ->get()->first();
        if ( empty($stData) ) return redirect()->route('admin.member-app-config.addNewBtnIframe')->withErrors(__('setting.empty_iframe_setting_data'));

        return view('backend.settings.button.mb_btn_setting', ['btnData' => json_decode($stData->value)]);
    }

    public function AddNewBtnIframe(Request $request) {
        Artisan::call('view:clear');
        $stData = Settings::where('group', 'member_app')
                    ->where('item', 'btn_iframe')
                    ->get()->first();
        if ( empty($stData) ) {
            $newSt = [
                'group' => 'member_app',
                'item' => 'btn_iframe',
            ];
            Settings::create($newSt);
        }
        return view('backend.settings.button.create_btn');
    }

    public function AddNewBtnIframeAction(Request $request) {
        $postData = $request->post();

        $validator = \Validator::make($postData, [
            'name' => 'required',
            'url' => 'required',
        ],
            );
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->messages());
        } else {
            $stData = Settings::where('group', 'member_app')
                ->where('item', 'btn_iframe')
                ->get()->first();
            if ( empty($stData->value) ) {
                $cfItems[] = [
                    'title' => $postData['title'],
                    'api_name' => $postData['name'],
                    'iframe_url' => $postData['url']
                ];
            } else {
                $cfItems = !is_array($stData->value) ? json_decode($stData->value) : $stData->value;
                //check api key exist
                foreach ($cfItems as $item) {
                    if ( $item->api_name == $postData['name'] ) return redirect()->back()->withErrors(__('setting.btn_key_exist'));
                }

                $cfItems[] = [
                    'title' => $postData['title'],
                    'api_name' => $postData['name'],
                    'iframe_url' => $postData['url']
                ];
            }

            $stData->value = json_encode($cfItems);
            $stData->save();
            return redirect()->route('admin.member-app-config.mbAppCf')->withFlashSuccess(__('setting.btn_iframe_create_success'));
        }

    }

    public function editBtnIframe($id) {
        Artisan::call('view:clear');

        $stData = Settings::where('group', 'member_app')
            ->where('item', 'btn_iframe')
            ->get()->first();
        if ( empty($stData) ) return redirect()->back()->withErrors(__('setting.empty_iframe_setting_data'));
        $btncfData = !is_array($stData->value) ? json_decode($stData->value) : $stData->value;
        foreach ( $btncfData as $item ) {
            if ( $item->api_name == $id ) $itemData = $item;
        }
        if ( !isset($itemData) || empty($itemData) ) return redirect()->back()->withErrors(__('setting.no_iframe_setting_found'));
        return view('backend.settings.button.edit_btn', ['item' => $itemData]);
    }

    public function EditBtnIframeAction($id, Request $request) {
        $postData = $request->post();

        $validator = \Validator::make($postData, [
            'url' => 'required',
        ],
            );
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->messages());
        } else {
            $stData = Settings::where('group', 'member_app')
                ->where('item', 'btn_iframe')
                ->get()->first();
            if ( empty($stData->value) ) {
                $cfItems[] = [
                    'title' => $postData['title'],
                    'api_name' => $postData['name'],
                    'iframe_url' => $postData['url']
                ];
            } else {
                $cfItems = !is_array($stData->value) ? json_decode($stData->value) : $stData->value;
                //check api key exist
                foreach ($cfItems as $item) {
                    if ( $item->api_name == $id ) {
                        $item->title = $postData['title'];
//                        $item->api_name = $postData['name'];
                        $item->iframe_url = $postData['url'];
                    }
                }

            }

            $stData->value = json_encode($cfItems);
            $stData->save();
            return redirect()->route('admin.member-app-config.mbAppCf')->withFlashSuccess(__('setting.btn_iframe_create_success'));
        }

    }

    public function deleteBtnIframe($id) {
        $stData = Settings::where('group', 'member_app')
            ->where('item', 'btn_iframe')
            ->get()->first();
        if ( empty($stData) ) return redirect()->back()->withErrors(__('setting.empty_iframe_setting_data'));

        $cfData = !is_array($stData->value) ? json_decode($stData->value, true) : $stData->value;

        foreach ( $cfData as $key => $item ) {
            if ( $item['api_name'] == $id ) unset($cfData[$key]);
        }
        $stData->value = json_encode($cfData);
        $stData->save();
        return redirect()->back()->withErrors(__('setting.delete_button_success'));
    }
}
