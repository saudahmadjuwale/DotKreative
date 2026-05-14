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
            <a href="{{route('home')}}">Home</a><a href="{{route('collabs.index')}}">Collaborations</a><a href="#contactUs">Contact Us</a>
        </div>
        <nav>
            <a  href="{{route('home')}}">Home</a><a href="{{route('collabs.index')}}">Collaborations</a><a href="#contactUs">Contact Us</a>
        </nav>
    </header>
<div class="gallery-wrap">

    <h1 class="gallery-title">Captured Galleries</h1>

    <div class="gallery-masonry">

        @foreach($galleries as $gallery)

            @php 
                $first = $gallery->media->first();
                $url = $first ? Storage::disk('public')->url($first->file_path) : null;
            @endphp

            <div class="gallery-item" onclick="openGallery({{ $gallery->id }})">

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

<div id="lightbox" class="lightbox">
    <span id="closeBtn" class="close-btn" onclick="closeLightbox()">&times;</span>

    
    <div id="lightbox-content"></div>
    <span id="prevBtn" class="nav-btn left" onclick="prevImg()">&#10094;</span>
    <span id="nextBtn" class="nav-btn right" onclick="nextImg()">&#10095;</span>
</div>
<section id="contactUs">
        <h2>Al doesn't feel emotions. We capture them<br> live, raw, and real</h2>
        <form action="" method="">
            <h3>Contact Us</h3>
            <label for="">Enter Your details below we'll response soon.</label>
            <input type="text" placeholder="Enter your Name" required>
            <input type="text" placeholder="Enter your Email (optional)">
            <input type="text" placeholder="Enter your Number" required>
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
    let galleries = @json($galleries);
    let active = [];
    let index = 0;

    function openGallery(id) {
        const g = galleries.find(x => x.id === id);
        active = g.media;
        index = 0;

        showSlide();
        document.getElementById('lightbox').classList.add('active');

        toggleNavButtons();
    }

    function toggleNavButtons() {
        const prev = document.getElementById("prevBtn");
        const next = document.getElementById("nextBtn");

        if (active.length <= 1) {
            prev.style.display = "none";
            next.style.display = "none";
        } else {
            prev.style.display = "block";
            next.style.display = "block";
        }
    }

    function showSlide() {
        const m = active[index];
        const c = document.getElementById('lightbox-content');
        const url = "/storage/" + m.file_path;

        c.innerHTML = (m.type === "image")
            ? `<img src="${url}">`
            : `<video src="${url}" controls autoplay></video>`;
    }

    function nextImg() {
        if (active.length <= 1) return;
        index = (index + 1) % active.length;
        showSlide();
    }

    function prevImg() {
        if (active.length <= 1) return;
        index = (index - 1 + active.length) % active.length;
        showSlide();
    }

    function closeLightbox() {
        document.getElementById('lightbox').classList.remove('active');
    }
</script>
<script src="{{ asset('/js/mobileNav.js') }}"></script>
</body>
</html>
