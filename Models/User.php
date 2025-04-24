<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Traits\MediaUrlTrait;

class User extends Authenticatable implements HasMedia
{
    use HasApiTokens, HasFactory, Notifiable, InteractsWithMedia, MediaUrlTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'country_code',
        'gender',
        'age',
        'firebase_uid',
        'firebase_session',
        'email_verified_at',
        'phone_verified_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'firebase_uid',
        'firebase_session',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
    ];

    public function avatar(){
        return $this->hasOne(Avatar::class, 'user_id');
    }

    public function bodyStatus(){
        return $this->hasOne(BodyStatus::class, 'user_id');
    }
    public function subscriptionUser()
    {
        return $this->hasMany(SubscriptionUser::class, 'user_id', 'id');
    }
    
    public function subscription()
    {
        return $this->belongsToMany(Subscription::class, 'subscription_users');
    }
    
    public function healthMentorWikis()
    {
       return    $this->hasMany(HealthMentorWiki::class)->onDelete('cascade');
    }
    
    public function medias()
    {
        return $this->morphMany(Media::class, 'model');
    }
    
    public function UserMedicalTestValue(){
        return $this->hasMany(UserMedicalTest::class);
    }
}
