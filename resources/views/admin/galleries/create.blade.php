@extends('admin.dashboard')

@section('content')
<h1>New Gallery</h1>

@if(session('ok')) <div class="bg-green-100 border p-2 my-2 text-green-800">{{ session('ok') }}</div> @endif
@if($errors->any())
  <div class="bg-red-100 border p-2 my-2 text-red-700">
    <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
@endif

<form method="post" action="{{ route('admin.galleries.store') }}" enctype="multipart/form-data" class="space-y-3">
  @csrf
  <div>
    <label class="block font-semibold">Title *</label>
    <input type="text" name="title" class="border rounded p-2 w-full" required>
  </div>

  <div>
    <label class="block font-semibold">Caption</label>
    <textarea name="caption" class="border rounded p-2 w-full"></textarea>
  </div>

  <label class="inline-flex items-center gap-2">
    <input type="checkbox" name="is_published" value="1"> Publish now
  </label>

  <div>
    <label class="block font-semibold">Media (images/videos)</label>
    <input type="file" name="media[]" multiple required class="border rounded p-2 w-full">
  </div>

  <button class="bg-purple-600 text-white px-4 py-2 rounded">Save</button>
</form>
@endsection
