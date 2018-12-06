<?php

namespace App\Providers;

use App\Models\Activity;
use App\Models\ActivityComponent;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Permission;
use App\Models\Unit;
use App\Models\User;
use App\Policies\ActivityComponentPolicy;
use App\Policies\ActivityPolicy;
use App\Policies\CoursePolicy;
use App\Policies\LessonPolicy;
use App\Policies\UnitPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Course::class => CoursePolicy::class,
        Unit::class => UnitPolicy::class,
        Lesson::class =>LessonPolicy::class,
        Activity::class =>ActivityPolicy::class,
        ActivityComponent::class => ActivityComponentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        foreach ($this->getPermissions() as $permission) {
            $roles = ($permission->roles->pluck('id')->toArray());

            Gate::define($permission->name, function ($user) use ($permission, $roles) {
                return  in_array($user->role_id, $roles) || $user->role_id == 1; //Admin has full access
            });
        }
    }

    protected function getPermissions()
    {
        return Permission::with('roles')->get();
    }
}
