<form action="/register" method="POST">
    @csrf
    <input type="text" name="name" value="{{ $payload_storage->getKey('name') }}" placeholder="Name">
    <input type="email" name="email" value="{{ $payload_storage->getKey('email') }}" placeholder="Email">
    <input type="password" name="password">
    
    @if($validation_errors)
        <div class="error">Check your inputs and try again.</div>
    @endif
    <button type="submit">Register</button>
</form>