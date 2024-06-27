<?php
/**
 * @author tmtuan
 * created Date: 08-Dec-20
 */
namespace App\Domains\Crm\Models;

use Illuminate\Database\Eloquent\Model;

class UserKpi extends Model {
    protected $table = 'users_kpi';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'revenue','target', 'target_percent', 'convertion_rate', 'rating', 'projection', 'appt_number', 'package_size', 'created_at',  'updated_at'  
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['user_id'];

    public static function findBySfAccountId($id = null) {
        if (isset($id) && !empty($id)) {
            return self::where('user_id',$id)->first();
        }
        return [];
    }

}
