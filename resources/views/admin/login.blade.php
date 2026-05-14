<!doctype html>
<html>
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Login</title>
  <style>body{font-family:system-ui,sans-serif;max-width:420px;margin:6rem auto;padding:1rem}
  form{display:grid;gap:.75rem}input,button{padding:.65rem .8rem;font-size:16px}</style>
</head>
<body>
  <h1>Admin Login</h1>
  @if($errors->any())<div style="color:#b00020">{{ $errors->first() }}</div>@endif
  <form method="post" action="{{ route('admin.login') }}">
    @csrf
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button>Login</button>
  </form>
</body>
</html>
