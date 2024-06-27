<?php
/**
 * @author tmtuan
 * created Date: 02-Dec-20
 */

namespace App\Domains\Crm\Http\Controllers\Api\Users;

use App\Domains\Auth\Models\Role;
use App\Domains\Crm\Models\UserKpi;
use App\Domains\Crm\Models\UserSfAccount;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use App\Domains\Auth\Models\User;
use App\Domains\Crm\Http\Controllers\Traits\userEmail;
use Illuminate\Support\Str;

class SfUserController extends ApiController {
    use userEmail;

    /**
     * Create an user
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createUser(Request $request) {
        $postData = $request->post();

        $validator = \Validator::make($postData, [
            'username' => 'required|max:255',
            // 'name' => 'sometimes|required|max:255',
            'phone' => 'sometimes|required|max:20',
            'email' => 'sometimes|required|email',
            'job_title' => 'sometimes|required',
            'sf_account_id' => 'required',
        ]);
        if ($validator->fails())
        {
            //log
            $errorString = implode(",",$validator->messages()->all());
            activity('salesforce_api')
                ->withProperties(['user' => $postData])
                ->log('User - Fail| create new user Fail | '.$errorString);
            return response()->json(['message' => $validator->messages()], 422);
        }

        //check user by email
        $user = User::where('email', $postData['email'])->first();
        if ( !empty($user) || isset($user->id) ) {
            if ($user->deleted_at != null){
                $user->deleted_at = null;
                $user->save();
            }
            //get new role
            if ( $postData['type'] == 'employee' )  $role = Role::where('name', $postData['job_title'])->get()->first();
            else $role = Role::where('name', 'Member')->get()->first();

            DB::beginTransaction();

            try {
                //xử lý xóa role cũ của Sales/PT/MS khi user type là employee
                if ( $postData['type'] == 'employee' ) {
                    $oldRole = ['Sale', 'PT', 'MS'];
                    foreach ($oldRole as $roleItem) {
                        if ( $user->hasRole($roleItem)) $user->removeRole($roleItem);
                    }
                }

                $user->assignRole([$role->id]);

            } catch (Exception $e) {
                DB::rollBack();
                return response()->json(['message' => 'There was a problem creating role for this user. Please try again.'], 422);
            }

            //log
            activity('salesforce_api')
                ->causedBy($user)
                ->withProperties(['user' => $request->post()])
                ->log('User| update user success #'.$user->id);
            DB::commit();

            // insert sf_account_id
            switch ($postData['type']) {
                case "employee":
                    $accountType = UserSfAccount::TYPE_EMPLOYEE;
                    break;
                case 'user':
                default:
                    $accountType = UserSfAccount::TYPE_MEMBER;
                    break;
            }
            $accTypeChk = UserSfAccount::where('account_type', $accountType)->where('user_id', $user->id)->get()->first();
            if ( empty($accTypeChk) ) {
                DB::beginTransaction();
                try {
                    UserSfAccount::create([
                        'user_id' => $user->id,
                        'sf_account_id' => $postData['sf_account_id'],
                        'account_type' => $accountType
                    ]);
                } catch (Exception $e) {
                    DB::rollBack();
                    return response()->json(['message' => 'There was a problem creating data for this user. Please try again.'], 422);
                }
                DB::commit();
            } else{
                $updateData = [
                    'sf_account_id' => $postData['sf_account_id'],
                    'account_type' => $accountType
                ];
                DB::table('user_sf_account')
                    ->where('user_id', $user->id)
                    ->update($updateData);

            }
            return response()->json(['message' => 'User update success', 'user' => User::find($user->id)], 200);
        } else {
            $userType = $postData['type'];
            DB::beginTransaction();

            try {
                $postData['password'] = Str::random(10);
                // $postData['type'] = User::TYPE_USER;
                $postData['email_verified_at'] = now() ;
                $postData['active'] = '1';
                $postData['force_pass_reset'] = '1';

                //create user
                $user = User::create($postData);
                $user->pw_raw = $postData['password'];

                //set role
                if ( $userType == 'employee' ) {
                    $role = Role::where('name', $postData['job_title'])->get()->first();
                }
                else $role = Role::where('name', 'Member')->get()->first();
                $user->syncRoles([$role->id]);

                //insert sf_account_id
                switch ($userType) {
                    case "employee":
                        $accountType = UserSfAccount::TYPE_EMPLOYEE;
                        break;
                    case 'user':
                    default:
                        $accountType = UserSfAccount::TYPE_MEMBER;
                        break;
                }
                UserSfAccount::create([
                    'user_id' => $user->id,
                    'sf_account_id' => $postData['sf_account_id'],
                    'account_type' => $accountType
                ]);

                $this->sendPasswordEmail($user);

                //log
                activity('salesforce_api')
                    ->causedBy($user)
                    ->withProperties(['user' => $request->post()])
                    ->log('User| create new user #'.$user->id);
                DB::commit();
                return response()->json(['message' => 'Created success', 'user' => User::find($user->id)], 201);
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json(['message' => 'There was a problem creating this user. Please try again.'], 422);
            }


        }
    }

    /**
     * Create 50 user at the sametime
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createUserBatch(Request $request) {
        $postData = $request->post();
        $returnData = [];
//        if ( count($postData) > 50 ) return response()->json(['message' => 'The number of user exceeding the permitted limits (50 users)'], 422);

        foreach ( $postData as $item ) {

            $validator = \Validator::make($item, [
                'username' => 'required|max:255',
                'phone' => 'sometimes|required|max:20',
                // 'email' => 'sometimes|required|email',
                'type' => 'sometimes|required',
                'job_title' => 'sometimes|required',
                'sf_account_id' => 'required',
            ]);
            if ($validator->fails()) {
                $returnData[] = [
                    'success' => false,
                    'error' => implode(",",$validator->messages()->all()),
                    'result' => $item['sf_account_id']
                ];
                //log request
                activity('salesforce_api')
                    ->withProperties(['user' => $item,
                                    'errors' => $validator->messages()])
                    ->log('createUserBatch Error | createUserBatch Fail - '.$request->path());
                continue;
            } else {

                //check user by email
                $user = User::where('username', $item['username'])->first();
                if ( !empty($user) || isset($user->id) ) {
                    $userType = $item['type'];
                    if ($user->deleted_at != null) {
                        $user->deleted_at = null;
                        $user->save();
                    }
                    //get new role
                    if ($item['type'] == 'employee') $role = Role::where('name', $item['job_title'])->get()->first();
                    else $role = Role::where('name', 'Member')->get()->first();

//                    DB::beginTransaction();
                    try {
                        //xử lý xóa role cũ của Sales/PT/MS khi user type là employee
                        if ($item['type'] == 'employee') {
                            $oldRole = ['Sale', 'PT', 'MS'];
                            foreach ($oldRole as $roleItem) {
                                if ($user->hasRole($roleItem)) $user->removeRole($roleItem);
                            }
                        }

                        $user->assignRole([$role->id]);
                    } catch (Exception $e) {
                        $returnData[] = [
                            'success' => false,
                            'error' => "There was a problem creating role for this user. Please try again.",
                            'result' => $item['sf_account_id']
                        ];
                    }

                    // insert sf_account_id
                    switch ($userType) {
                        case "employee":
                            $accountType = UserSfAccount::TYPE_EMPLOYEE;
                            break;
                        case 'user':
                        default:
                            $accountType = UserSfAccount::TYPE_MEMBER;
                            break;
                    }
                    $accTypeChk = UserSfAccount::where('account_type', $accountType)->where('user_id', $user->id)->get()->first();
                    if (empty($accTypeChk)) {
                        try {
                            UserSfAccount::create([
                                'user_id' => $user->id,
                                'sf_account_id' => $item['sf_account_id'],
                                'account_type' => $accountType
                            ]);

                        } catch (Exception $e) {
                            $returnData[] = [
                                'success' => false,
                                'error' => 'There was a problem creating data for this user. Please try again.',
                                'result' => $item['sf_account_id']
                            ];
                        }

                    } else {
                        $updateData = [
                            'sf_account_id' => $item['sf_account_id'],
                            'account_type' => $accountType
                        ];
                        DB::table('user_sf_account')
                            ->where('user_id', $user->id)
                            ->update($updateData);
                    }
                    unset($item['type']);
                    $user->fill($item);
                    $user->save();
                    //log
                    activity('salesforce_api')
                        ->causedBy($user)
                        ->withProperties(['user' => $request->post()])
                        ->log('User| update user success #' . $user->id);
//                    DB::commit();
                    $returnData[] = [
                        'success' => true,
                        'error' => 'User update success',
                        'result' => $item['sf_account_id']
                    ];
                    continue;
                } else { //create new user
                    $userType = $item['type'];
                    try {
//                        $item['password'] = Str::random(10);
                        $item['password'] = mt_rand(100000, 999999);
                        $item['name'] = $item['name'] ?? $item['last_name'] . ' ' . $item['first_name'] ;
                        $item['email_verified_at'] = now() ;
                        $item['active'] = '1';
                        $item['force_pass_reset'] = '1';
                        $item['type'] = 'user';

                        //create user
                        $newUser = User::create($item);

                        //set role
                        if ( $userType == 'employee' ) {
                            $role = Role::where('name', $item['job_title'])->get()->first();
                        }
                        else $role = Role::where('name', 'Member')->get()->first();

                        $newUser->syncRoles([$role->id]);

                        $newUser->pw_raw = $item['password'];
                        // send email

                        //insert sf_account_id
                        switch ($userType) {
                            case "employee":
                                $accountType = UserSfAccount::TYPE_EMPLOYEE;
                                break;
                            case 'user':
                            default:
                                $accountType = UserSfAccount::TYPE_MEMBER;
                                break;
                        }
                        UserSfAccount::create([
                            'user_id' => $newUser->id,
                            'sf_account_id' => $item['sf_account_id'],
                            'account_type' => $accountType
                        ]);

//                        $this->sendPasswordEmail($newUser);

                        //log
                        activity('salesforce_api')
                            ->causedBy($newUser)
                            ->withProperties(['user' => $request->post()])
                            ->log('User| create new user from Salesforce #'.$item['sf_account_id']);

                        $returnData[] = [
                            'success' => true,
                            'error' => "Create User Success",
                            'result' => $item['sf_account_id']
                        ];
//                        DB::commit();
                        continue;
                    } catch (Exception $e) {
                        //log request
                        activity('salesforce_api')
                            ->withProperties(['user' => $item,
                                'errors' => $e->getMessage()])
                            ->log('createUserBatch Error | createUserBatch Fail - '.$request->path());

                        DB::rollBack();
                        $returnData[] = [
                            'success' => false,
                            'error' => "Create User Fail",
                            'result' => $item['sf_account_id']
                        ];
                    }

                }
            }
        }

        return response()->json($returnData, 200);
    }


    /**
     * save KPI information for user
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function createUserKpi(Request $request) {
        $postData = $request->post();
        if ( empty($postData['sf_account_id']) || !isset($postData['sf_account_id']) ) return response()->json(['message' => 'SalesforceAccount ID can not empty'], 422);

        $user = UserSfAccount::where('sf_account_id', $postData['sf_account_id'])
                    ->first();
        if ( empty($user) ) return response()->json(['message' => "The user with Salesforce ID #{$postData['sf_account_id']} is not exist"], 422);

        $kpiItem = UserKpi::where('user_id', $user->user_id)->first();
        if ( !empty($kpiItem) ) {
            DB::beginTransaction();
            try {
                unset($postData['Id']);
                $kpiItem->fill($postData);
                $kpiItem->save();

                //log
                activity('salesforce_api')
                    ->withProperties(['kpi' => $postData])
                    ->log('User KPI| Update User KPI success #' . $kpiItem->Id);
                DB::commit();
                $returnData = [
                    'success' => true,
                    'error' => 'Update account success',
                    'result' => $kpiItem->Id
                ];

                return response()->json($returnData, 200);
            } catch (\Exception $e) {
                DB::rollBack();
                $returnData = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'result' => $kpiItem->Id
                ];
                return response()->json($returnData, 200);
            }
        } else {
            $kpi = new UserKpi($postData);
            $kpi->user_id = $user->id;
            $kpi->save();
            //log
            activity('salesforce_api')
                ->causedBy($user)
                ->withProperties(['kpi' => $postData])
                ->log('User KPI| create new user kpi');

            return response()->json(['message' => 'Created User Kpi Success'], 201);
        }
    }

    /**
     * update password
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setPassword(Request $request) {
        $postData = $request->post();

        //log request
        activity('salesforce_api')
            ->withProperties(['user-password' => $postData])
            ->log('Request | setPassword - '.$request->path());

        if (empty($postData['username']) || empty($postData['password'])) return response()->json([
            'success' => false,
            'message' => 'Invalid Request'
        ], 200);

        $user = User::where('username', $postData['username'])->first();

        if ( isset($user->id) ) {
            //update user password
            $user->password_changed_at = now();
            $user->password = $postData['password'];
            $user->force_pass_reset = 1;
            $user->update();

            return response()->json([
                'success' => true,
            ], 200);
        }
    }

    /**
     * Update password multi user
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setPasswordMulti(Request $request) {
        $postData = $request->post();
        $returnData = [];

        foreach ($postData as $item) {
            if (empty($item['username']) || empty($item['password'])) $returnData[] = [
                'success' => false,
                'message' => 'Invalid username or password'
            ];
            else
            {
                $user = User::where('username', $item['username'])->first();

                if ( isset($user->id) ) {
                    //update user password
                    $user->password_changed_at = now();
                    $user->password = $item['password'];
                    $user->force_pass_reset = 1;
                    $user->update();

                    //log request
                    activity('salesforce_api')
                        ->causedBy($user)
                        ->withProperties(['user-password' => $item])
                        ->log('Request | setPassword - '.$request->path());
                    $returnData[] = [
                        'success' => true,
                        'message' => 'Update password success for user '.$item['username']
                    ];
                } else {
                    $returnData[] = [
                        'success' => false,
                        'user' => $item['username'],
                        'message' => 'No user exist with username = '.$item['username']
                    ];
                }
            }
        }

        return response()->json($returnData, 200);
    }

    /**
     * Kiểm tra username đã tồn tại hay chưa
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkUserName(Request $request) {
        $postData = $request->post();
        $returnData = [];

        foreach ($postData as $item) {
            if ( !empty( $item['username'] ) ) {
                $userData = User::join('user_sf_account', 'user_sf_account.user_id', '=', 'users.id')
                        ->where('username', $item['username'])->first();

                if ( isset($userData->id) ) $returnData[] = [
                    'email' => $item['username'],
                    'Account_Id' => $userData->sf_account_id,
                    'message' => "username exist"
                ];
                else $returnData[] = [
                    'email' => $item['username'],
                    'message' => "username does not exist"
                ];
            }

        }
        return response()->json($returnData, 200);
    }

    public function changeEmail(Request $request) {
        $postData = $request->post();
        $returnData = [];

        foreach ($postData as $item) {
            if ( !empty( $item['sf_account'] ) && !empty($item['email']) ) {
                $userData = User::leftJoin('user_sf_account', 'user_sf_account.user_id', '=', 'users.id')
                            ->where('user_sf_account.sf_account_id', $item['sf_account'])->first();

                if ( isset($userData->id) ) {
                    //update email
                    $userData->email = $item['email'];
                    $userData->email_verified_at = Carbon::now();
                    $userData->save();

                    $returnData[] = [
                        'sf_account' => $item['sf_account'],
                        'success' => true,
                        'message' => 'Update email success'
                    ];
                    //log
                    activity('salesforce_api')
                        ->causedBy($userData)
                        ->withProperties(['user_email' => $item])
                        ->log('User Email| Update email success');

                } else $returnData[] = [
                    'sf_account' => $item['sf_account'],
                    'success' => false,
                    'message' => "User does not exist"
                ];

            }
            unset($userData);
        }
        return response()->json($returnData, 200);

    }

}
