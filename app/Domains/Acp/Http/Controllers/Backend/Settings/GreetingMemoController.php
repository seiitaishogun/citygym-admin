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

class GreetingMemoController extends Controller {

    public function memoSettings(Request $request) {
        Artisan::call('view:clear');
        $stData = Settings::where('group', 'member_app')
                    ->where('item', 'greeting_memo')
                    ->get()->first();
        if ( empty($stData) ) {
            $newSt = [
                'group' => 'member_app',
                'item' => 'greeting_memo',
                'value' => json_encode([])
            ];
            Settings::create($newSt);
            $stData = (object) $newSt;
        }

        return view('backend.settings.memo.memo_setting', ['memoData' => json_decode($stData->value)]);
    }

    public function AddNewMemo(Request $request) {
        Artisan::call('view:clear');
        $stData = Settings::where('group', 'member_app')
                    ->where('item', 'greeting_memo')
                    ->get()->first();
        if ( empty($stData) ) {
            $newSt = [
                'group' => 'member_app',
                'item' => 'greeting_memo',
                'value' => json_encode([])
            ];
            Settings::create($newSt);
        }
        return view('backend.settings.memo.create_memo');
    }

    public function AddNewMemoAction(Request $request) {
        $postData = $request->post();

        $validator = \Validator::make($postData, [
            'content' => 'required',
        ],
            );
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->messages());
        } else {
            $stData = Settings::where('group', 'member_app')
                ->where('item', 'greeting_memo')
                ->get()->first();
            if ( empty($stData->value) ) {
                $memoItems[] = $postData['content'];
            } else {
                $memoItems = json_decode($stData->value);
                array_push($memoItems, $postData['content']);
            }

            $stData->value = json_encode($memoItems);
            $stData->save();
            return redirect()->route('admin.member-app-config.memoSetting')->withFlashSuccess(__('setting.add_memo_success'));
        }

    }

    public function editMemo($id) {
        Artisan::call('view:clear');

        $stData = Settings::where('group', 'member_app')
            ->where('item', 'greeting_memo')
            ->get()->first();
        if ( empty($stData) ) return redirect()->back()->withErrors(__('setting.empty_memo_data'));

        $memoData = !is_array($stData->value) ? json_decode($stData->value) : $stData->value;


        if ( empty($memoData[$id]) ) return redirect()->back()->withErrors(__('setting.no_memo_found'));
        $itemData = [
            'id' => $id,
            'content' => $memoData[$id]
        ];

        return view('backend.settings.memo.edit_memo', ['item' => $itemData]);
    }

    public function editMemoAction($id, Request $request) {
        $postData = $request->post();

        $validator = \Validator::make($postData, [
            'content' => 'required',
        ],
            );
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->messages());
        } else {
            $stData = Settings::where('group', 'member_app')
                ->where('item', 'greeting_memo')
                ->get()->first();
            if ( empty($stData->value) ) {
                $memoItems[] = $postData['content'];
            } else {
                $memoItems = json_decode($stData->value);
                $memoItems[$id] = $postData['content'];

            }

            $stData->value = json_encode($memoItems);
            $stData->save();
            return redirect()->route('admin.member-app-config.memoSetting')->withFlashSuccess(__('setting.edit_memo_success'));
        }

    }

    public function deleteMemo($id) {
        $stData = Settings::where('group', 'member_app')
            ->where('item', 'greeting_memo')
            ->get()->first();

        if ( empty($stData) ) return redirect()->back()->withErrors(__('setting.empty_memo_data'));

        $memoData = !is_array($stData->value) ? json_decode($stData->value) : $stData->value;
        exit('Noactionallow');
        if ( !isset($itemData) || empty($itemData) ) return redirect()->back()->withErrors(__('setting.no_iframe_setting_found'));
        dd($itemData);
    }
}
