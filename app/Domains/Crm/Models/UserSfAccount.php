<?php
/**
 * @author tmtuan
 * created Date: 05-Jan-21
 */
namespace App\Domains\Crm\Models;

use Illuminate\Database\Eloquent\Model;

class UserSfAccount extends Model {
    protected $table = 'user_sf_account';

    public const TYPE_MEMBER = 'member';
    public const TYPE_EMPLOYEE = 'employee';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'sf_account_id', 'account_type'
    ];

    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;
}
