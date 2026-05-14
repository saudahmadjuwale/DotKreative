@extends('admin.dashboard')

@section('content')
<h1>Collaborations</h1>
@if(session('ok')) <p style="color:green">{{ session('ok') }}</p> @endif

<p><a href="{{ route('admin.collaborations.create') }}">+ New Collaboration</a></p>

<table>
  <tr>
    <th>Logo</th><th>Brand</th><th>Slug</th><th>Year</th><th>Actions</th><th>Folder Size</th>
  </tr>
  @forelse($items as $row)
    <tr>
        @if($row->brand_logo_path)
        <td><img src="{{ asset('storage/'.$row->brand_logo_path) }}" alt="logo" style="height:28px"></td>
        @else
            <td>—</td>
        @endif
      <td>{{ $row->brand_name }}</td>
      <td>{{ $row->slug }}</td>
      <td>{{ $row->year ?? '—' }}</td>
      <td>
    <a href="{{ route('admin.collaborations.media', $row->id) }}">Manage Media</a>

    <form action="{{ route('admin.collaborations.destroy', $row->id) }}" method="POST" style="display:inline">
        @csrf
        @method('DELETE')
        <button type="submit" onclick="return confirm('Delete this collaboration?')">
            Delete
        </button>
    </form>
</td>
      @php
        $rowMedia = \App\Models\MediaAsset::where('collaboration_id', $row->id)->sum('file_bytes');
        $rowUsed  = $rowMedia + ($row->logo_bytes ?? 0);
    @endphp
      <td>{{ formatBytes($rowUsed) }}</td>
    </tr>
  @empty
    <tr><td colspan="4">No collaborations yet.</td></tr>
  @endforelse
</table>

{{ $items->links() }}
@endsection
