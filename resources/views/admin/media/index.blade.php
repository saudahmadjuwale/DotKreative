{{-- MEDIA PANEL – Premium Admin UI --}}
{{-- Expected: $collab, $images, $videos --}}

@php
    // Group images+videos into batches
    $all = $images->concat($videos);
    $batches = $all->groupBy(function($m){
        return $m->upload_batch_id ?: 'single-'.$m->id;
    });
@endphp

<style>
    body { background:#f5f7fa; }

    .media-page {
        max-width: 1250px;
        margin: 0 auto;
        padding: 24px 18px 40px;
        font-family: Inter, system-ui;
        color: #111827;
    }

    h1 {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .page-intro {
        color: #6b7280;
        font-size: 14px;
        margin-bottom: 24px;
    }

    /* Section Title */
    .section-title {
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        color: #6b7280;
        margin: 30px 0 10px;
        letter-spacing: .05em;
    }

    /* Card */
    .card {
        background:#fff;
        border-radius:14px;
        border:1px solid #e5e7eb;
        box-shadow:0 6px 20px rgba(0,0,0,0.06);
        padding:20px;
    }

    .btn-primary {
        background:#111827;
        color:#fff;
        padding:9px 16px;
        border:none;
        border-radius:8px;
        font-size:14px;
        cursor:pointer;
    }

    .btn-primary:hover { background:black; }

    .btn-ghost {
        background:#f3f4f6;
        border:1px solid #d1d5db;
        padding:6px 10px;
        border-radius:8px;
        font-size:13px;
        cursor:pointer;
    }

    .btn-ghost:hover { background:#e5e7eb; }

    .btn-danger {
        background:#dc2626;
        color:white;
        border:none;
        padding:7px 12px;
        border-radius:8px;
        font-size:13px;
        cursor:pointer;
    }
    .btn-danger:hover { background:#b91c1c; }

    /* BATCH GRID */
    .batch-grid {
        display:grid;
        grid-template-columns:repeat(auto-fill, minmax(280px, 1fr));
        gap:20px;
    }

    .batch-card {
        display:flex;
        flex-direction:column;
        gap:10px;
    }

    .thumb-grid {
        display:grid;
        grid-template-columns: repeat(3,1fr);
        gap:6px;
    }

    .thumb-grid img,
    .thumb-grid video {
        width:100%;
        height:85px;
        object-fit:cover;
        border-radius:8px;
        background:black;
    }

    .batch-caption {
        font-size:13px;
        margin-top:4px;
        color:#374151;
    }

    .batch-actions {
        display:flex;
        align-items:center;
        gap:10px;
        margin-top:4px;
    }

    /* Order input fields */
    .order-box {
        width:48px;
        padding:5px;
        font-size:12px;
        border:1px solid #d1d5db;
        border-radius:6px;
        margin-right:6px;
    }

    /* Small upload form */
    .small-form {
        margin-top:12px;
        padding-top:12px;
        border-top:1px dashed #e5e7eb;
    }

    .small-form input {
        width:100%;
        padding:6px 7px;
        border:1px solid #d1d5db;
        border-radius:8px;
        font-size:12px;
        margin-bottom:7px;
    }
</style>


<div class="media-page">
    <div class="container">
        <div class="flash-wrapper">
            @if(session('success'))
                <div class="flash flash-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="flash flash-error">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="flash flash-error">
                    <ul>
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    <h1>Media — {{ $collab->brand_name }}</h1>

    <p class="page-intro">
        Upload photos & videos for this collaboration. Files are grouped into batches automatically.
    </p>

    {{-- UPLOAD NEW BATCH --}}
    
    <div class="card">
        <form method="POST" action="{{ route('admin.collaborations.media.store',$collab->id) }}" enctype="multipart/form-data">
            <div class="section-title">Upload New Batch</div>
            @csrf

            <label>Choose Files</label>
            <input type="file" name="files[]" multiple required style="margin-bottom:10px;">

            <label>Shared Caption (Optional)</label>
            <input type="text" name="caption" placeholder="Eg: Campaign creatives">

            <button class="btn-primary" type="submit">Upload Batch</button>
        </form>
        @if($collab->brand_logo_path)
        <div style="margin-bottom:12px;">
            <div class="section-title">Change Logo</div>
            <img src="{{ asset('storage/'.$collab->brand_logo_path) }}"
                 style="height:60px;border-radius:6px;border:1px solid #e5e7eb;">
        </div>
    @endif

    <form method="POST"
          action="{{ route('admin.collaborations.updateLogo',$collab->id) }}"
          enctype="multipart/form-data">

        @csrf
        @method('PATCH')

        <input type="file" name="logo" required style="margin-bottom:10px;">

        <button class="btn-primary">
            Update Logo
        </button>

    </form>
    </div>

    {{-- Alerts --}}
    @if($errors->any())
        <div style="background:#fee2e2;border:1px solid #fecaca;padding:12px;border-radius:8px; margin-top:14px;">
            @foreach($errors->all() as $e)
                <div>{{ $e }}</div>
            @endforeach
        </div>
    @endif

    @if(session('ok'))
        <div style="background:#ecfdf5;border:1px solid #bbf7d0;padding:12px;border-radius:8px;margin-top:14px;">
            {{ session('ok') }}
        </div>
    @endif


    {{-- EXISTING BATCHES --}}
    <div class="section-title">Existing Media</div>

    @if($batches->isEmpty())
        <p style="color:#6b7280;">No media uploaded yet.</p>
    @else

        <div class="batch-grid">

            @foreach($batches as $gid => $group)

                @php
                    $isSingle = str_starts_with($gid,'single-');
                    $batchId = $isSingle ? null : $gid;
                    $items = $group->sortBy('batch_order');
                    $first = $items->first();
                    $caption = $first->caption;
                @endphp

                <div class="card batch-card">

                    {{-- Preview Thumbs (6 max) --}}
                    <div class="thumb-grid">
                        @foreach($items->take(6) as $m)
                            @if($m->type === "video")
                                <video src="{{ Storage::disk('public')->url($m->file_path) }}"></video>
                            @else
                                <img src="{{ Storage::disk('public')->url($m->file_path) }}">
                            @endif
                        @endforeach
                    </div>

                    <div style="font-size:13px;">
                        <strong>{{ $isSingle ? 'Single Item' : 'Batch' }}</strong> • {{ $items->count() }} item(s)
                        @if($caption)
                            <div class="batch-caption">“{{ \Illuminate\Support\Str::limit($caption,80) }}”</div>
                        @endif
                    </div>

                    


                    {{-- ACTIONS --}}
                    <div class="batch-actions">

                        @if($isSingle)
                            {{-- Edit single --}}
                            @php $m = $items->first(); @endphp

                            <a href="{{ route('admin.media.edit',$m->id) }}" class="btn-ghost">
                                Edit
                            </a>

                            <form method="POST" action="{{ route('admin.media.destroy',$m->id) }}">
                                @csrf @method('DELETE')
                                <button class="btn-danger" onclick="return confirm('Delete this item?')">
                                    Delete
                                </button>
                            </form>

                        @else
                            {{-- Edit whole batch --}}
                            <a href="{{ route('admin.media.batch.edit',[$collab->id,$batchId]) }}" class="btn-ghost">
                                Edit Batch
                            </a>

                            {{-- Delete batch --}}
                            <form method="POST" action="{{ route('admin.media.batch.delete',[$collab->id,$batchId]) }}"
                                  onsubmit="return confirm('Delete entire batch?');">
                                @csrf @method('DELETE')
                                <button class="btn-danger">Delete Batch</button>
                            </form>

                        @endif
                    </div>


                    {{-- Add more files to batch --}}
                    @if(!$isSingle)
                        <form method="POST" action="{{ route('admin.collaborations.media.store',$collab->id) }}"
                              enctype="multipart/form-data" class="small-form">
                            @csrf

                            <input type="hidden" name="batch_id" value="{{ $batchId }}">

                            <label style="font-size:12px;">Add more files</label>
                            <input type="file" name="files[]" multiple required>

                            <input type="text" name="caption" placeholder="Optional caption">

                            <button class="btn-ghost">Add</button>
                        </form>
                    @endif

                </div>

            @endforeach

        </div>

    @endif
</div>
