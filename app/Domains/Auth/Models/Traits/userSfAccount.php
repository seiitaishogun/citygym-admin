<?php
/**
 * @author tmtuan
 * created Date: 8/23/2021
 * project: citygym-admin
 */
namespace App\Domains\Auth\Models\Traits;

use App\Domains\Crm\Models\SfAcccount;
use function Couchbase\defaultDecoder;
use Illuminate\Support\Facades\DB;

trait userSfAccount {
    public function getSfAccountAttribute() {
        if ( isset($this->attributes['id']) ) {
            $sfAccount = $sfData = DB::table('user_sf_account')->where('user_id', $this->attributes['id'])->first();
            if ( isset($sfAccount->sf_account_id) ) return $sfAccount->sf_account_id;
            else return '';
        } else return '';
    }
}
