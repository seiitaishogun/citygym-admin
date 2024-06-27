<?php
/**
 * @author tmtuan
 * created Date: 06-Apr-21
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobLog extends Model {
    protected $table = 'job_log';
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'job_title',
        'status',
        'duration',
        'response'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
    ];
}
