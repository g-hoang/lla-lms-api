@component('mail::message')
# Account Activation

Hi {{$user->firstname}},

You have been invited to Language Link's LinkUP platform. Please click on this link to complete your registration.

@if(false)
@component('mail::panel')
    `Name: {{ $user->getFullNameAttribute() }}`<br>
    `Email: {{ $user->email }}`
@endcomponent
@endif

@component('mail::button', ['url' => App::make('url')->to('/').'/user/register?email='.$user->email.'&token='.$user->email_token])
    Confirm & Activate Account
@endcomponent

Thanks,<br>
{{ config('app.name') }} Team
@endcomponent