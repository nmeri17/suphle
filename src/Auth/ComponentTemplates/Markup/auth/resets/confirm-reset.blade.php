<h1>Create New Password</h1>

<form method="POST" action="/resets/new-password">

    <input
        type="hidden"
        name="_csrf"
        value="<?= $csrf_token ?>"
    >

    <input
        type="hidden"
        name="token"
        value="<?= $token ?>"
    >

    <input
        type="password"
        name="password"
        placeholder="New Password"
        required
    >

    <input
        type="password"
        name="password_confirmation"
        placeholder="Confirm Password"
        required
    >

    <button type="submit">
        Update Password
    </button>

</form>