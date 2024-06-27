<?php
/**
 * @author tmtuan
 * created Date: 10-Nov-20
 */

namespace App\Http\Controllers\Api;

use App\Domains\Auth\Models\User;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use App\Domains\Crm\Http\Controllers\Traits\userEmail;
use App\Domains\TmtSfObject\Classes\ApexRest;
use App\Domains\Crm\Models\SfAcccount;
use App\Domains\Crm\Models\UserSfAccount;

class AuthController extends ApiController {
    use userEmail;


    /**
     * @param Request $request
     * @param JWTAuth $auth
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request, JWTAuth $auth) {
        $appsource = $request->header('appsource');
        $inputData = $request->only('username', 'password');
        $token = null;

//        if ( isset($appsource) && $appsource === 'app_sale' ) { //Sale Login
            if (!$token = JWTAuth::attempt($inputData, ['exp' => Carbon::now()->addDays(1)->timestamp])) {
                return response()->json([
                    'message' => 'Invalid Email or Password',
                ], 422);
            }

            $user = auth()->user();
            $user->sf_account_id();
            //log last log_in time
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => request()->getClientIp(),
            ]);
            // $user->sf_account_id();
             
            $sfaccounts = UserSfAccount::where('user_id', $user->id)->get();

            if($sfaccounts){
                $ids = [];
                foreach ($sfaccounts as $key => $value) {
                    $ids[] = $value->sf_account_id;
                }
                $accounts = SfAcccount::whereIn('Id', $ids)
                            ->where('IsDeleted', 0)->select('Name', 'LastName','FirstName','Job_Title__c')
                            ->get();
            }
            
            return response()->json([
                'token' => $token,
                'user' => $user,
                'accounts' => $accounts
            ], 200);
//        } else { //Member Login
//            $this->setupLogin();
//
//            if (!$token = JWTAuth::attempt($inputData, ['exp' => Carbon::now()->addDays(1)->timestamp])) {
//                return response()->json([
//                    'message' => 'Invalid Email or Password',
//                ], 422);
//            }
//            $user = auth()->user();
//            //log last log_in time
//            $user->update([
//                'last_login_at' => now(),
//                'last_login_ip' => request()->getClientIp(),
//            ]);
//            return response()->json([
//                'token' => $token,
//                'user' => $user
//            ], 200);
//        }

    }

    /**
     * Login for member account
     * @param Request $request
     * @param JWTAuth $auth
     * @return \Illuminate\Http\JsonResponse
     */
    public function mbLogin(Request $request, JWTAuth $auth) {
        $inputData = $request->only('username', 'password');
        $token = null;

        $this->setupLogin();

        if (!$token = JWTAuth::attempt($inputData, ['exp' => Carbon::now()->addDays(1)->timestamp])) {
            return response()->json([
                'message' => 'Invalid Email or Password',
            ], 422);
        }
        $user = auth()->user();
        //log last log_in time
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => request()->getClientIp(),
        ]);
        return response()->json([
            'token' => $token,
            'user' => $user
        ], 200);

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function logout(Request $request) {
        try {
            JWTAuth::invalidate($request->token);

            return response()->json([
//                'status' => true,
                'message' => 'User logged out successfully'
            ],200);
        } catch (JWTException $exception) {
            return response()->json([
//                'status' => false,
                'message' => 'Sorry, the user cannot be logged out'
            ], 400);
        }
    }

    /**
     * Get the authenticated User Information.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUser() {
        return response()->json(auth()->user(),200);
    }

    /**
     * API reset password
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request) {
        $postData = $request->post();

        if (empty($postData['email'])) return response()->json([
//            'status' => false,
            'message' => 'Invalid Request'
        ], 400);

        $user = User::where('email', $postData['email'])
                    ->where('email_verified_at', '!=', null)
                    ->where('deleted_at', null)
                    ->first();
        if (empty($user)) return response()->json([
//            'status' => false,
            'message' => __('apiAuth.user_not_exist')
        ], 422);

        if ( $user->active == 0 ) return response()->json([
//            'status' => false,
            'message' => __('apiAuth.user_not_active')
        ], 422);

        //update user password
        if ( isset($postData['debug']) ) $password = '12341234';
        else $password = mt_rand(100000, 999999);
        $user->password_changed_at = now();
        $user->password = $password;
        if ( isset($postData['force_pass_reset']) ) $user->force_pass_reset = $postData['force_pass_reset'];

        //$user->update();
        //log
        activity('sale_pt_app')
            ->causedBy($user)
            ->withProperties(['user' => $user->toArray()])
            ->log('user| reset password #'.$user['id']);

        // gửi email mật khẩu mới
        //$user->pw_raw = $password;
        //$this->sendPasswordEmail($user);

        /**
         * Thực hiện gọi API lên SF để gửi email
         */
        $apiData = [
            "emailType" => "RESET_PASSWORD",
            "accountIds" => [
                $user->SfAccount
            ]
        ];

        try {
            $response = ApexRest::post('app-api/v1/send-email', $apiData);
            $respBody = json_decode($response->getBody()->getContents()); //dd($apiData, $respBody);
            if ( $respBody->success != 'true' ) {
                $errs = $respBody->error;
                $resErr = ( is_array($errs) ) ? implode(',', $errs) : $errs;

                return response()->json(['message' => $resErr], $response->getStatusCode());
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'pw' => $password,
            'message' => __('apiAuth.user_password_reset_success')
        ], 200);
    }


    /**
     * API change password
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request) {
        $postData = $request->post();

        $user = auth()->user();
        if ( empty($user) ) return response()->json([
//            'status' => false,
            'message' => __('apiAuth.user_not_exist')
        ], 422);

        if (empty($postData['old_password']) || empty($postData['password']) || empty($postData['passwordcf'])) return response()->json([
//            'status' => false,
            'message' => __('apiAuth.password_empty')
        ], 422);

        if ( $postData['password'] !== $postData['passwordcf'] ) return response()->json([
//            'status' => false,
            'message' => __('apiAuth.password_not_match')
        ], 422);

        if ( !Hash::check($postData['old_password'], $user->password) ) return response()->json([
//            'status' => false,
            'message' => __('apiAuth.invalid_old_password')
        ], 422);

        //update user password
        $user->password_changed_at = now();
        $user->password = $postData['password'];
        if ( isset($postData['force_pass_reset']) && $postData['force_pass_reset'] == 0 ) $user->force_pass_reset = 0;
        $user->update();

        return response()->json([
            'status' => true,
            'message' => __('apiAuth.user_password_update_success')
        ], 200);
    }

    /**
     * API change password
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function mbChangePassword(Request $request) {
        $postData = $request->post();

        $username = $postData['username'];
        $user = User::getByUsername($username);    
        if (!isset($user) || empty($user) ) return response()->json([
                'status' => false,
                'message' => 'Invalid username'
            ], 400); 
        if ( empty($user) ) return response()->json([
            'message' => __('apiAuth.user_not_exist')
        ], 422);

        if (empty($postData['password']) || empty($postData['passwordcf'])) return response()->json([
            'message' => __('apiAuth.password_empty')
        ], 422);

        if (empty($user->otp_verified_at)) return response()->json([
            'message' => 'User is not verified'
        ], 422);            

        if ( $postData['password'] !== $postData['passwordcf'] ) return response()->json([
            'message' => __('apiAuth.password_not_match')
        ], 422);

        // if ( !Hash::check($postData['old_password'], $user->password) ) return response()->json([
        //     'message' => __('apiAuth.invalid_old_password')
        // ], 422);

        //update user password
        $user->password_changed_at = now();
        $user->password = $postData['password'];
        if ( isset($postData['force_pass_reset']) && $postData['force_pass_reset'] == 0 ) $user->force_pass_reset = 0;
        
        $user->otp_verified_at = null;
        $user->update();

        return response()->json([
            'status' => true,
            'message' => __('apiAuth.user_password_update_success')
        ], 200);
    }    

}
