<?php

namespace App\Models;

use App\Http\Requests\ActivateUser;
use Illuminate\Database\Query\Builder;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Http\Request;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstname', 'lastname', 'email', 'password','role_id','latest_jwt_claims'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token','email_token'
    ];

    /**
     * The attributes that should be Converted
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Custom Observables
     *
     * @var array
     */
    protected $observables = [
        'activated','statusChanged'
    ];

    /**
     * User role
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Get full name
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return $this->firstname . " " . $this->lastname;
    }

    /**
     * Get full name
     *
     * @return string
     */
    public function getStatusName()
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    /**
     * User Search
     *
     * @param Builder $query  query
     * @param Request $search search
     *
     * @return mixed
     */
    public function scopeSearch($query, Request $search)
    {
        $query->where(
            function ($query) use ($search) {

                if (($keyword = $search->get('keyword')) !== false) {
                    $query->where("firstname", "LIKE", "%$keyword%")
                        ->orWhere("lastname", "LIKE", "%$keyword%")
                        ->orWhere("email", "LIKE", "%$keyword%");


                }
            }
        );

        $sortable_fields = ['firstname','lastname','status','email','is_active'];

        $sort_by = 'lastname';

        if (in_array($search->get('sortBy'), $sortable_fields)) {
            $sort_by = $search->get('sortBy');
        }

        $sort_order = [
            'ascending' => 'ASC',
            'descending' => 'DESC',
        ];

        $order = $sort_order[$search->get('order', 'ascending')];

        if ($sort_by == 'is_active') {
            $order = ($order == 'ASC') ? 'DESC' : 'ASC';
        }

        return $query->orderBy($sort_by, $order);
    }

    /**
     * Activate User
     *
     * @param ActivateUser $fields Object
     *
     * @return bool
     */
    public function activate(ActivateUser $fields)
    {
        $this->status = 'REGISTERED';

        $this->password = bcrypt($fields->password);

        if ($this->save()) {
            $this->fireModelEvent('activated', false);
            return true;
        }

        return false;
    }

    /**
     * Find user by email and token
     *
     * @param string $email       email
     * @param string $email_token token
     *
     * @return mixed
     */
    public static function isValidInvitedUser($email, $email_token)
    {
        return User::where(
            [
                'email' => $email,
                'email_token' => $email_token,
                'is_active' => 1,
                'status' => 'INVITED'
            ]
        )->first();
    }

    /**
     * Update is_active state
     *
     * @param bool $value true or false
     *
     * @return bool
     */
    public function changeIsActiveState($value)
    {
        $this->is_active = $value;

        if ($this->save()) {
            $this->fireModelEvent('statusChanged', false);
            return true;
        }

        return false;
    }

    /**
     * Set Latest JWT
     *
     * @param null $token token value
     *
     * @return void
     */
    public function setJWT($token = null)
    {
        $this->latest_jwt_claims = $token;
        $this->save();
    }

    /**
     * Get active admin user by email
     *
     * @param $email
     *
     * @return mixed
     */
    public static function getValidUserByEmail($email)
    {
        return User::where([
            'email' => $email,
            'is_active' => 1,
            'status' => 'REGISTERED'
        ])->first();
    }

    /**
     * Get all of the password resets.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function passwordReset()
    {
        return $this->morphMany(PasswordReset::class, 'password_resettable');
    }

    /**
     * Get latest password reset that is assigned to the user.
     */
    public function latestPasswordReset()
    {
        return $this->morphMany(PasswordReset::class, 'password_resettable')
            ->orderBy('id', 'DESC')
            ->first();
    }

    /**
     * JWT Claims for admin user
     *
     * @return array
     */
    public function generateJWTClaims()
    {
        return [
            'type' => 'user',
            'email' => $this->email
        ];
    }

    /**
     * JWT Credentials
     *
     * @param $email
     * @param $password
     *
     * @return array
     */
    public function generateJWTCredentials($email, $password)
    {
        return [
            'email' => $email,
            'password' => $password,
            'is_active' => true,
            'status' => 'REGISTERED'
        ];
    }
}
