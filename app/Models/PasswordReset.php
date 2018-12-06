<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    protected $fillable = ['email', 'token', 'password_resettable_type', 'is_changed', 'password_resettable_id'];

    protected $casts = [
        'is_changed' => 'boolean'
    ];

    public function setCreatedAtAttribute()
    {
        $this->attributes['created_at'] = Carbon::now()->toDateTimeString();
    }

    public function setUpdatedAt($value)
    {
        return $this;
    }

    public function getUpdatedAtColumn()
    {
        return null;
    }

    /**
     * Get all of the owning passwordResettable models.
     */
    public function passwordResettable()
    {
        return $this->morphTo();
    }

    /**
     * Check whether the request is valid or not
     *
     * @param $email
     * @param $token
     * @param string $type
     * @return mixed
     */
    public static function isValid($email, $token, $type = Learner::class)
    {
        return PasswordReset::where('email', $email)
            ->where('token', $token)
            ->where('is_changed', false)
            ->where('password_resettable_type', $type)
            ->orderBy('id', 'DESC')
            ->first();
    }

    /**
     * Check whether the token is expired or not
     *
     * @return bool
     */
    public function notExpired()
    {
        $time_diff = Carbon::now()->diffInHours(Carbon::parse($this->created_at));

        return $time_diff < 24;
    }

    /**
     * Check token validity
     *
     * @param $token
     *
     * @return PasswordReset|null
     */
    public function valid($token)
    {
        return $this->is_changed == false && $this->token == $token
            ? $this
            : null;
    }


}
