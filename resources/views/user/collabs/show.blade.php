<!doctype html>
<html>
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>{{ $collab->brand_name }} — Collaboration</title>
  <link rel="stylesheet" href="{{ asset('/css/style.css') }}">
</head>
<script src="/js/carousel.js" defer></script>
<body style="background:#f7f7f7;">
  <div id="header">
    <a class="backToAction" href="{{ route('collabs.index') }}">← &nbsp;&nbsp;All Collaborations</a>
    <div class="head">
      @if($collab->brand_logo_path)
      <img src="{{ Storage::disk('public')->url($collab->brand_logo_path) }}" alt="{{ $collab->brand_name }} logo">
      @endif
      @if($collab->year) <span class="meta">• {{ $collab->year }}</span> @endif
      @if($collab->location) <span class="meta">• {{ $collab->location }}</span> @endif
    </div>
  </div>
  <div class="miniSum">
    @if($collab->mini_summary)
      <h1>{{ $collab->brand_name }}</h1>
    @endif
    @if($collab->mini_summary)
      <span>{{ $collab->mini_summary }}</span>
    @endif
    @if($collab->website_url || $collab->instagram_url)
      <div style="margin-top:8px">
        @if($collab->website_url)
          <a class="btn" href="{{ $collab->website_url }}" target="_blank" rel="noopener">Visit Brand Site ↗</a>
        @endif
        @if($collab->instagram_url)
          <a class="btn" href="{{ $collab->instagram_url }}" target="_blank" rel="noopener">View on Instagram ↗</a>
        @endif
      </div>
    @endif
  </div>


  @if($collab->description)
    <div class="desc">{!! nl2br(e($collab->description)) !!}</div>
  @endif

 @php
  // Merge images + videos, then group by batch
  $all = $images->concat($videos);

  // Group by upload_batch_id; singles get a unique key
  $batches = $all->groupBy(function($m){
      return $m->upload_batch_id ?: 'single-'.$m->id;
  });
@endphp
<section class="colImages">
  <div class="masonry">
    @foreach ($batches as $gid => $group)
      <div class="set">
        <div class="carousel" data-carousel id="batch-{{ Str::slug($gid) }}">
          <div class="carousel__track">
            @foreach ($group->sortBy('batch_order') as $m)
              <div class="carousel__slide">
                @if($m->type === 'video')
                  <video src="{{ Storage::disk('public')->url($m->file_path) }}"
                        controls playsinline preload="metadata"></video>
                @else
                  <img src="{{ Storage::disk('public')->url($m->file_path) }}"
                      alt="{{ $m->alt_text ?? 'Photo' }}">
                @endif
              </div>
            @endforeach
          </div>

          @if($group->count() > 1)
            <div class="carousel__footer">
              <button class="carousel__ctrl" data-prev aria-label="Previous">‹</button>
              <div class="carousel__dots" data-dots>
                @for ($i = 0; $i < $group->count(); $i++)
                  <button data-dot="{{ $i }}" aria-label="Go to slide {{ $i+1 }}"></button>
                @endfor
              </div>
              <button class="carousel__ctrl" data-next aria-label="Next">›</button>
            </div>
          @endif
          @php $cap = optional($group->first())->caption; @endphp
          @if($cap)
            <div class="set__caption">{{ $cap }}</div>
          @endif
        </div>

      </div>
    @endforeach
  </div>
</section>
<script>
  
document.addEventListener("DOMContentLoaded", function () {

    // Get ALL videos inside the collaboration page
    const videos = document.querySelectorAll("video");

    videos.forEach(video => {
        video.addEventListener("play", () => {

            // Pause all other videos
            videos.forEach(v => {
                if (v !== video) {
                    v.pause();
                }
            });

        });
    });

});

</script>
</body>
</html>
