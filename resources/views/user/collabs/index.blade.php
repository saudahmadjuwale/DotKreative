<!doctype html>
<html>
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Collaborations</title>
  <link rel="stylesheet" href="{{ asset('/css/style.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body style="background-color:white;">
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
            <a  href="{{route('home')}}">Home</a><a  href="{{route('galleries')}}">Gallery</a><a class="active" href="{{route('collabs.index')}}">Collaborations</a><a id="cus" href="#contactUs">Contact Us</a>
        </nav>
        
    </header>
  <section id="collabList">
    <div class="collab-heading">

        <span>TRUSTED PARTNERSHIPS</span>

        <h2>
            Brands We've <br> Collaborated With
        </h2>

        <p>
            From luxury fashion campaigns to cinematic product launches,
            we create visuals that connect brands with human emotions.
        </p>

    </div>
    <div class="grid">
      @foreach($collabs as $c)
        <a class="card" href="{{ route('collabs.show', $c->slug) }}">
          <div class="brand">
            @if($c->brand_logo_path)
              <img src="{{ Storage::disk('public')->url($c->brand_logo_path) }}" alt="{{ $c->brand_name }} logo">
            @endif
          </div>
          @if($c->mini_summary)
            <div class="sum">{{ $c->mini_summary }}</div>
          @endif
          @if($c->instagram_url)
            <div style="margin-top:6px;font-size:13px;color:#6b21a8">View on Instagram →</div>
          @endif
        </a>
      @endforeach
    </div>

    <div style="margin-top:20px">
      {{ $collabs->links() }}
    </div>
  </section>
  <section id="contactUs">
        <h2>Al doesn't feel emotions. We capture them<br> live, raw, and real</h2>
        <form action="{{ route('contact.submit') }}" method="POST">
          @csrf
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
  <script src="{{ asset('/js/mobileNav.js') }}"></script>
</body>
</html>
