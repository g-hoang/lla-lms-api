@component('mail::message')
# Password Reset
Hi {{$user->firstname}},

A request was made to reset your password. If you would like to reset your password, click on the link below. Please note that this link expires after 24 hours.
<br>
@component('mail::button', ['url' => App::make('url')->to('/').'/learner/reset-password?email='.$user->email.'&token='.$token])
    Choose new password
@endcomponent
Thanks,<br>
{{ config('app.name') }} Team
@endcomponent