<?php

namespace App\Http\Controllers;

use App\Events\AdminActivationResend;
use App\Http\Requests\ActivateUser;
use App\Http\Requests\StoreUser;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use App\Linkup\Facades\Search;
use App\Http\Resources\User as UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class UserController extends ApiController
{
    /**
     * Display a listing of users.
     *
     * @param Request $request Http Request
     *
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorize('user.list');

        $users = Search::users($request)
            ->with(['role']);

        $size = $request->get('pageSize', 20);

        return UserResource::collection($users->paginate((int) $size));
        //return UserResource::collection($users->get());
    }


    /**
     * Store a newly created user.
     *
     * @param StoreUser $storeUser Object
     *
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(StoreUser $storeUser)
    {
        $this->authorize('user.create');

        $user = User::create(
            [
                'email' => $storeUser->email,
                'firstname' => htmlspecialchars($storeUser->firstname),
                'lastname' => htmlspecialchars($storeUser->lastname),
                'role_id' => 1,
            ]
        );

        $msg = "We've sent an invite to ".$user->email.", it should arrive in a few seconds";
        return $this->respondCreated($msg, new UserResource($user));
    }

    /**
     * Show user
     *
     * @param id $id id
     *
     * @return UserResource|mixed
     * @throws AuthenticationException
     */
    public function show($id)
    {

        if ($user = User::find($id)) {
            if (Auth::user()->cant('view', $user)) {
                throw new AuthenticationException();
            }

            return new UserResource($user);
        }

        return $this->respondNotFound('User Not Found');
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User $user
     *
     * @return void
     * @throws AuthenticationException
     */
    public function update(Request $request, User $user)
    {
        if (Auth::user()->cant('update', $user)) {
            throw new AuthenticationException();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User $user
     * @return void
     * @throws AuthenticationException
     */
    public function destroy(User $user)
    {
        if (Auth::user()->cant('delete', $user)) {
            throw new AuthenticationException();
        }
    }

    /**
     * Activate User
     *
     * @param ActivateUser $userData Object
     *
     * @return mixed
     */
    public function activate(ActivateUser $userData)
    {

        if ($user = User::isValidInvitedUser($userData->email, $userData->token)) {
            return $user->activate($userData)
                ? $this->respondSuccess('Account has been activated!')
                : $this->respondForBadRequest('Can not activate');


        }

        return $this->respondForBadRequest('Invalid Email or Token');
    }

    /**
     * Validate and redirect the user to front end
     *
     * @param Request $request Http Request Object
     *
     * @return mixed
     */
    public function register(Request $request)
    {
        $email = htmlspecialchars($request->get('email'));
        $token = htmlspecialchars($request->get('token'));

        if (!$email || !$token) {
            return Redirect::to(env('FRONTEND_URL').'/login');
        }

        if ($user = User::isValidInvitedUser($email, $token)) {
            $url = '/setup-password?email='.$email.'&token='.$token;
            return Redirect::to(env('FRONTEND_URL').$url);
        };

        return Redirect::to(env('FRONTEND_URL').'/login');
    }

    /**
     * Change user is_active state
     *
     * @param int     $id      Id
     * @param Request $request Http Request
     *
     * @throws AuthenticationException
     * @return mixed
     */
    public function updateStatus($id, Request $request)
    {
        if ($user = User::find($id)) {
            if ($user == Auth::user()) {
                return $this->invalidArguments('Can not update');
            }

            if (Auth::user()->cant('update', $user)) {
                throw new AuthenticationException();
            }

            $state = $request->post('active');

            if (in_array($state, [1, 0])) {
                if ($user->changeIsActiveState($state)) {
                    return $this->respondSuccess('Status updated');
                };
            }

            return $this->invalidArguments('Invalid State Value');

        }

        return $this->respondNotFound('User Not Found');
    }

    /**
     * Resend admin user activation email
     *
     * @param Request $request
     * @return mixed
     */
    public function resendActivationEmail(Request $request)
    {
        if($user = User::find($request->user_id)){

            if(!$user->is_active){
                return $this->respondNotFound('User not active');
            }

            if($user->status == 'REGISTERED'){
                return $this->respondNotFound('User already registered');
            }

            event(new AdminActivationResend($user));

            return $this->respondSuccess('Activation email has been sent!');
        }

        return $this->respondNotFound('Admin user not found');
    }
}
