@component('mail::message')
# Krys & Tell Dental Clinic

{!! nl2br(e($replyBody)) !!}

@component('mail::panel')
Original message from {{ $contactMessage->name }} ({{ $contactMessage->email }}):  
{{ $contactMessage->message }}
@endcomponent

Thank you,  
Krys & Tell Dental Clinic
@endcomponent
