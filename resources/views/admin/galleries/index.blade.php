@extends('admin.dashboard')

@section('content')
<h1>Galleries</h1>
<a href="{{ route('admin.galleries.create') }}" class="text-purple-700 font-semibold">+ New Gallery</a>

@php
  if (!function_exists('formatBytes')) {
    function formatBytes($b){ $u=['B','KB','MB','GB','TB']; $i=$b>0?floor(log($b,1024)):0; return round($b/pow(1024,$i),2).' '.$u[$i]; }
  }
@endphp

<table class="table-auto w-full mt-4 border">
  <thead>
    <tr class="bg-gray-100 text-left">
      <th class="p-2">Title</th>
      <th class="p-2">Slug</th>
      <th class="p-2">Photos</th>
      <th class="p-2">Published</th>
      <th class="p-2">Folder Size</th>
      <th class="p-2">Actions</th>
    </tr>
  </thead>
  <tbody>
    @forelse($items as $g)
      <tr class="border-t">
        <td class="p-2">{{ $g->title }}</td>
        <td class="p-2">{{ $g->slug }}</td>
        <td class="p-2">{{ $g->media_count }}</td>
        <td class="p-2">
          <form method="POST" action="{{ route('admin.galleries.publish', $g->id) }}">
            @csrf @method('PATCH')
            <button class="px-2 py-1 rounded {{ $g->is_published ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
              {{ $g->is_published ? 'Published' : 'Unpublished' }}
            </button>
          </form>
        </td>
        <td class="p-2">{{ formatBytes((int)($g->folder_bytes ?? 0)) }}</td>
        <td class="p-2 flex gap-3">
          <a href="{{ route('admin.galleries.media', $g->id) }}" class="text-purple-700">Manage Media</a>
          <form method="POST" action="{{ route('admin.galleries.destroy', $g->id) }}"
                onsubmit="return confirm('Delete entire gallery? This removes all media.')">
            @csrf @method('DELETE')
            <button class="text-red-600">Delete Gallery</button>
          </form>
        </td>
      </tr>
    @empty
      <tr><td colspan="6" class="p-4 text-center text-gray-500">No galleries yet.</td></tr>
    @endforelse
  </tbody>
</table>

<div class="mt-3">
  {{ $items->links() }}
</div>

@php
  $used = (int)$collabUsed + (int)$logoUsed + (int)$galleryUsed;
  $pct  = $capacity ? min(100, ($used / $capacity) * 100) : 0;
@endphp
<div class="mt-6 p-4 border rounded">
  <strong>Storage</strong><br>
  Used: {{ formatBytes($used) }} of {{ formatBytes($capacity) }} ({{ number_format($pct,1) }}%)
  <div style="height:10px;background:#eee;border-radius:999px;overflow:hidden;margin-top:6px;">
    <div style="height:100%;width:{{ $pct }}%;background:#7c3aed;"></div>
  </div>
  <ul style="margin-top:.5rem;color:#555;font-size:.9rem;list-style:disc;padding-left:1rem;">
    <li>Collaborations media: {{ formatBytes($collabUsed) }}</li>
    <li>Collaboration logos: {{ formatBytes($logoUsed) }}</li>
    <li>Gallery media: {{ formatBytes($galleryUsed) }}</li>
  </ul>
</div>
@endsection
