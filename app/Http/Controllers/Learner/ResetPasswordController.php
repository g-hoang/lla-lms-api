<?php

namespace App\Http\Controllers\Learner;

use App\Http\Requests\Learner\ResetPasswordRequest;
use App\Models\Learner;
use App\Models\PasswordReset;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller
{
    /**
     * Create a new controller instance.
     *
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Check Password Reset Token Expired
     *
     * @param Request $request http request
     *
     * @return mixed
     */
    public function check(Request $request)
    {
        try {
            $user = PasswordReset::where('email', '=', $request->get('email'))
                ->where('token', '=', $request->get('token'))
                ->firstOrFail();
            $timediff = Carbon::now()->diffInHours(Carbon::parse($user->created_at));
            if ($timediff < 24 && !$user->is_changed) {
                return $this->respondSuccess('The link has live', null);
            } elseif ($timediff < 24 && $user->is_changed) {
                return $this->respondNotFound('The link has been used.');
            } else {
                return $this->respondNotFound('The link has expired');
            }
        } catch (\Exception $e) {
            return $this->respondNotFound('The link has NOT found');
        }
    }

    /**
     * Reset user password
     *
     * @param ResetPasswordRequest $resetPasswordRequest
     * @return mixed
     */
    public function reset(ResetPasswordRequest $resetPasswordRequest)
    {
        try {
            $user = PasswordReset::where('email', '=', $resetPasswordRequest->email)
                ->where('token', '=', $resetPasswordRequest->token)
                ->firstOrFail();
            $timediff = Carbon::now()->diffInHours(Carbon::parse($user->created_at));

            if ($timediff < 24 && !$user->is_changed) {
                $learner = Learner::where('email', '=', $resetPasswordRequest->email)
                    ->where('is_active', '=', true)
                    ->where('status', '=', 'REGISTERED')
                    ->update(['password' => bcrypt($resetPasswordRequest->password)]);

                $reset = PasswordReset::where('email', '=', $resetPasswordRequest->email)
                    ->where('token', '=', $resetPasswordRequest->token)
                    ->update(['is_changed'=>true]);

                $request = new \Illuminate\Http\Request();
                $request->replace(['email' => $resetPasswordRequest->email, 'password' => $resetPasswordRequest->password]);

                $login = (new AuthController())->login($request);

                return $this->respondSuccess('sueecssfully updated', $login->original);

            } elseif ($timediff < 24 && $user->is_changed) {
                return $this->respondNotFound('The link has been used.');
            } else {
                return $this->respondNotFound('The link has expired');
            }
        } catch (\Exception $e) {
            return $this->respondNotFound('The link has NOT found');
        }
    }
}
