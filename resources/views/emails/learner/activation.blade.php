@component('mail::message')
# Account Activation

Hi {{$learner->firstname}},

You have been invited to Language Link's LinkUP platform. Please click on this link to complete your registration.

@component('mail::button', ['url' => App::make('url')->to('/').'/learner/register?email='.$learner->email.'&token='.$learner->email_token])
    Confirm & Activate Account
@endcomponent

Thanks,<br>
{{ config('app.name') }} Team
@endcomponent