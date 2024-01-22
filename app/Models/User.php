<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * 取得 JWT 辨識字串
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
        // return 'hrm_id';
    }

    /**
     * 回傳鍵值對陣列，內容包含被加入 JWT 的自定義 Payload
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    // /**
    //  * Get the name of the unique identifier for the user.
    //  *
    //  * @return string
    //  */
    // public function getAuthIdentifierName()
    // {
    //     return 'hrm_id';
    // }
}
