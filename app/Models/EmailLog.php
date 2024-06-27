<?php
/**
 * @author tmtuan
 * created Date: 02-Mar-21
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model {
    protected $table = 'email_log';
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'from',
        'to',
        'cc',
        'bcc',
        'subject',
        'body',
        'headers',
        'attachments'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public $timestamps = true;
}
