@component('mail::message')
# Student Assignment Notification

Dear {{ $instructor->name }},

Here are the students assigned to you:

@foreach($students as $student)
- **Name:** {{ $student->full_name }}
- **Phone:** {{ $student->phone_number }}
- **Location:** {{ $student->location }}
- **Company:** {{ $student->hosting_company }}

@endforeach

Thank you for your mentorship!



Best regards,
{{ config('app.name') }}
@endcomponent