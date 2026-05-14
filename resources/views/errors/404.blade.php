<!doctype html>
<html>
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Page Not Found</title>
  <style>
    body{font-family:system-ui,sans-serif;display:grid;place-items:center;min-height:100dvh;margin:0}
    .box{max-width:560px;padding:2rem;text-align:center}
    h1{font-size:44px;margin:0 0 .25rem 0}
    p{color:#555;margin:.25rem 0 1rem}
    a{display:inline-block;padding:.6rem 1rem;border:1px solid #222;text-decoration:none;color:#222}
  </style>
</head>
<body>
  <div class="box">
    <h1>404</h1>
    <p>Oops—this page doesn’t exist.</p>
    <a href="{{ route('home') }}">Go to Home</a>
  </div>
</body>
</html>
