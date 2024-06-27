<?php
/**
 * @author tmtuan
 * created Date: 09-Dec-20
 */
namespace App\Domains\Crm\Models;

use Illuminate\Database\Eloquent\Model;

class RecordType extends Model {
    protected $table = 'salesforce_Record_Type';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        //force required
        'Id', 'Name','DeveloperName', 'SobjectType'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public $timestamps = true;
    protected $casts = ['Id' => 'string'];
}
