@extends('admin.dashboard')

@section('content')
<h1>New Collaboration</h1>
@if($errors->any()) <div style="color:#b00020">{{ $errors->first() }}</div> @endif

<form method="post" action="{{ route('admin.collaborations.store') }}" enctype="multipart/form-data">
  @csrf

  <label>Brand Name<br>
    <input name="brand_name" value="{{ old('brand_name') }}" required>
  </label><br><br>

  <label>Slug (leave blank for auto)<br>
    <input name="slug" value="{{ old('slug') }}">
  </label><br><br>

  <label>Mini Summary (160 chars)<br>
    <input name="mini_summary" maxlength="160" value="{{ old('mini_summary') }}">
  </label><br><br>

  <label>Location<br>
    <input name="location" value="{{ old('location') }}">
  </label><br><br>

  <label>Year<br>
    <input type="number" name="year" min="1900" max="2100" value="{{ old('year') }}">
  </label><br><br>

  <label>Website URL<br>
    <input type="url" name="website_url" value="{{ old('website_url') }}">
  </label><br><br>

  <label>Instagram URL<br>
    <input type="url" name="instagram_url" value="{{ old('instagram_url') }}" placeholder="https://www.instagram.com/reel/...">
  </label><br><br>

  <label>Description<br>
    <textarea name="description" rows="6" style="width:100%">{{ old('description') }}</textarea>
  </label><br><br>

  <label>Brand Logo<br>
    <input type="file" name="brand_logo" accept="image/*">
  </label><br><br>

  <button>Create</button>
</form>
@endsection
