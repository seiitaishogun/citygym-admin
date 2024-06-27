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

class ContactController extends Controller {

    public function contactSettings(Request $request) {
        Artisan::call('view:clear');
        $stData = Settings::where('group', 'member_app')
                    ->where('item', 'contact')
                    ->get()->first();
        if ( empty($stData) ) {
            $newSt = [
                'group' => 'member_app',
                'item' => 'contact',
                'value' => json_encode([])
            ];
            Settings::create($newSt);
            $stData = (object) $newSt;
        }

        return view('backend.settings.contact.contact_setting', ['data' => json_decode($stData->value)]);
    }

    public function AddContact(Request $request) {
        Artisan::call('view:clear');
        $stData = Settings::where('group', 'member_app')
                    ->where('item', 'contact')
                    ->get()->first();
        if ( empty($stData) ) {
            $newSt = [
                'group' => 'member_app',
                'item' => 'contact',
                'value' => json_encode([])
            ];
            Settings::create($newSt);
        }
        return view('backend.settings.contact.create_contact');
    }

    public function AddContactAction(Request $request) {
        $postData = $request->post();

        $validator = \Validator::make($postData, [
            'content' => 'required',
        ],
            );
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->messages());
        } else {
            $stData = Settings::where('group', 'member_app')
                ->where('item', 'contact')
                ->get()->first();
            if ( empty($stData->value) ) {
                $cfItems[] = [
                    'title' => $postData['title'],
                    'api_name' => $postData['name'],
                    'content' => $postData['content']
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
                    'content' => $postData['content']
                ];
            }

            $stData->value = json_encode($cfItems);
            $stData->save();
            return redirect()->route('admin.member-app-config.contactSetting')->withFlashSuccess(__('setting.add_contact_success'));
        }

    }

    public function edit($id) {
        Artisan::call('view:clear');

        $stData = Settings::where('group', 'member_app')
            ->where('item', 'contact')
            ->get()->first();
        if ( empty($stData) ) return redirect()->back()->withErrors(__('setting.empty_memo_data'));

        $cfData = !is_array($stData->value) ? json_decode($stData->value) : $stData->value;

        foreach ( $cfData as $item ) {
            if ( $item->api_name == $id ) $itemData = $item;
        }
        if ( !isset($itemData) || empty($itemData) ) return redirect()->back()->withErrors(__('setting.no_memo_found'));
        return view('backend.settings.contact.edit_contact', ['item' => $itemData]);
    }

    public function editAction($id, Request $request) {
        $postData = $request->post();
        $user = auth()->user();

        $validator = \Validator::make($postData, [
            'content' => 'required',
        ],
            );
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->messages());
        } else {
            $stData = Settings::where('group', 'member_app')
                ->where('item', 'contact')
                ->get()->first();
            if ( empty($stData->value) ) {
                $cfItems[] = [
                    'title' => $postData['title'],
                    'api_name' => $postData['name'],
                    'content' => $postData['content']
                ];
            } else {
                $cfItems = !is_array($stData->value) ? json_decode($stData->value) : $stData->value;
                //check api key exist
                foreach ($cfItems as $item) {
                    if ( $item->api_name == $id ) {
                        $item->title = $postData['title'];
                        if ( $user->id == 1 ) $item->api_name = $postData['name'];
                        $item->content = $postData['content'];
                    }
                }

            }

            $stData->value = json_encode($cfItems);
            $stData->save();
            return redirect()->route('admin.member-app-config.contactSetting')->withFlashSuccess(__('setting.edit_contact_success'));
        }

    }

    public function delete($id) {
        $stData = Settings::where('group', 'member_app')
            ->where('item', 'contact')
            ->get()->first();

        if ( empty($stData) ) return redirect()->back()->withErrors(__('setting.empty_memo_data'));

        $cfData = !is_array($stData->value) ? json_decode($stData->value) : $stData->value;

        foreach ( $cfData as $key => $item ) {
            if ( $item->api_name == $id ) unset($cfData[$key]);
        }
        $stData->value = json_encode($cfData);
        $stData->save();
        return redirect()->back()->withErrors(__('setting.delete_button_success'));
    }
}
