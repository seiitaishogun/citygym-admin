<?php
/**
 * @author tmtuan
 * created Date: 24-Feb-21
 */

namespace App\Domains\Crm\Models;

use Illuminate\Database\Eloquent\Model;

class AppNotification extends Model
{
    public const MB_APP = 'app_MB';
    public const SALE_APP = 'app_SALE';

    protected $table = 'app_notification';
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'user_id',
        'group',
        'title',
        'message',
        'content',
        'data_option',
        'is_seen',
        'send_time',
        'is_sent'
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'is_seen' => 'boolean'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];
}
