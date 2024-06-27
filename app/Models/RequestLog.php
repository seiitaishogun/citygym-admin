<?php
/**
 * @author tmtuan
 * created Date: 22-Mar-21
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestLog extends Model {
    protected $table = 'request_log';
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'duration',
        'status_code',
        'url',
        'method',
        'ip',
        'Request',
        'Response'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
    ];

    public $timestamps = true;
}
