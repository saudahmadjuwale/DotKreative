@extends('admin.dashboard')

@section('content')
<div style="max-width:1100px;margin:auto;padding:20px;">
    <h1 style="font-size:26px;font-weight:700;margin-bottom:6px;">
        Batch Edit — {{ $collab->brand_name }}
    </h1>

    <p style="margin-bottom:16px;">
        <a href="{{ route('admin.collaborations.media', $collab->id) }}" style="color:#4f46e5;font-weight:500;">
            ← Back to Media
        </a>
    </p>

    {{-- Errors --}}
    @if ($errors->any())
        <div style="background:#fee2e2;border:1px solid #fca5a5;padding:10px;border-radius:8px;margin-bottom:14px;color:#991b1b;">
            @foreach ($errors->all() as $e)
                <div>{{ $e }}</div>
            @endforeach
        </div>
    @endif

    {{-- SUCCESS --}}
    @if(session('ok'))
        <div style="background:#ecfdf5;border:1px solid #6ee7b7;padding:10px;border-radius:8px;margin-bottom:14px;color:#065f46;">
            {{ session('ok') }}
        </div>
    @endif

    {{-- UPDATE BATCH FORM --}}
    <form id="batch-form"
          method="POST"
          action="{{ route('admin.media.batch.update', [$collab->id, $batchId]) }}">
        @csrf
        @method('PUT')

        <div style="margin:16px 0;">
            <label style="font-size:14px;font-weight:600;">Shared Caption for this batch</label>
            <input type="text" name="caption"
                   value="{{ old('caption', $sharedCaption) }}"
                   style="width:100%;padding:8px;border-radius:8px;border:1px solid #ccc;margin-top:6px;">
            <div style="font-size:12px;color:#666;margin-top:4px;">
                This caption will be applied to every file in this batch.
            </div>
        </div>

        {{-- MEDIA ITEMS GRID --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:18px;margin-top:20px;">
            @foreach ($items as $it)
                <div style="border:1px solid #e5e7eb;border-radius:12px;padding:14px;background:white;display:flex;flex-direction:column;gap:10px;">

                    {{-- Header (ID + Delete Btn) --}}
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <div style="font-size:12px;color:#555;">
                            ID: {{ $it->id }} • {{ strtoupper($it->type) }}
                        </div>
                    </div>

                    {{-- Thumbnail --}}
                    <div style="aspect-ratio:16/10;border-radius:10px;overflow:hidden;border:1px solid #ddd;">
                        @if($it->type === 'video')
                            <video src="{{ Storage::disk('public')->url($it->file_path) }}"
                                   style="width:100%;height:100%;object-fit:cover;" controls></video>
                        @else
                            <img src="{{ Storage::disk('public')->url($it->file_path) }}"
                                 style="width:100%;height:100%;object-fit:cover;">
                        @endif
                    </div>

                    {{-- Alt text --}}
                    <div>
                        <label style="font-size:13px;font-weight:600;">Alt Text</label>
                        <input type="text"
                               name="alt_text[{{ $it->id }}]"
                               value="{{ old("alt_text.$it->id", $it->alt_text) }}"
                               style="width:100%;padding:7px;border-radius:6px;border:1px solid #ccc;">
                    </div>

                    {{-- Order --}}
                    <div>
                        <label style="font-size:13px;font-weight:600;">Order</label>
                        <input type="number"
                               name="order[{{ $it->id }}]"
                               value="{{ old("order.$it->id", $it->batch_order) }}"
                               style="width:120px;padding:7px;border-radius:6px;border:1px solid #ccc;">
                        <div style="font-size:12px;color:#777;margin-top:3px;">Lower number appears first.</div>
                    </div>
                    
                    <button class="btn-danger">Submit</button>
                  </form>
                    <form method="POST"  action="{{ route('admin.media.destroy',$it->id) }}">
                        @csrf @method('DELETE')
                        <button type="submit" onclick="return confirm('Delete this media file?');">
                            Delete
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
        
        </div>

  

</div>
@endsection


