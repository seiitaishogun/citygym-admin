<?php

namespace App\Domains\Auth\Models;

use App\Domains\Auth\Models\Traits\Attribute\UserAttribute;
use App\Domains\Auth\Models\Traits\Method\UserMethod;
use App\Domains\Auth\Models\Traits\Relationship\UserRelationship;
use App\Domains\Auth\Models\Traits\Scope\UserScope;
use App\Domains\Auth\Models\Traits\userSfAccount;
use App\Domains\Auth\Notifications\Frontend\ResetPasswordNotification;
use App\Domains\Auth\Notifications\Frontend\VerifyEmail;
use Carbon\Carbon;
use DarkGhostHunter\Laraguard\Contracts\TwoFactorAuthenticatable;
use DarkGhostHunter\Laraguard\TwoFactorAuthentication;
use Database\Factories\UserFactory;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Lab404\Impersonate\Models\Impersonate;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Domains\Crm\Models\Traits\UserRole;
use Illuminate\Support\Facades\URL;

/**
 * Class User.
 */
class User extends Authenticatable implements MustVerifyEmail, TwoFactorAuthenticatable, JWTSubject
{
    use HasFactory,
        HasRoles,
        Impersonate,
        MustVerifyEmailTrait,
        Notifiable,
        SoftDeletes,
        TwoFactorAuthentication,
        UserAttribute,
        UserMethod,
        UserRelationship,
        UserScope,
        UserRole,
        userSfAccount;

    public const TYPE_ADMIN = 'admin';
    public const TYPE_USER = 'user';
//    public const TYPE_EMPLOYEE = 'employee';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'name', 'last_name', 'first_name',
        'email',
        'username', 'avata',
        'nick_name', 'gender', 'phone', 'club_id',
        'email_verified_at',
        'password',
        'password_changed_at',
        'active',
        'timezone',
        'last_login_at',
        'last_login_ip',
        'to_be_logged_out',
        'provider',
        'provider_id',
         'force_pass_reset',
//        'sf_account_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @var array
     */
    protected $dates = [
        'last_login_at',
        'email_verified_at',
        'password_changed_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'active' => 'boolean',
        'last_login_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'to_be_logged_out' => 'boolean',
    ];

    /**
     * @var array
     */
    protected $appends = [
//        'avatar',
        'SfAccount'
    ];

    /**
     * @var string[]
     */
    protected $with = [
        'permissions',
        'roles',
    ];

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Send the registration verification email.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmail);
    }

    /**
     * Return true or false if the user can impersonate an other user.
     *
     * @param void
     * @return  bool
     */
    public function canImpersonate(): bool
    {
        return $this->can('admin.access.user.impersonate');
    }

    /**
     * Return true or false if the user can be impersonate.
     *
     * @param void
     * @return  bool
     */
    public function canBeImpersonated(): bool
    {
        return ! $this->isMasterAdmin();
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return UserFactory::new();
    }

    /**
     * @inheritDoc
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * @inheritDoc
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getAvataAttribute($value) {
        $myDate = Carbon::parse($this->attributes['created_at']);
        if ( !empty($value) ) return URL::to('storage/users/'.$myDate->format('Y/m/').$value);
        else return 'https://gravatar.com/avatar/'.md5(strtolower(trim($this->email))).'?s='.config('boilerplate.avatar.size', null).'&d=mp';
    }

    public static function getByUsername($username) {
        if (!isset($username) || empty($username) ) return null;
        return self::where('username', $username)->first();
    }
}
