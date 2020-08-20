<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function userdetails()
    {
        return $this->hasOne('App\UserDetails');
    }

    public function homeaddress()
    {
        return $this->hasOne('App\UserHomeAddress');
    }

    public function officeaddress()
    {
        return $this->hasOne('App\UserOfficeAddress');
    }

    public function socialmedia()
    {
        return $this->hasOne('App\UserSocialMediaAccounts');
    }

    public function makerequest()
    {
        return $this->hasMany('App\MakeRequest');
    }

    public function surevault()
    {
        return $this->hasMany('App\SureVault');
    }

    public function lender()
    {
        return $this->hasMany('App\ConnectBorrowerToLender','lender_id','id');
    }

    public function borrower()
    {
        return $this->hasMany('App\ConnectBorrowerToLender','borrow_id','id');
    }
    public function bankdetails()
    {
        return $this->hasOne('App\BankInformation','user_id','id');
    }

    // public function userhomecountry()
    // {
    //     return $this->BelongsTo('App\Countries','user_id','id');
    // }

   
}
