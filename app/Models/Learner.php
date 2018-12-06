<?php

namespace App\Models;

use App\Http\Requests\ActivateLearner;
use App\Http\Requests\ActivateUser;
use Illuminate\Database\Query\Builder;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Http\Request;

class Learner extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstname', 'lastname', 'email', 'password','role_id','latest_jwt_claims', 'is_active',
        'course_id', 'center', 'dialingcode', 'phone', 'address1', 'address2', 'town', 'country_id',
        'zip', 'status', 'center_id', 'language_id'
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
        'activated',
        'statusChanged',
        'fieldsUpdated'
    ];

    protected static $progress = null;
    protected static $completed_items = null;

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

    public function courses()
    {
        return $this->belongsToMany('App\Models\Course', 'course_learners')
            ->withTimestamps()
            ->orderBy('course_learners.is_active', '= 1')
            ->orderBy('pivot_updated_at', 'desc');
    }

    /**
     * Currently assigned course
     *
     * @return mixed
     */
    public function assignedCourse()
    {
        return $this->courses()
            ->where('is_active', true)
            ->with(['units' => function ($q) {
                $q->orderBy('order', 'ASC');
            }])->first();
    }

    public function assignedCourseId()
    {
        $course = $this->courses()
            ->where('is_active', true)
            ->first();

        return $course ? $course->id : null;
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

        $query->leftJoin('centers', 'learners.center_id', '=', 'centers.id')
            // ->select('learners.*', 'centers.name as center_name')
            // ->leftJoin('course_learners', 'learners.id', '=', 'course_learners.learner_id')
            ->leftJoin('course_learners', function ($join) {
                $join->on('learners.id', '=', 'course_learners.learner_id');
                $join->on('course_learners.is_active', '=', DB::raw('1'));
            })
            ->leftJoin('courses', 'course_learners.course_id', '=', 'courses.id')
            ->select('learners.*', 'courses.name as course_name', 'centers.name as center_name');

        $query->whereNotIn('learners.id', [1]);

        $query->where(
            function ($query) use ($search) {

                if (($keyword = $search->get('keyword')) !== false) {
                    $query->where("firstname", "LIKE", "%$keyword%")
                        ->orWhere("lastname", "LIKE", "%$keyword%")
                        ->orWhere("learners.email", "LIKE", "%$keyword%")
                        ->orWhere("learners.status", "LIKE", "$keyword")
                        ->orWhere("courses.name", "LIKE", "%$keyword%");

                    //Filter is_active
                    if (strtolower($keyword) == 'active') {
                        $query->orWhere("learners.is_active", true);
                    } elseif (strtolower($keyword) == 'inactive') {
                        $query->orWhere("learners.is_active", false);
                    }
                }
            }
        );

        $query->with(['center' => function ($q) {
            $q->select('id', 'name');
        }]);

        $sortable_fields = ['firstname','lastname','status','email','center.name','course.name','active.name'];

        $sort_by = 'lastname';

        if (in_array($search->get('sortBy'), $sortable_fields)) {
            $sort_by = $search->get('sortBy');
        }

        $sort_order = [
            'ascending' => 'ASC',
            'descending' => 'DESC',
        ];

        $order = $sort_order[$search->get('order', 'ascending')];

        if ($sort_by == 'center.name') {
            $sort_by = 'center_name';
        } elseif ($sort_by == 'course.name') {
            $sort_by = 'course_name';
        } elseif ($sort_by == 'active.name') {
            $sort_by = 'is_active';
            $order = ($order == 'ASC') ? 'DESC' : 'ASC';
        }

        return $query->groupBy('learners.id')->orderBy($sort_by, $order);
    }

    /**
     * Center
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function center()
    {
        return $this->belongsTo(Center::class);
    }

    /**
     * Check whether the learner is active
     *
     * @param $email
     * @param $email_token
     * @return mixed
     */
    public static function isValidInvitedLearner($email, $email_token)
    {
        return Learner::where(
            [
                'email' => $email,
                'email_token' => $email_token,
                'is_active' => true,
                'status' => 'INVITED'
            ]
        )->first();
    }

    /**
     * Activate Learner account
     *
     * @param ActivateLearner $learner
     * @return bool
     */
    public function activate(ActivateLearner $learner)
    {
        $this->status = 'REGISTERED';

        $this->password = bcrypt($learner->password);

        if ($this->save()) {
            $this->fireModelEvent('activated', false);
            return true;
        }

        return false;
    }

    /**
     * Get all of the password resets.
     */
    public function passwordReset()
    {
        return $this->morphMany(PasswordReset::class, 'password_resettable');
    }

    /**
     * @param $email
     * @return mixed
     */
    public static function isValidAndRegistered($email)
    {
        return Learner::where(
            [
                'email' => $email,
                'is_active' => true,
                'status' => 'REGISTERED'
            ]
        )->first();
    }

    /**
     * Check whether the learner is active and registered
     *
     * @return boolean
     */
    public function isValidRegisteredLearner()
    {
        return $this->is_active && $this->status == 'REGISTERED';
    }

    /**
     * @param $email
     * @param $password
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

    /**
     * JWT Claims for learner
     *
     * @return array
     */
    public function generateJWTClaims()
    {
        return [
            'type' => 'learner',
            'email' => $this->email
        ];
    }

    /**
     * @param $email
     * @return mixed
     */
    public static function getActiveAccountByEmail($email)
    {
        return Learner::where(
            [
                'email' => $email,
                'is_active' => true,
                'status' => 'REGISTERED'
            ]
        )->first();
    }

    /**
     * save and log
     */
    public function pushAndLog()
    {
        $this->push();

        $this->fireModelEvent('fieldsUpdated', false);
    }

    public function getProgress($forceReload = false)
    {
        if ($this->id == 1) {
            return ['prev' => null, 'next' => null];
        }

        if (self::$progress && !$forceReload) {
            return self::$progress;
        }

        self::$progress = Progress::getProgress($this->id, $this->assignedCourse()->id);

        return self::$progress;
    }

    public function getActivityStatus($activity_id, $forceReload = false)
    {
        // Special user always has access and considered as completed
        if ($this->id == 1) {
            return 'COMPLETED';
        }

        if (!self::$completed_items || $forceReload) {
            self::$completed_items = Progress::loadLearnerActivityProgress($this->id, $this->courses[0]['id']);
        }

        return (in_array($activity_id, self::$completed_items['activities']) ? 'COMPLETED' : 'PENDING');
    }

    public function getLessonStatus($lesson_id, $forceReload = false, $lesson = null)
    {
        // Special user always has access and considered as completed
        if ($this->id == 1) {
            return 'COMPLETED';
        }

        if (!self::$completed_items || $forceReload) {
            self::$completed_items = Progress::loadLearnerActivityProgress($this->id, $this->courses[0]['id']);
        }

        if (isset(self::$completed_items['lessons'][$lesson_id])) {
            if (self::$completed_items['lessons'][$lesson_id]) {
                return 'COMPLETED';
            } else {
                return 'IN-PROGRESS';
            }
        }

        return 'PENDING';
    }

    /**
     * Units
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function units()
    {
        return $this->belongsToMany('App\Models\Units', 'learner_units');
    }
}
