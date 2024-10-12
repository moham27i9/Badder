<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table='users';
    protected $fillable = [
        'first_name',
        'last_name',
        'role',
        'phone',
        'image',
        'email',
        'password',
    ];


    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function subscribe(){
        return $this->hasMany(Subscribe::class);
    }

    public function react(){
        return $this->hasMany(React::class);
    }

    public function saveArticle(){
        return $this->hasMany(SaveArticle::class);
    }

    public function subscribe_course(){
        return $this->hasMany(Subscribe_course::class);
    }

    public function suggestion(){
        return $this->hasMany(Suggestion::class);
    }

    public function help_request(){
        return $this->hasMany(Help_request::class);
    }

    public function gallery(){
        return $this->hasMany(Gallery::class);
    }

    public function volunteer (){
        return $this->hasOne(Volunteer::class);
    }

    public function donation_number(){
        return $this->hasMany(Donation_number::class);
    }

    public function ads(){
        return $this->hasMany(Ads::class);
    }
    public function rating(){
        return $this->hasMany(Rating::class);
    }


   public function getJWTIdentifier(){
        return $this -> getKey();
    }
    public function getJWTCustomClaims(){
        return [];
    }
}

