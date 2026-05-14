@extends('admin.dashboard')

@section('content')
<h1>Edit media — {{ $collab->brand_name }}</h1>
<p><a href="{{ route('admin.collaborations.media', $collab->id) }}">← Back</a></p>

@if ($errors->any())
  <div style="color:#b00020;margin-bottom:12px;">
    @foreach ($errors->all() as $e) <div>{{ $e }}</div> @endforeach
  </div>
@endif

<form method="POST" action="{{ route('admin.media.update', $media->id) }}" enctype="multipart/form-data">
  @csrf @method('PUT')

  <div style="max-width:560px">
    <div style="margin-bottom:10px">
      <label>Caption</label>
      <input type="text" name="caption" value="{{ old('caption',$media->caption) }}" style="width:100%">
    </div>

    <div style="margin-bottom:10px">
      <label>Alt text</label>
      <input type="text" name="alt_text" value="{{ old('alt_text',$media->alt_text) }}" style="width:100%">
    </div>

    @if($media->upload_batch_id)
    <div style="margin:8px 0;">
      <label style="display:inline-flex;gap:6px;align-items:center;">
        <input type="checkbox" name="sync_caption" value="1" checked>
        Apply caption to the whole batch
      </label>
    </div>
    @endif

    <div style="margin:12px 0">
      <label>Replace file (optional)</label>
      <input type="file" name="file" accept=".jpg,.jpeg,.png,.webp,.gif,.mp4">
    </div>

    <button type="submit">Save</button>
  </div>
</form>
@endsection
