<?php

namespace App\Domains\Crm\Models\Traits;
use App\Domains\Crm\Models\UserSfAccount;
use Illuminate\Support\Facades\DB;
/**
 * Class UserScope.
 */
trait UserRole
{
    function getRequestHeaders() {
        $headers = array();
        foreach($_SERVER as $key => $value) {
            if (substr($key, 0, 5) <> 'HTTP_') {
                continue;
            }
            $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
            $headers[$header] = $value;
        }
        return $headers;
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function sf_account_id($user_type = 'member')
    {
        $headers = $this->getRequestHeaders();

        $app_source = (isset($headers['Appsource']) && $headers['Appsource'] == 'app_sale') ? 'employee' : $user_type;

        // $user_type = 'employee';
        if($this->userSfAccount){
            $userSfAccount = $this->userSfAccount->where('user_sf_account.account_type',$app_source)->where('user_sf_account.user_id', $this->id)->first();
            if ($userSfAccount) {
                return $userSfAccount->sf_account_id;
            }  
        }


        return null;
    }

    public function userSfAccount()
    {
        return $this->belongsTo(UserSfAccount::class, 'id','user_id');
    }
}
