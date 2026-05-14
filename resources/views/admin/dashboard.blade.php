<!doctype html>
<html>
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin</title>
  <style>
    body{font-family:system-ui,sans-serif;max-width:980px;margin:2rem auto;padding:0 1rem}
    nav a{margin-right:1rem}
    table{width:100%;border-collapse:collapse}
    th,td{border:1px solid #ddd;padding:.5rem}
  </style>
</head>
<body>
  <nav>
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <a href="{{ route('admin.galleries.index') }}">Gallery</a>
    <a href="{{ route('admin.collaborations.index') }}">Collaborations</a>
    <form method="post" action="{{ route('admin.logout') }}" style="display:inline">@csrf <button>Logout</button></form>
  </nav>
  <hr>

  @yield('content')

  @php
    // Bytes used by: collaboration media, collaboration logos, and GALLERY media
    $collabMediaUsed = \App\Models\MediaAsset::sum('file_bytes');
    $logoUsed        = \App\Models\Collaboration::sum('logo_bytes');
    $galleryUsed     = \App\Models\GalleryMediaAsset::sum('file_bytes');

    $used = (int) $collabMediaUsed + (int) $logoUsed + (int) $galleryUsed;
    $cap  = (int) config('quota.total_bytes');

    $pctRaw   = $cap > 0 ? ($used / $cap) * 100 : 0;
    $pctLabel = ($pctRaw > 0 && $pctRaw < 0.1) ? '<0.1' : number_format($pctRaw, 1);
    $barWidth = $used > 0 ? min(100, max(1, (int) round($pctRaw))) : 0;

    // Fallback formatter if helper isn't loaded
    if (!function_exists('formatBytes')) {
      function formatBytes($b){
        $u=['B','KB','MB','GB','TB']; $i=$b>0?floor(log($b,1024)):0;
        return round($b/pow(1024,$i),2).' '.$u[$i];
      }
    }
  @endphp

  <div style="border:1px solid #ddd;padding:1rem;margin:1rem 0">
    <strong>Storage</strong><br>
    Used: {{ formatBytes($used) }} of {{ formatBytes($cap) }} ({!! $pctLabel !!}%)
    <div style="height:8px;background:#eee;border-radius:4px;margin-top:.5rem;overflow:hidden">
      <div style="height:8px;width:{{ $barWidth }}%;background:#7c3aed"></div>
    </div>
    <div style="margin-top:.5rem;color:#555;font-size:.9rem">
      <div>• Collaborations media: {{ formatBytes($collabMediaUsed) }}</div>
      <div>• Collaboration logos: {{ formatBytes($logoUsed) }}</div>
      <div>• Gallery media: {{ formatBytes($galleryUsed) }}</div>
    </div>
  </div>

</body>
</html>
