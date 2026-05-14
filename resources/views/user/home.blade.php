<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DotKreative - Home Page</title>
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
            <a class="active" href="{{route('home')}}">Home</a><a href="{{route('collabs.index')}}">Collaborations</a><a href="#contactUs">Contact Us</a>
        </nav>
    </header>
    <section id="hero">
        <h2>AI can’t shoot your<br> moments, We can!</h2>
        <p>Through the lens, we tell stories of people, places, and raw emotion.<br>  Welcome to our world of visual storytelling.</p>
        <a href="#contactUs">Lets Collaborate</a>
    </section>
    <section id="gallery_sec">
        <div class="wrap">
            <h3 class="sec-title">Captured Moments</h3>

            <div class="gallery-grid">
                @foreach($randomGalleryImages as $img)

                    <a class="g-card" href="{{ route('galleries') }}">
                        <img src="{{ Storage::disk('public')->url($img->file_path) }}"
                            alt="{{ $img->alt_text ?? 'Gallery Image' }}">
                    </a>

                @endforeach
            </div>

            <div class="cta-row">
                <a class="btn-ghost" href="/collabs">See More</a>
            </div>
        </div>
    </section>
    <section id="about">
        <h2>A Collective Eye for<br> Visual Storytelling</h2>
        <div class="aboutCont">
            <img src="{{ asset('/image/logo/logodot.png') }}" alt="">
            <div class="aboutText">
                <p>Dot Kreative is more than just a name — it's a collective of passionate visual storytellers. Formed by a group of creators with a shared love for photography, film, and creative expression, Dot Kreative captures the raw, real, and remarkable. Whether it's an intimate portrait, a dynamic collaboration, or a brand's visual identity every frame we craft tells a story worth remembering.</p>
                <div class="team">
                    <span>
                        <img src="{{ asset('/image/pfp/pfp.jpg') }}" alt="">
                        <strong>Abhishek Bhalerao</strong>
                    </span>
                    <span>
                        <img src="" alt="">
                        <strong>Mihir </strong>
                    </span>
                    
                </div>
            </div>
        </div>
    </section>
    <section id="collabHome">
        <h2>Trusted by<br> Industry Leaders</h2>
        <div class="collabs">
            @foreach ($collabs as $collab)
            <div class="cCard">
                @if ($collab->brand_logo_path)
                <a href="{{route('collabs.show', $collab->slug)}}"><img
                    src="{{ Storage::disk('public')->url($collab->brand_logo_path) }}"
                    alt="{{ $collab->brand_name }} logo"></a>
                @else
                <div class="cCard__placeholder">{{ \Illuminate\Support\Str::substr($collab->brand_name, 0, 1) }}</div>
                @endif
            </div>
            @endforeach
        </div>
        <a class="btn-ghost" href="/collabs">See More</a>
    </section>

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
    <script src="{{ asset('/js/mobileNav.js') }}"></script>
</body>
</html>