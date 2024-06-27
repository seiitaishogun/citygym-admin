<?php
/**
 * @author tmtuan
 * created Date: 03-Mar-21
 */
namespace App\Domains\Acp\Http\Controllers\Backend\Settings;

use App\Models\Settings;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class EmailSettingController extends Controller {
    public function emailSetting(Request $request) {
        Artisan::call('view:clear');
        $settings = Settings::where('group', 'system_cf')
            ->where('item', 'email_settings')
            ->get()->first();

        $item_id = 0;
        if ( !empty($settings) ) {
            $data['email_settings'] = json_decode($settings->value);
            $item_id = $settings->id;
        } else {
            $settings = [
                'host' => 'smtp.googlemail.com',
                'port' => 465,
                'encryption' => 'ssl',
                'username' => '',
                'password' => ''
            ];
            $data['email_settings'] = (object) $settings;
        }

        return view('backend.settings.email_setting', ['email_settings' => $data['email_settings'], 'id' => $item_id]);

    }

    public function saveEmailSetting(Request $request) {
        $postData = $request->post();

        $validator = Validator::make($postData, [
            'host' => 'required',
            'username' => 'required|email',
            'password' => 'required',
            'port' => 'required',
            'encryption' => 'required',
        ], [
            'host.required' => 'Vui lòng nhập địa chỉ host',
            'username.required' => 'Vui lòng nhập email',
            'username.email' => 'Email không đúng định dạng',
            'password.required' => 'Vui lòng nhập mật khẩu email',
            'port.required' => 'Vui lòng nhập port',
            'encryption.required' => 'Vui lòng nhập port',
        ]);

        if ($validator->fails()) {
            return redirect('admin/config-email')
                ->withErrors($validator)
                ->withInput();
        }

        //save setting to DB
        $settings = [
            'host' => $postData['host'],
            'port' => $postData['port']??465,
            'encryption' => $postData['encryption']??'ssl',
            'username' => $postData['username'],
            'password' => $postData['password']
        ];

        if ( isset($postData['id']) && $postData['id'] > 0 ) {
            $item = Settings::find($postData['id']);
            $item->value = json_encode((object) $settings);
            $item->update();
            //log
            activity('System Config')
                ->causedBy($item)
                ->withProperties(['email_config' => $postData])
                ->log('email_config| Update email config success');
        } else {
            Settings::create([
                'group' => 'system_cf',
                'item' => 'email_settings',
                'value' => json_encode((object) $settings)
            ]);

            //log
            activity('System Config')
                ->causedBy($postData)
                ->withProperties(['email_config' => $postData])
                ->log('email_config| Create email config success');
        }
        return redirect()->back()->withFlashSuccess(__('setting.email_st_save_success'));
    }

    public function sentTestMail(Request $request) {

        $to_name = 'Test';
        $to_email = $request->query('email') ?? 'sloveoflove@yahoo.com';

        try {
            Mail::send([],[], function($message) use ($to_name, $to_email) {
                $message->to($to_email, $to_name)->subject('Your New Test Email')
                    ->setBody('Hi, welcome user!');
                $message->from('tuan.tran@itk.com.vn', 'TEst');
            });

            return "Mail sent!";
        } catch (\Exception $e) {
            dd($e);
        }

    }
}
