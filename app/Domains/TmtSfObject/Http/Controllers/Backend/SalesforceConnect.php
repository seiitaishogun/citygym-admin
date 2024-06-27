<?php
/**
 * @author tmtuan
 * created Date: 03-Dec-20
 */

namespace App\Domains\TmtSfObject\Http\Controllers\Backend;

use App\Domains\TmtSfObject\Classes\Authorization;
use App\Domains\TmtSfObject\Classes\Constant;
use App\Http\Controllers\Controller;
use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SalesforceConnect extends Controller {

    public function connect() {
//        Artisan::call('view:clear');

        $data = [];
        $settings = Settings::where('group', 'system_cf')->where('item', Constant::SALESFORCE_SETTING_CODE)->first();
        $item_id = 0;
        if ( !empty($settings) ) {
            $data['api_settings'] = json_decode($settings->value);
            $item_id = $settings->id;
        } else {
            $settings = [
                'api_user' => '',
                'api_password' => '',
                'security_token' => '',
                'sms_api_token' => '',
                'client_id' => '',
                'client_secret' => '',
                'is_sandbox' => 0,
                'refresh_token' => ''
            ];
            $data['api_settings'] = (object) $settings;
        }

        return view('backend.sfObject.connect', ['api_settings' => $data['api_settings'], 'id' => $item_id]);
    }


    public function connectAction(Request $request) {
        $postData = $request->post();

        if ( empty($postData['client_id']) && empty($postData['client_secret']) ) return redirect()->back()->withErrors( __('sfObject.empty_api_data'));
        $settings = [
            'api_user' => $postData['api_user'],
            'api_password' => $postData['api_password'],
            'security_token' => $postData['security_token'],
            'sms_api_token' => '',
            'client_id' => $postData['client_id'],
            'client_secret' => $postData['client_secret'],
            'is_sandbox' => $postData['is_sandbox']??0,
            'refresh_token' => ''
        ];

        //save setting to DB
        if ( isset($postData['id']) && $postData['id'] > 0 ) {
            $item = Settings::find($postData['id']);
            $item->value = json_encode((object) $settings);
            $item->update();
        } else {
            Settings::create([
                'item' => Constant::SALESFORCE_SETTING_CODE,
                'value' => json_encode((object) $settings)
            ]);
        }

        return redirect()->back()->withFlashSuccess(__('sfObject.api_data_save_success'));
    }

    public function onConnect() {
        $settingData = Settings::where('item', Constant::SALESFORCE_SETTING_CODE)->first();
        $oauthRedirect = Authorization::buildAuthorizationCodeUrl(json_decode($settingData->value));

        return redirect()->to($oauthRedirect);
    }

    public function handleOAuthCallback(Request $request) {
        $code = $request->query('code');
        $settingData = Settings::where('item', Constant::SALESFORCE_SETTING_CODE)->first();
        $setting = json_decode($settingData->value);

        $result = Authorization::authenticate($setting->client_id, $setting->client_secret, $code, Constant::GRANT_TYPE_CODE, $setting->is_sandbox);
        if($result){
            Cache::forget(Authorization::TOKEN_CACHE_KEY);

            $setting->refresh_token = $result->refreshToken;
            $setting->access_token = $result->accessToken;

            //save Setting to database
            $settingData->value = json_encode((object) $setting);
            $settingData->update();
        }

        return redirect()->route('admin.sf-object.connect');
    }
}
