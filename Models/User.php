<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements HasMedia
{
    use HasApiTokens, HasFactory, Notifiable, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    // protected $fillable = [
    //     'name',
    //     'email',
    //     'password',
    // ];
    protected $guarded = [];

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
