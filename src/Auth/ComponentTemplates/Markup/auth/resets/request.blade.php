<h1>Forgot Password</h1>

<form method="POST" action="/resets/mail">

    <input
        type="hidden"
        name="_csrf"
        value="<?= $csrf_token ?>"
    >

    <input
        type="email"
        name="email"
        placeholder="Email Address"
        required
    >

    <button type="submit">
        Send Reset Link
    </button>

</form>