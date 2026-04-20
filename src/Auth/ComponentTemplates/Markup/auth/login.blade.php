<form action="/auth/login" method="POST">
    <input type="hidden" name="csrf_token" value="{{ $csrf_token }}">
    
    <input type="email" name="email" value="{{ $payload_storage->getKey('email') }}">
    <input type="password" name="password">

    @if($validation_errors)
        <ul class="errors">
            @foreach($validation_errors as $error) <li>{{ $error }}</li> @endforeach
        </ul>
    @endif
    <button type="submit">Login</button>
</form>