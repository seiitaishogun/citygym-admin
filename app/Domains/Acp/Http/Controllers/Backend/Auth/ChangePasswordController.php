<?php
/**
 * @author tmtuan
 * created Date: 11/5/2021
 * project: citygym-admin
 */

namespace App\Domains\Acp\Http\Controllers\Backend\Auth;


use App\Domains\Auth\Http\Requests\Backend\User\EditUserPasswordRequest;
use App\Domains\Auth\Http\Requests\Backend\User\UpdateUserPasswordRequest;
use App\Domains\Auth\Models\User;
use App\Domains\Auth\Services\UserService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use LangleyFoxall\LaravelNISTPasswordRules\PasswordRules;

class ChangePasswordController extends Controller
{
    /**
     * @var UserService
     */
    protected $userService;

    /**
     * UserPasswordController constructor.
     *
     * @param  UserService  $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @param  EditUserPasswordRequest  $request
     * @param  User  $user
     *
     * @return mixed
     */
    public function edit(Request $request)
    {
        $user = auth()->user();
        if ( !isset($user->id) ) return redirect()->route('admin.dashboard')->withFlashSuccess(__('Invalid Request!'));

        return view('backend.auth.user.edit-password')
            ->withUser($user);
    }

    /**
     * @param  UpdateUserPasswordRequest  $request
     * @param  User  $user
     *
     * @return mixed
     * @throws \Throwable
     */
    public function update(Request $request)
    {
        $user = auth()->user();
        if ( !isset($user->id) ) return redirect()->route('admin.dashboard')->withFlashSuccess(__('Invalid Request!'));

        $postData = $request->post();

        $rules = array(
            'password'              => PasswordRules::changePassword($user->email),
        );

        $validator = \Validator::make($postData, $rules);

        if ($validator->fails()) {

            // get the error messages from the validator
            $messages = $validator->messages();

            // redirect our user back to the form with the errors from the validator
            return redirect()->back()
                ->withErrors($validator);

        }

        $this->userService->updatePassword($user, $postData);

        return redirect()->route('admin.dashboard')->withFlashSuccess(__('Đổi mật khẩu thành công.'));
    }
}
