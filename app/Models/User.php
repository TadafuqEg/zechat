<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Casts\CustomDateTimeCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;


    protected $guarded = ['password_confirmation'];

    protected $appends = ['profile_image_full_url'];

    public function getProfileImageFullUrlAttribute()
    {
        return asset('/profile_images/'.$this->attributes['profile_image']);
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id')->orWhere('receiver_id', $this->id);
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $casts = [
        'password' => 'hashed',
        'created_at' => CustomDateTimeCast::class,
        'updated_at' => CustomDateTimeCast::class,
    ];
}
