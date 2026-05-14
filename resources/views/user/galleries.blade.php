<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DotKreative - Galleries</title>
    <link rel="stylesheet" href="{{ asset('/css/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body>
<header>
        <div class="logo">
            <h1>DOT.</h1>
        </div>
        <i id="menuBtn" class="fa-solid fa-bars"></i>
        <div id="mobNav">
            <i id="cancel" class="fa-solid fa-xmark"></i>
            <a href="{{route('home')}}">Home</a><a href="{{route('galleries')}}">Gallery</a><a href="{{route('collabs.index')}}">Collaborations</a><a href="#contactUs">Contact Us</a>
        </div>
        <nav>
            <a  href="{{route('home')}}">Home</a><a class="active" href="{{route('galleries')}}">Gallery</a><a href="{{route('collabs.index')}}">Collaborations</a><a id="cus" href="#contactUs">Contact Us</a>
        </nav>
        
    </header>
<div class="gallery-wrap">

    <div class="gallery-heading">

    <span class="gallery-subtitle">
        VISUAL JOURNALS
    </span>

    <h1>
        Captured Galleries
    </h1>

    <p>
        Real emotions, untold stories, and timeless moments —
        captured through lenses that see beyond the ordinary.
    </p>

</div>

    <div class="gallery-masonry">

        @foreach($galleries as $gallery)

            @php 
                $first = $gallery->media->first();
                $url = $first ? Storage::disk('public')->url($first->file_path) : null;
            @endphp

            <div class="gallery-item"

    data-title="{{ $gallery->title }}"
    data-caption="{{ $gallery->caption }}"
    data-date="{{ $gallery->created_at->diffForHumans() }}"

    data-media='@json(
        $gallery->media->map(function($media){
            return [
                "type" => $media->type,
                "url" => Storage::disk("public")->url($media->file_path)
            ];
        })
    )'

    onclick="openGallery(this)"
>

                <div class="gallery-thumb">
                    @if($first)
                        @if($first->type === 'image')
                            <img src="{{ $url }}" alt="">
                        @else
                            <video src="{{ $url }}" muted></video>
                        @endif
                    @endif
                </div>

                <div class="gallery-meta">
                    <h3>{{ $gallery->title }}</h3>
                    <span>{{ $gallery->created_at->diffForHumans() }}</span>
                </div>

            </div>

        @endforeach

    </div>

</div>
<div class="gallery-modal" id="galleryModal">

    <div class="gallery-overlay"></div>

    <div class="gallery-content">

        <button class="close-gallery" onclick="closeGallery()">
            ✕
        </button>

        <div class="gallery-image-wrap">

            <button class="gallery-nav prev" onclick="prevSlide()">
                ❮
            </button>

            <div id="galleryMediaContainer"></div>

            <button class="gallery-nav next" onclick="nextSlide()">
                ❯
            </button>

        </div>

        <div class="gallery-details">

    <div class="gallery-content-inner">

        <span class="gallery-badge">
            Visual Story
        </span>

        <h2 id="galleryModalTitle"></h2>

        
        <p id="galleryModalCaption"></p>
        <div class="gallery-date-wrap">

            <span id="galleryModalDate"></span>

        </div>

    </div>

</div>

    </div>

</div>
<section id="contactUs">
        <h2>Al doesn't feel emotions. We capture them<br> live, raw, and real</h2>
        <form action="" method="">
            <h3>Contact Us</h3>
            <label for="">Enter Your details below we'll response soon.</label>
            <input name="name" type="text" placeholder="Enter your Name" required>
            <input name="email" type="text" placeholder="Enter your Email (optional)">
            <input name="number" type="text" placeholder="Enter your Number" required>
            <button>Submit</button>
        </form>
    </section>
    <footer>
        <div class="footCont">
            <div class="footlogo">
                <img src="{{ asset('/image/logo/darkdotlogo.png') }}" alt="">
            </div>
            <div class="footText">
                <h1>DotKreative</h1>
                <p>DOT is a photography studio crafting brand stories through stills and motion. From candid moments to campaign work, we partner with teams to plan, shoot, and deliver visuals that move people. For collaborations or bookings, reach us by clicking on below icon —<strong> © 2025 DOT.</strong></p>
            </div>
        </div>
        <div class="footSocial">
            <a href=""><img src="{{ asset('/image/icon/instagram.png') }}" alt=""></a>
            <a href=""><img src="{{ asset('/image/icon/gmail.png') }}" alt=""></a> 
        </div>
        <div class="footCopyR">
            <p>© 2025 Dot Kreative · All Rights Reserved.</p>
        </div>
    </footer>
<script>

    const modal = document.getElementById('galleryModal');

    let currentGalleryMedia = [];
    let currentIndex = 0;

    function openGallery(element){

        const title = element.dataset.title;
        const caption = element.dataset.caption;
        const date = element.dataset.date;

        currentGalleryMedia = JSON.parse(element.dataset.media);

        currentIndex = 0;

        modal.classList.add('active');

        document.getElementById('galleryModalTitle').innerText = title;
        document.getElementById('galleryModalCaption').innerText = caption;
        document.getElementById('galleryModalDate').innerText = date;

        renderMedia();

        toggleButtons();
    }

    function renderMedia(){

        const container = document.getElementById('galleryMediaContainer');

        const media = currentGalleryMedia[currentIndex];

        if(media.type === 'image'){

            container.innerHTML = `
                <img src="${media.url}" alt="">
            `;

        }else{

            container.innerHTML = `
                <video src="${media.url}" controls autoplay></video>
            `;
        }
    }

    function nextSlide(){

        if(currentIndex < currentGalleryMedia.length - 1){

            currentIndex++;

            renderMedia();

            toggleButtons();
        }
    }

    function prevSlide(){

        if(currentIndex > 0){

            currentIndex--;

            renderMedia();

            toggleButtons();
        }
    }

    function toggleButtons(){

        const prev = document.querySelector('.gallery-nav.prev');
        const next = document.querySelector('.gallery-nav.next');

        prev.style.display =
            currentIndex === 0 ? 'none' : 'flex';

        next.style.display =
            currentIndex === currentGalleryMedia.length - 1
            ? 'none'
            : 'flex';
    }

    function closeGallery(){

        modal.classList.remove('active');

        document.getElementById('galleryMediaContainer').innerHTML = '';
    }

    // ESC CLOSE
    document.addEventListener('keydown', function(e){

        if(e.key === "Escape"){
            closeGallery();
        }

        if(e.key === "ArrowRight"){
            nextSlide();
        }

        if(e.key === "ArrowLeft"){
            prevSlide();
        }

    });

    // CLICK OUTSIDE CLOSE
    document.querySelector('.gallery-overlay')
        .addEventListener('click', closeGallery);

</script>
<script src="{{ asset('/js/mobileNav.js') }}"></script>
</body>
</html>
