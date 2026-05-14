@extends('admin.dashboard')

@section('styles')
<style>
    :root {
        --bg: #f8fafc;
        --card: #ffffff;
        --border: #e2e8f0;
        --text: #1e293b;
        --subtext: #64748b;
        --purple: #6d28d9;
    }

    body {
        font-family: "Inter", system-ui, sans-serif;
    }

    .page-wrap {
        max-width: 1250px;
        margin: 0 auto;
        padding: 28px 20px 50px;
    }

    h1 {
        font-size: 28px;
        font-weight: 700;
        color: var(--text);
    }

    .section-title {
        margin-top: 26px;
        margin-bottom: 8px;
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: var(--subtext);
    }

    /* ---------- CARD ---------- */
    .card {
        background: var(--card);
        padding: 20px 22px;
        border-radius: 14px;
        border: 1px solid var(--border);
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        margin-bottom: 24px;
    }

    /* ---------- INLINE EDIT FIELDS ---------- */
    .inline-field {
        display: flex;
        gap: 14px;
        margin-bottom: 16px;
    }
    .inline-field input,
    .inline-field textarea {
        width: 100%;
        padding: 9px 12px;
        border-radius: 8px;
        border: 1px solid var(--border);
        font-size: 14px;
    }
    .btn-save {
        background: var(--purple);
        color: white;
        padding: 10px 16px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
    }
    .btn-save:hover {
        opacity: .9;
    }

    /* ---------- SETTINGS / BUTTONS ---------- */
    .pill {
        border-radius: 999px;
        padding: 6px 12px;
        font-size: 13px;
        border: 1px solid var(--border);
        background: var(--card);
        cursor: pointer;
    }

    .pill-pub {
        background: #dcfce7;
        border-color: #bbf7d0;
        color: #166534;
    }
    .pill-unpub {
        background: #fee2e2;
        border-color: #fecaca;
        color: #b91c1c;
    }

    .btn-delete {
        background: #fee2e2;
        border: 1px solid #fecaca;
        color: #b91c1c;
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 13px;
        cursor: pointer;
    }

    /* ---------- MEDIA GRID ---------- */
    #media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
        gap: 20px;
    }

    .media-card {
        background: var(--card);
        border-radius: 12px;
        border: 1px solid var(--border);
        overflow: hidden;
        box-shadow: 0 6px 18px rgba(0,0,0,0.05);
        cursor: grab;
        transition: 0.2s ease;
        position: relative;
    }
    .media-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    }

    .media-thumb img,
    .media-thumb video {
        width: 100%;
        height: 150px;
        object-fit: cover;
        display: block;
    }

    .media-info {
        padding: 10px 12px;
        font-size: 13px;
        color: var(--subtext);
    }

    .order-label {
        font-size: 12px;
        color: var(--text);
        margin-bottom: 6px;
    }

    /* ---------- HOVER ACTIONS ---------- */
    .media-actions {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.45);
        opacity: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 14px;
        transition: .2s;
    }
    .media-card:hover .media-actions {
        opacity: 1;
    }

    .action-btn {
        background: white;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 12px;
        border: 1px solid var(--border);
        cursor: pointer;
        font-weight: 600;
    }
    .action-btn.delete {
        background: #fee2e2;
        border-color: #fecaca;
        color: #b91c1c;
    }

    .edit-alt-input {
        width: 100%;
        padding: 6px;
        margin-top: 4px;
        border-radius: 6px;
        border: 1px solid var(--border);
        font-size: 12px;
    }
</style>
@endsection


@section('content')
<div class="page-wrap">

    {{-- TITLE + CAPTION INLINE UPDATE --}}
    <form method="POST" action="{{ route('admin.galleries.update.info', $gallery->id) }}">
        @csrf @method('PATCH')

        <div class="card">
            <h1>Edit Gallery</h1>

            <div class="inline-field">
                <input name="title" value="{{ $gallery->title }}" placeholder="Gallery Title">
                <textarea name="caption" rows="2" placeholder="Caption...">{{ $gallery->caption }}</textarea>
            </div>

            <button class="btn-save">Save Gallery Details</button>
        </div>
    </form>


    {{-- SETTINGS --}}
    <div class="section-title">Gallery Settings</div>
    <div class="card" style="display:flex; justify-content:space-between; align-items:center;">
        <form method="POST" action="{{ route('admin.galleries.publish', $gallery->id) }}">
            @csrf @method('PATCH')
            <button class="pill {{ $gallery->is_published ? 'pill-pub' : 'pill-unpub' }}">
                {{ $gallery->is_published ? 'Published – Visible on site' : 'Unpublished – Hidden' }}
            </button>
        </form>

        <form method="POST" action="{{ route('admin.galleries.destroy', $gallery->id) }}"
              onsubmit="return confirm('Delete entire gallery?')">
            @csrf @method('DELETE')
            <button class="btn-delete">Delete Gallery</button>
        </form>
    </div>


    {{-- UPLOAD --}}
    <div class="section-title">Upload New Media</div>
    <div class="card">
        <form method="POST" action="{{ route('admin.galleries.media.upload', $gallery->id) }}" enctype="multipart/form-data">
            @csrf
            <input type="file" name="media[]" multiple required>
            <p style="font-size:12px;color:var(--subtext);margin-top:8px;">Images & MP4 videos — Max 50MB each.</p>
            <button class="btn-save" style="margin-top:12px;">Upload</button>
        </form>
    </div>


    {{-- EXISTING MEDIA --}}
    <div class="section-title">Existing media in this gallery</div>

<div class="media-grid">
    @forelse($gallery->media as $m)
        @php
            $url = Storage::disk('public')->url($m->file_path);
        @endphp

        <div class="media-item">
            <div class="thumb">
                @if($m->type === 'image')
                    <img src="{{ $url }}" alt="">
                @else
                    <video src="{{ $url }}" muted></video>
                @endif
            </div>

            <div class="meta">
                <div class="field">
                    <label>Order</label>
                    <input type="number" class="order-input"
                           value="{{ $m->order_index }}"
                           onchange="updateOrder({{ $gallery->id }}, {{ $m->id }}, this.value)">
                </div>

                <form method="POST"
                      action="{{ route('admin.galleries.media.delete', [$gallery->id, $m->id]) }}"
                      onsubmit="return confirm('Delete this media?')">
                    @csrf @method('DELETE')
                    <button class="delete-btn">Delete</button>
                </form>
            </div>
        </div>

    @empty
        <p class="filters-note">No media yet. Upload something above.</p>
    @endforelse
</div>
@endsection



{{-- ===========================
    JS: Drag-Drop + Alt Update
============================== --}}
@section('scripts')
<script>
/* ---------- DRAG DROP ORDER ---------- */
document.addEventListener("DOMContentLoaded", () => {
    const grid = document.getElementById("media-grid");
    const orderUrl = grid.dataset.orderUrl;
    const csrf = '{{ csrf_token() }}';
    let dragged;

    grid.addEventListener("dragstart", e => {
        dragged = e.target;
        dragged.style.opacity = "0.5";
    });

    grid.addEventListener("dragend", e => {
        dragged.style.opacity = "1";
        saveOrder();
    });

    grid.addEventListener("dragover", e => {
        e.preventDefault();
        const target = e.target.closest(".media-card");
        if (target && target !== dragged) {
            const rect = target.getBoundingClientRect();
            const next = (e.clientY - rect.top) > (rect.height / 2);
            grid.insertBefore(dragged, next ? target.nextSibling : target);
        }
    });

    function saveOrder() {
        const items = [...grid.querySelectorAll(".media-card")];

        const payload = {
            order: items.map((item, i) => ({
                id: item.dataset.id,
                order_index: i
            }))
        };

        fetch(orderUrl, {
            method: "PATCH",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrf
            },
            body: JSON.stringify(payload)
        });
    }
});


/* ---------- UPDATE ALT TEXT INLINE ---------- */
function updateAlt(galleryId, mediaId, value) {
    fetch(`/admin/galleries/${galleryId}/media/${mediaId}/alt`, {
        method: "PATCH",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": '{{ csrf_token() }}'
        },
        body: JSON.stringify({ alt_text: value })
    });
}
</script>
@endsection
