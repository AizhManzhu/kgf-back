@component('mail::message')
{!! $message !!}
@if($eventMemberId!=null)
    <br>
Ваш билет:
    <br>

<img style="margin-top: 1rem; width: 100%; height: 100%" src="https://kgf.cic.kz/storage/uploads/{{$eventMemberId}}.jpeg" alt="">
@endif
<br>
<span style="display: flex; justify-content: center">для завершения регистрации перейдите в бот KGF в телеграм</span>
<br>
@component('mail::button', ['url' => "https://t.me/KgfKZBot?start=".$hash])
    Нажмите для перехода в чат-бот
@endcomponent
@endcomponent




