<?php
/**
 * @author tmtuan
 * created Date: 01-Dec-20
 */

namespace App\Domains\Crm\Http\Controllers\Api\Users;

use App\Domains\Auth\Models\Member;
use App\Domains\Auth\Models\User;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use App\Domains\TmtSfObject\Classes\SObject;
use Carbon\Carbon;
use App\Domains\TmtSfObject\Classes\ApexRest;

class UserController extends ApiController{

    public function updateProfile(Request $request) {
        $user = auth()->user();

        if ( empty($user) ) return response()->json([
//            'status' => false,
            'message' => 'Invalid Request'
        ], 400);

        $postData = $request->post();

        //update data
        $user->fill($postData);
        if ( isset($postData['full_name']) && !empty($postData['full_name']) ) {
            $user->name = $postData['full_name'];
        }
        if ( isset($postData['dob']) && (!empty($postData['dob']) || $postData['dob'] !== '') ) {
            $user->dob = date('Y-m-d', strtotime(str_replace('/', '-', $postData['dob'])));
        }
        if ( isset($postData['club_id']) && !empty($postData['club_id'])) {
            $user->club_id = $postData['club_id'];
        }
        $user->updated_at = now();

        //update SF Account
        //check SF Account
        if ( $user->sf_account_id() ) {
            $soUser = SObject::query("Select id,Name from Account where Id = '{$user->sf_account_id()}'");
            if ( !empty($soUser) ) {
                if ( $postData['gender'] == 1 ) $gender = 'Male';
                else if ( $postData['gender'] == 0 ) $gender = 'Female';
                else $gender = '';

                if ( isset($postData['FirstName']) && isset($postData['LastName']) ) {
                    $fName = $postData['FirstName'];
                    $lName = $postData['LastName'];
                } else {
                    $tmpName = explode(' ', $postData['full_name']);
                    $fName = array_pop($tmpName);
                    unset($tmpName[$fName]);
                    $lName = implode(' ', $tmpName);
                }

                $sfUpdateData = [
//            "Name"=> "tmtuan TEst",
                    "LastName"=> $lName,
                    "FirstName"=> $fName,
                    "DOB__c"=> date('Y-m-d', strtotime(str_replace('/', '-', $postData['dob']))),
                    "Gender__c"=> $gender,
                    "PersonMobilePhone"=> $postData['phone']??$user->phone,
                    "Pref_Mobile__c"=> $postData['phone']??$user->phone,
//            "Job_Title__c"=> "Sale",
//            "Title__c"=> "Mr sale man",
//            "Street__pc"=> "string",
//            "Country__pc"=> "string",
//            "District__pc"=> "string",
//            "Province__pc"=> "string",
//            "Ward__pc"=> "string"
                ];
                SObject::update('Account', $user->sf_account_id(), $sfUpdateData);
            }
        }
        $user->update();
        $newUserData = User::find($user->id);

        return response()->json([
//            'status' => true,
            'message' => __('apiAuth.update_user_success'),
            'user' => $newUserData
        ], 200);

    }

    public function updateDevice(Request $request) {
        $postData = $request->post();

        $validator = \Validator::make($postData, [
            'user_id' => 'required',
            'type' => 'required',
            'device_token' => 'required',
            'device_type' => 'required'
        ],
        [
            'user_id.required' => 'Vui lòng nhập user_id',
            'type.required' => 'Vui lòng nhập type',
            'device_token.required' => 'Vui lòng nhập device_token',
            'device_type.required' => 'Vui lòng nhập device_type',
        ]
        );
        if ($validator->fails())
        {
            $messages = implode(";",$validator->messages()->all());
            return response()->json([
                'message' => $messages
            ], 422);
        }

        switch ($postData['type']) {
            case 'user':
                $user = User::find($postData['user_id']);
                if ( !isset($user->id) ) return response()->json([
                    'message' => 'Invalid user'
                ], 404);
                else {
                    if ( $user->device_token != $postData['device_token'] ) {
                        $user->device_token = $postData['device_token'] ?? '';
                        $user->device_type = $postData['device_type'] ?? '';
                        $user->device_id = $postData['device_id'] ?? '';
                        $user->save();

                        //log
                        activity('sale_pt_app')
                            ->withProperties(['User' => $postData])
                            ->log('User| Update User device info success #' . $user->id);
                    }

                    return response()->json([
                        'message' => 'Update success'
                    ], 200);
                }
                break;
            case 'member':
                $member = Member::find($postData['user_id']);

                if ( !isset($member->id) ) return response()->json([
                    'message' => 'Invalid member'
                ], 404);
                else {
                    if ( $member->device_token != $postData['device_token'] ) {
                        $member->device_token = $postData['device_token'] ?? '';
                        $member->device_type = $postData['device_type'] ?? '';
                        $member->device_id = $postData['device_id'] ?? '';
                        $member->save();

                        //log
                        activity('sale_pt_app')
                            ->withProperties(['Member' => $postData])
                            ->log('Member| Update Member device info success #' . $member->id);
                    }

                    return response()->json([
                        'message' => 'Update success'
                    ], 200);
                }
                break;
        }
    }

    /**
     * Reset password cho user
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPass(Request $request) {
        $postData = $request->post();

        $validator = \Validator::make($postData, [
            'email' => 'required',
        ],
        [
            'email.required' => 'Vui lòng nhập email',
        ]
        );
        if ($validator->fails())
        {
            $messages = implode(";",$validator->messages()->all());
            return response()->json([
                'message' => $messages
            ], 422);
        }

        $userData = User::where('email', $postData['email'])->get()->first();
        if ( empty($userData) ) return response()->json([
            'message' => 'Không có user nào tồn tại trong hệ thống'
        ], 422);

        //thực hiện call api SF để gửi link reset pw
        dd($userData);
    }

    public function listSales(Request $request) {
        $user = auth()->user();
        //Son Add Role Sale,Pt
        if ( !$user->hasRole(['Sale', 'PT'])) return response()->json([
            'message' => 'You don\' have permission on this action'
        ], 401);
        $userData = User::role('Sale')
            ->whereNotIn('id', [auth()->user()->id] )->get();

        foreach ($userData as $user) $user->sf_account_id = $user->sf_account_id();
        return response()->json($userData);
    }

    public function generateOTP(Request $request) {
        $max_send_time = 5;
        $postData = $request->post();
        $username = $postData['username'];
        $user = User::getByUsername($username);    
        if (!isset($user) || empty($user) ) return response()->json([
                'status' => false,
                'message' => 'Username không đúng'
            ], 400);  
        $length = 6;
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        $time_send ='0';
        if(isset($user->otp) && !empty($user->otp)){
            $array_otps = explode('-', $user->otp);
             
            if(isset($array_otps[1]) && !empty($array_otps[1]) && isset($array_otps[2])){
                $time_send_new = (int)$array_otps[2];
                $now = Carbon::now();
                $user_otp_time =  Carbon::createFromTimestamp($array_otps[1]);
                $minutes = $now->diffInMinutes($user_otp_time);       
                if($time_send_new >=$max_send_time && $minutes < 720){
                    return response()->json([
                                    'status' => false,
                                    'message' => 'Spam Otp'
                                ], 400);   
                }else{
                    if($time_send_new < $max_send_time){
                        $time_send = $time_send_new + 1;
                    }
                }
            }
        }

        // Send randomString to SF
            if(isset($postData['debug']) && $postData['debug'] == 'true'){
                // 0010w00000pPFxDAAW // 0779707383
                // $sendSMS = $this->sendSMS(['0010w00000pPFxDAAW'],[['OTP'=>$randomString]]);
                // $rp = json_decode('{"CodeResult":"100","CountRegenerate":0,"SMSID":"f230b99e-38a7-417e-b416-fc0dc2638a62100"}', true); 
                // var_dump($rp['CodeResult']);die();

                // $sendSMS = $this->sendSMS('0367597481','Mã code của bạn là: '.$randomString);
                // if(isset($sendSMS) && isset($sendSMS['ErrorMessage']) && !empty($sendSMS['ErrorMessage'])){
                //     return response()->json(['message' => $sendSMS['ErrorMessage']], 400);
                // }
                // var_dump($sendSMS);die();
            }

        if(!isset($user->phone) || empty($user->phone)){
            return response()->json(['message' => 'User không có số điện thoại'], 400);
        }

        $phone = str_replace("+84","0",$user->phone);
        // $phone = '0367597481';
        $content = 'Sử dụng '.$randomString.' để xác nhận thay đổi mật khẩu ứng dụng CITIGYM.';
        $sendSMS = $this->sendSMS($phone,$content); 
        //log
        activity('sms_api')
            ->withProperties(['account' => $sendSMS])
            ->log('Account| Send SMS #' . $user->phone . ' - '.$user->id);        
        if(isset($sendSMS) && isset($sendSMS['ErrorMessage']) && !empty($sendSMS['ErrorMessage'])){
            return response()->json(['message' => $sendSMS['ErrorMessage']], 400);
        }

        $current_timestamp = Carbon::now()->timestamp;
        $user->otp = $randomString.'-'.$current_timestamp .'-'.$time_send;
        $user->otp_verified_at = null;


        $user->save();
        return response()->json(['message' => 'Thành Công'], 201);
    }    
    public function verifiedOTP(Request $request) {
        $postData = $request->post();    
        if (!isset($postData['OTP']) || empty($postData['OTP'])) {
            return response()->json([
                'status' => false,
                'message' => 'Mã OTP không hợp lệ'
            ], 400);
        }      
        $username = $postData['username'];
        $user = User::getByUsername($username);    
        if (!isset($user) || empty($user) ) return response()->json([
                'status' => false,
                'message' => 'Username không đúng'
            ], 400);           
        $otp = $postData['OTP'];            
        $user_otp = $user->otp;          
        $array_otps = explode('-', $user_otp);
        if(!(isset($array_otps[0]) && !empty($array_otps[0]) && isset($array_otps[1]) && !empty($array_otps[1])))return response()->json([
            'message' => 'You don\' have request code'
        ], 400);        
        $now = Carbon::now();
        // var_dump($array_otps);
        $user_otp_time =  Carbon::createFromTimestamp($array_otps[1]);
        $minutes = $now->diffInMinutes($user_otp_time);

        if($otp != $array_otps[0])return response()->json([
                'status' => false,
                'message' => 'Mã OTP không hợp lệ'
            ], 400);

        if($minutes >= 3)return response()->json([
                'status' => false,
                'message' => 'Mã OTP đã hết hạn!'
            ], 400);

        $user->otp = '';
        $user->otp_verified_at = Carbon::now();
        $user->save();
        return response()->json(['message' => 'Xác minh thành công'], 201);
        
    }      

    /**
     * Task [ https://beunik.atlassian.net/browse/CIT-701]
     * @param 
     * @return mixed
     */

    public function sendSMSSF($accountIds,$OTPs){
        /**
         * Thực hiện gọi API lên SF để send sms
         * https://app.swaggerhub.com/apis/Raca/CITIGYM_Salesforce_API/1.0.0?loggedInWithGitHub=true#/SMS/sendSMS
         */
        $recordData = [
            'type' => 'FORGOT_PASSWORD_OTP',
            'accountIds'=>$accountIds,
            'data' =>$OTPs
        ];
        try {
            $response = ApexRest::post('app-api/v1/send-sms', $recordData);
            $respBody = json_decode($response->getBody()->getContents());
            var_dump($respBody);die();
            if ( $respBody->success == 'true' ) {

                //log
                activity('salesforce_api')
                    ->log('User| Send SMS Success #' . $id);

                $returnData = [
                    'success' => true,
                    'result' => $respBody->result,
                    'message' => 'Send SMS thành công!'
                ];
                return response()->json($returnData, 200);
            } else {
                $errs = $respBody->error;
                $resErr = ( is_array($errs) ) ? implode(',', $errs) : $errs;

                return response()->json(['success' => false, 'message' => $resErr], $response->getStatusCode());
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }        
    } 

    public function sendSMS($phone,$content){
        $recordData = [
            'ApiKey' => '08045792D956D7C58AC3D74041889D',
            'SecretKey' => '5F1F94B5E3C6F45F725233D8A7C5B5',
            'IsUnicode' => '0',
            'Brandname' => 'CITIGYM',
            'SmsType' => '2',
            'Content'=>$content,
            'Phone' =>$phone
        ];       
        try {
            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => 'http://rest.esms.vn/MainService.svc/json/SendMultipleMessage_V4_post_json/',
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_POSTFIELDS =>$recordData?json_encode($recordData):'', 
              CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
              ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            $rp = json_decode($response, true);       
            return $rp;
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }        
    }         
}
