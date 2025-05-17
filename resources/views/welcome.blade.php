<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reservasi Restoran Digital</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
</head>
<body>

    <nav id="navbar" class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="{{ asset('images/Logo Resto Online.png') }}" alt="Logo" class="me-2" style="height: 40px;">
                RestoOnline
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#menu">Menu</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">Tentang</a></li>
                    <li class="nav-item"><a class="nav-link" href="#aplikasi">Aplikasi</a></li>
                    <li class="nav-item"><a class="nav-link" href="#our_team">Tim</a></li>
                    <li class="nav-item"><a class="nav-link" href="#location">Lokasi</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Masuk</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <section id="home" class="hero position-relative text-white d-flex align-items-center overflow-hidden" style="min-height: 100vh;">
        <div class="position-absolute top-0 start-0 w-100 h-100 overlay-dark"></div>

        <div class="container text-center position-relative z-2">
            <h1 class="display-4 fw-bold animate__animated animate__fadeInDown">
                Reservasi & Pemesanan Restoran Digital
            </h1>
            <p class="lead animate__animated animate__fadeInUp animate__delay-0.5s">
                Pesan meja, pilih menu, dan nikmati kemudahan layanan restoran modern.
            </p>
            <a href="#aplikasi" class="btn btn-lg btn-warning mt-3 animate__animated animate__zoomIn animate__delay-1s">
                Mulai Sekarang
            </a>
        </div>
    </section>


    <section id="menu" class="menu-main py-5" data-aos="fade-up">
        <div class="menu-box-shadow">
            <div class="container">

                <div class="text-center mb-4">
                    <h2 class="block-title text-change active">Menu</h2>
                    <p class="title-caption text-change active">Explore our delicious selections</p>
                </div>

                <ul class="nav nav-pills justify-content-center mb-4" id="menuTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="food-tab" data-bs-toggle="pill" data-bs-target="#food" type="button" role="tab" aria-controls="food" aria-selected="true">
                            <h2>FOOD</h2><p><i class="fas fa-utensils fa-2x"></i></p>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="beverage-tab" data-bs-toggle="pill" data-bs-target="#beverage" type="button" role="tab" aria-controls="beverage" aria-selected="false">
                            <h2>BEVERAGE</h2><p><i class="fas fa-wine-glass-alt fa-2x"></i></p>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="dessert-tab" data-bs-toggle="pill" data-bs-target="#dessert" type="button" role="tab" aria-controls="dessert" aria-selected="false">
                             <h2>DESSERTS</h2><p><i class="fas fa-cake-candles fa-2x"></i></p>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="appetizer-tab" data-bs-toggle="pill" data-bs-target="#appetizer" type="button" role="tab" aria-controls="appetizer" aria-selected="false">
                            <h2>APPETIZER</h2><p><i class="fas fa-leaf fa-2x"></i></p>
                        </button>
                    </li>
                     <li class="nav-item" role="presentation">
                        <button class="nav-link" id="other-tab" data-bs-toggle="pill" data-bs-target="#other" type="button" role="tab" aria-controls="other" aria-selected="false">
                             <h2>OTHER</h2><p><i class="fas fa-bars fa-2x"></i></p>
                        </button>
                    </li>
                </ul>


                <div class="tab-content" id="menuTabsContent">

                    @foreach(['food', 'beverage', 'dessert', 'appetizer', 'other'] as $category)
                        <div class="tab-pane fade show {{ $loop->first ? 'active' : '' }}" id="{{ $category }}" role="tabpanel" aria-labelledby="{{ $category }}-tab">
                            <div class="row">
                                {{-- Assuming $menus is a collection passed from a Laravel controller --}}
                                @isset($menus)
                                    @foreach($menus as $menu)
                                        @if($menu->category === $category)
                                            <div class="col-md-6 menu-item-box d-flex align-items-center mb-4" data-aos="fade-up">
                                                <div class="menu-img-wrapper">
                                                    <img src="{{ $menu->image ? Storage::url($menu->image) : asset('images/default.jpg') }}" alt="{{ $menu->name }}" class="menu-img">
                                                </div>
                                                <div class="menu-details ms-3 flex-grow-1">
                                                    <h4 class="menu-title">{{ $menu->name }}</h4>
                                                    <p class="menu-desc">{{ $menu->description }}</p>
                                                </div>
                                                <div class="menu-price-circle">
                                                    <span>
                                                        @if($menu->price >= 1000)
                                                            {{ number_format($menu->price / 1000, 1) }}k
                                                        @else
                                                            {{ number_format($menu->price, 0, ',', '.') }}
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                @else
                                    {{-- Placeholder/example content if $menus is not defined --}}
                                     <div class="col-md-6 menu-item-box d-flex align-items-center mb-4" data-aos="fade-up">
                                        <div class="menu-img-wrapper">
                                            <img src="{{ asset('images/default.jpg') }}" alt="Example Food" class="menu-img">
                                        </div>
                                        <div class="menu-details ms-3 flex-grow-1">
                                            <h4 class="menu-title">Example Menu Item</h4>
                                            <p class="menu-desc">Delicious description of the menu item.</p>
                                        </div>
                                        <div class="menu-price-circle">
                                            <span>25k</span>
                                        </div>
                                    </div>
                                @endisset
                            </div>
                        </div>
                    @endforeach

                </div> </div> </div> </section>


    <section class="fitur-section py-5">
        <div class="container text-center position-relative">
            <h2 class="mb-5 fitur-heading" data-aos="fade-down">Fitur Unggulan</h2>

            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-right">
                    <div class="feature-card">
                        <div class="feature-icon fitur-text">📅</div>
                        <h5 class="fitur-text">Reservasi Meja</h5>
                        <p class="fitur-text">Pilih waktu dan meja dengan mudah langsung dari aplikasi.</p>
                    </div>
                </div>

                <div class="col-md-4" data-aos="zoom-in">
                    <div class="feature-card">
                        <div class="feature-icon fitur-text">🍽️</div>
                        <h5 class="fitur-text">Pemesanan Menu</h5>
                        <p class="fitur-text">Telusuri katalog digital dan pesan menu favorit Anda.</p>
                    </div>
                </div>

                <div class="col-md-4" data-aos="fade-left">
                    <div class="feature-card">
                        <div class="feature-icon fitur-text">📱</div>
                        <h5 class="fitur-text">Notifikasi Otomatis</h5>
                        <p class="fitur-text">Dapatkan update saat reservasi dikonfirmasi atau makanan siap disajikan.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <section id="about" class="py-5 text-white" style="background-color: #f4623a;">
        <div class="container">
            <div class="row align-items-center mb-5">

                <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                    <h2 class="mb-3">Tentang Kami</h2>
                    <h4 class="text-light">Hadirkan Pengalaman Kuliner yang Modern dan Mudah</h4>
                    <p>RestoOnline lahir dari keinginan untuk mempermudah pengalaman bersantap Anda. Kami menggabungkan cita rasa kuliner terbaik dengan teknologi terkini, menghadirkan platform digital yang memungkinkan pelanggan untuk memesan meja, memilih menu, dan melakukan pemesanan dengan cepat dan praktis.</p>
                    <p>Dengan desain antarmuka yang ramah pengguna dan sistem reservasi real-time, kami memastikan setiap kunjungan Anda ke restoran menjadi lebih terencana dan menyenangkan — tanpa antrean dan tanpa repot.</p>
                    <p>Komitmen kami adalah memberikan pelayanan terbaik, inovasi tanpa henti, dan kemudahan dalam setiap langkah Anda menikmati hidangan favorit bersama orang-orang tercinta.</p>
                </div>

                <div class="col-lg-6" data-aos="fade-left">
                    <div class="position-relative">
                        <img src="images/about-main.jpg" alt="About Main" class="img-fluid rounded shadow">
                        {{-- Ensure 'images' directory is accessible from public --}}
                        <img src="images/about-inset.jpg" alt="About Inset" class="img-thumbnail position-absolute about-inset-img">
                    </div>
                </div>

            </div>
        </div>
    </section>


    <section id="aplikasi" class="hero-section py-5 position-relative text-white d-flex align-items-center">

        <div class="container position-relative z-2">
            <div class="row align-items-center">

                <div class="col-md-6 text-center text-md-start" data-aos="fade-right">
                    <h1 class="display-4 fw-bold">Pesan Makanan Favorit Kini❤️ Semudah Sentuhan Jari.👉📱</h1>
                    <p class="lead">Download aplikasi RestoOnline dan nikmati kemudahan memesan makanan serta reservasi meja langsung dari smartphone Anda.</p>
                    <div class="d-flex justify-content-center justify-content-md-start gap-2 flex-wrap">
                        <a href="#" class="btn-download btn-orange">Playstore</a> {{-- Use # as placeholder or actual link --}}
                        <a href="#" class="btn-download btn-outline-white">Appstore</a> {{-- Use # as placeholder or actual link --}}
                    </div>
                </div>

                <div class="col-md-6 text-center mt-4 mt-md-0" data-aos="fade-left">
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <img src="{{ asset('images/mockup1.png') }}" alt="Mockup Playstore" class="hero-image">
                        <img src="{{ asset('images/mockup2.png') }}" alt="Mockup Appstore" class="hero-image">
                    </div>
                </div>

            </div>
        </div>
    </section>


    <section class="how-it-works py-5 text-white position-relative" style="background-image: url('{{ asset('images/team_bg.jpg') }}'); background-size: cover; background-position: center;">
        <div class="position-absolute top-0 start-0 w-100 h-100" style="background-color: rgba(0, 0, 0, 0.6); z-index: 1;"></div>

        <div class="container position-relative z-2 py-5">
            <div class="text-center mb-5" data-aos="fade-up">
                <h3 class="fw-bold step-title step">Bagaimana Aplikasi Ini Bekerja</h3>
                <p class="lead">Langkah mudah untuk menikmati...</p>
            </div>

            <div class="d-flex flex-column gap-5">
                <div class="row align-items-center" data-aos="fade-right">
                    <div class="col-md-4 text-center mb-3 mb-md-0">
                         <img src="{{ asset('images/mockupdaftar.png') }}" alt="Step 1" class="img-fluid" style="max-width: 200px;">
                    </div>
                    <div class="col-md-8 text-center text-md-start">
                        <h4 class="fw-semibold step-title step">Buat Akun</h4>
                        <p class="mb-0">Daftarkan dirimu dengan mudah menggunakan email, dan mulailah menjelajahi fitur aplikasi kami.</p>
                    </div>
                </div>

                <div class="row align-items-center flex-md-row-reverse" data-aos="fade-left">
                    <div class="col-md-4 text-center mb-3 mb-md-0">
                        <img src="{{ asset('images/mockupmenu.png') }}" alt="Step 2" class="img-fluid" style="max-width: 200px;">
                    </div>
                    <div class="col-md-8 text-center text-md-start">
                        <h4 class="fw-semibold step-title step">Jelajahi & Pilih Menu</h4>
                        <p class="mb-0">Lihat berbagai pilihan menu lezat dan tambahkan ke keranjang sesuai selera kamu.</p>
                    </div>
                </div>

                <div class="row align-items-center" data-aos="fade-right">
                    <div class="col-md-4 text-center mb-3 mb-md-0">
                        <img src="{{ asset('images/mockupnotifikasi.png') }}" alt="Step 3" class="img-fluid" style="max-width: 200px;">
                    </div>
                    <div class="col-md-8 text-center text-md-start">
                        <h4 class="fw-semibold step-title step">Dapatkan Notifikasi</h4>
                        <p class="mb-0">Tenang saja, kamu akan menerima notifikasi saat pesananmu sedang disiapkan dan dikirim.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <section id="our_team" class="team-main pad-top-100 pad-bottom-100 parallax py-5"> {{-- Added py-5 for spacing --}}
        <div class="container position-relative">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 z-2">
                    <div class="wow fadeIn" data-wow-duration="1s" data-wow-delay="0.1s">
                        <h2 class="block-title text-center" data-aos="fade-down">Tim Kami</h2>
                        <p class="title-caption text-center" data-aos="fade-up">
                            Di balik layanan hebat kami, ada tim luar biasa yang bekerja dengan dedikasi tinggi. Kami adalah kombinasi dari para profesional di bidang teknologi, pelayanan, dan kuliner yang berkomitmen menghadirkan pengalaman restoran digital terbaik untuk Anda.
                        </p>
                    </div>
                </div>
            </div>

            <div class="team-box mt-4"> {{-- Added mt-4 for spacing --}}
                <div class="row">
                    <div class="col-md-4 col-sm-6 mb-4"> {{-- Added mb-4 for spacing --}}
                        <div class="sf-team wow fadeInUp" data-wow-duration="1s" data-wow-delay="0.2s">
                            <div class="thumb">
                                <h4>Manager</h4>
                                <a href="#">
                                    {{-- Ensure 'images' directory is accessible from public --}}
                                    <img src="images/jav.png" alt="Jav - Restaurant Manager" class="img-fluid">
                                </a>
                            </div>
                            <div class="text-col">
                                <h3>Jav</h3>
                                <p>Dengan pengalaman lebih dari 10 tahun di industri kuliner, Jav memimpin tim kami dengan dedikasi dan visi yang kuat. Ia dikenal karena kemampuannya menjaga standar layanan tertinggi dan menciptakan suasana kerja yang profesional dan bersahabat. Di bawah kepemimpinannya, setiap kunjungan pelanggan menjadi pengalaman yang berkesan.</p>
                                <ul class="team-social">
                                    <li><a href="#"><i class="fab fa-facebook"></i></a></li>
                                    <li><a href="#"><i class="fab fa-twitter"></i></a></li>
                                    <li><a href="#"><i class="fab fa-linkedin"></i></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6 mb-4"> {{-- Added mb-4 for spacing --}}
                        <div class="sf-team wow fadeInUp" data-wow-duration="1s" data-wow-delay="0.2s">
                            <div class="thumb">
                                <h4>Cheff</h4>
                                <a href="#">
                                    {{-- Ensure 'images' directory is accessible from public --}}
                                    <img src="images/upi.png" alt="Upi - Cheff" class="img-fluid">
                                </a>
                            </div>
                            <div class="text-col">
                                <h3>Upi</h3>
                                <p>Chef Upi adalah otak kreatif di balik setiap hidangan khas restoran kami. Menggabungkan teknik kuliner modern dengan cita rasa lokal, ia menciptakan menu yang tidak hanya memanjakan lidah, tetapi juga meninggalkan kesan mendalam. Keahliannya menjadikan dapur kami sebagai pusat inovasi dan kualitas yang sangat cekatan dan juga baik.</p>
                                <ul class="team-social">
                                    <li><a href="#"><i class="fab fa-facebook"></i></a></li>
                                    <li><a href="#"><i class="fab fa-twitter"></i></a></li>
                                    <li><a href="#"><i class="fab fa-linkedin"></i></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6 mb-4"> {{-- Added mb-4 for spacing --}}
                        <div class="sf-team wow fadeInUp" data-wow-duration="1s" data-wow-delay="0.2s">
                            <div class="thumb">
                                <h4>Bartender</h4>
                                <a href="#">
                                    {{-- Ensure 'images' directory is accessible from public --}}
                                    <img src="images/rey.png" alt="Rey - Bartender" class="img-fluid">
                                </a>
                            </div>
                            <div class="text-col">
                                <h3>Rey</h3>
                                <p>Rey adalah bartender andalan kami yang menghadirkan pengalaman minum yang tak terlupakan. Dengan keahlian mencampur berbagai jenis minuman dan sentuhan kreatif dalam setiap racikan, Rey selalu tahu cara memanjakan tamu dengan koktail yang sempurna. Energinya yang positif dan keramahan membuat setiap kunjungan ke bar menjadi lebih istimewa.</p>
                                <ul class="team-social">
                                    <li><a href="#"><i class="fab fa-facebook"></i></a></li>
                                    <li><a href="#"><i class="fab fa-twitter"></i></a></li>
                                    <li><a href="#"><i class="fab fa-linkedin"></i></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6 mb-4"> {{-- Added mb-4 for spacing --}}
                        <div class="sf-team wow fadeInUp" data-wow-duration="1s" data-wow-delay="0.2s">
                            <div class="thumb">
                                <h4>Host</h4>
                                <a href="#">
                                    {{-- Ensure 'images' directory is accessible from public --}}
                                    <img src="images/lif.png" alt="Lif - Host" class="img-fluid">
                                </a>
                            </div>
                            <div class="text-col">
                                <h3>Lif</h3>
                                <p>Lif adalah sosok ramah yang menyambut setiap tamu dengan senyum hangat dan pelayanan penuh perhatian. Sebagai host kami, Lif memastikan pengalaman pertama Anda di restoran dimulai dengan kesan yang menyenangkan, dari sambutan hangat hingga pengaturan tempat duduk yang nyaman. Dengan ketulusan dan kecekatan.</p>
                                <ul class="team-social">
                                    <li><a href="#"><i class="fab fa-facebook"></i></a></li>
                                    <li><a href="#"><i class="fab fa-twitter"></i></a></li>
                                    <li><a href="#"><i class="fab fa-linkedin"></i></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6 mb-4"> {{-- Added mb-4 for spacing --}}
                        <div class="sf-team wow fadeInUp" data-wow-duration="1s" data-wow-delay="0.2s">
                            <div class="thumb">
                                <h4>Waiter</h4>
                                <a href="#">
                                    {{-- Ensure 'images' directory is accessible from public --}}
                                    <img src="images/bay.png" alt="Bay - Waiter" class="img-fluid">
                                </a>
                            </div>
                            <div class="text-col">
                                <h3>Bay</h3>
                                <p>Bay adalah pramusaji yang selalu siap memberikan pelayanan terbaik dengan senyum tulus dan sikap profesional. Dengan perhatian pada detail dan kecepatan dalam melayani, Bay memastikan setiap pesanan Anda datang dengan sempurna dan tepat waktu. Kepuasan tamu adalah prioritas utama bagi Bay, menjadikan setiap kunjungan Anda lebih berkesan.</p>
                                <ul class="team-social">
                                    <li><a href="#"><i class="fab fa-facebook"></i></a></li>
                                    <li><a href="#"><i class="fab fa-twitter"></i></a></li>
                                    <li><a href="#"><i class="fab fa-linkedin"></i></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 mb-4"> {{-- Added mb-4 for spacing --}}
                        <div class="sf-team wow fadeInUp" data-wow-duration="1s" data-wow-delay="0.2s">
                            <div class="thumb">
                                <h4>Lover</h4> {{-- Role 'Lover' seems unusual for a restaurant team, maybe a placeholder? --}}
                                <a href="#">
                                     {{-- Ensure 'images' directory is accessible from public --}}
                                    <img src="images/miko2.jpeg" alt="Miko - Lover" class="img-fluid">
                                </a>
                            </div>
                            <div class="text-col">
                                <h3>Miko</h3> {{-- Name 'Miko' --}}
                                <p>Miko hadir sebagai simbol cinta dan kehangatan di restoran kami. Dengan pesonanya yang menenangkan dan senyuman tulus, Miko mewakili momen-momen spesial bagi pasangan yang datang menikmati hidangan bersama. Ia menjadi pengingat bahwa setiap kunjungan bukan hanya tentang rasa, tapi juga tentang hubungan yang diciptakan di setiap meja.</p> {{-- Description for 'Lover' role --}}
                                <ul class="team-social">
                                    <li><a href="#"><i class="fab fa-facebook"></i></a></li>
                                    <li><a href="#"><i class="fab fa-twitter"></i></a></li>
                                    <li><a href="#"><i class="fab fa-linkedin"></i></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </section>


    <section id="location" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right"> {{-- Added mb for spacing on smaller screens --}}
                    <h2 class="text-center mb-4">Lokasi Kami</h2>
                    <div class="map-container">
                        <div id="map" style="height: 400px; width: 100%; border-radius: 8px;"></div>
                    </div>
                </div>

                <div class="col-lg-6" data-aos="fade-left">
                    <div class="contact-info p-4" style="background-color: #f8f9fa; border-radius: 8px;">
                        <h3 class="mb-3"><i class="fas fa-map-marker-alt me-2"></i>Alamat</h3>
                        <p class="mb-4">Jl. Contoh No. 123, Kota Bandung, Jawa Barat</p>

                        <h3 class="mb-3"><i class="fas fa-clock me-2"></i>Jam Operasional</h3>
                        <p class="mb-4">
                            Senin-Jumat: 10.00 - 22.00<br>
                            Sabtu-Minggu: 08.00 - 23.00
                        </p>

                        <h3 class="mb-3"><i class="fas fa-phone-alt me-2"></i>Kontak</h3>
                        <p class="mb-4">
                            Telepon: (022) 1234-5678<br>
                            WhatsApp: 0812-3456-7890<br>
                            Email: info@resto.com
                        </p>

                        <button class="btn btn-primary mt-3 w-100" onclick="openDirections()">
                            <i class="fas fa-directions"></i> Petunjuk Arah
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>


    {{-- Removed the extra '...' and commented out 'Body Content' section --}}


    <footer> {{-- Used semantic footer tag --}}
        <div class="footer-box pad-top-70 py-5"> {{-- Added py-5 for vertical padding --}}
            <div class="container">
                <div class="row">
                    {{-- Removed the problematic footer-in-main div and its empty children --}}

                    <div class="col-12 text-center footer-logo mb-4" data-aos="zoom-in"> {{-- Centered and added bottom margin --}}
                        {{-- Ensure 'images' directory is accessible from public --}}
                        <img src="images/Logo Resto Online.png" lt="Logo" style="max-width: 120px; height: auto;" /> {{-- Fixed typo: 'lt' should be 'alt' --}}
                    </div>

                    <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12 mb-4" data-aos="fade-up"> {{-- Adjusted column classes for responsiveness, added mb --}}
                        <div class="footer-box-a">
                            <h3>Tentang Kami</h3>
                            <p>RestoOnline hadir untuk memudahkan pemesanan makanan dan reservasi meja secara digital. Cepat, praktis, dan terpercaya.</p>
                            <ul class="socials-box footer-socials pull-left">
                                <li><a href="#"><div class="social-circle-border"><i class="fab fa-facebook"></i></div></a></li>
                                <li><a href="#"><div class="social-circle-border"><i class="fab fa-twitter"></i></div></a></li>
                                <li><a href="#"><div class="social-circle-border"><i class="fab fa-google-plus"></i></div></a></li>
                                <li><a href="#"><div class="social-circle-border"><i class="fab fa-pinterest"></i></div></a></li>
                                <li><a href="#"><div class="social-circle-border"><i class="fab fa-linkedin"></i></div></a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12 mb-4" data-aos="fade-up" data-aos-delay="100"> {{-- Added mb and delay --}}
                        <div class="footer-box-b">
                            <h3>Menu Baru</h3>
                            <ul>
                                <li><a href="#">Burger Spesial RestoOnline</a></li>
                                <li><a href="#">Ayam Bakar Sambal Digital</a></li>
                                <li><a href="#">Nasi Goreng Keju Melt</a></li>
                                <li><a href="#">Es Kopi Susu Signature</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12 mb-4" data-aos="fade-up" data-aos-delay="200"> {{-- Added mb and delay --}}
                        <div class="footer-box-c">
                            <h3>Hubungi Kami</h3>
                            <p><i class="fa fa-map-signs" aria-hidden="true"></i><span>Jl. Makanan Digital No. 88, Jakarta, Indonesia</span></p>
                            <p><i class="fa fa-mobile" aria-hidden="true"></i><span>+62 812 3456 7890</span></p>
                            <p><i class="fa fa-envelope" aria-hidden="true"></i><span><a href="#">cs@restoonline.id</a></span></p>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12 mb-4" data-aos="fade-up" data-aos-delay="300"> {{-- Added mb and delay --}}
                        <div class="footer-box-d">
                            <h3>Jam Operasional</h3>
                            <ul>
                                <li><p>Senin - Kamis</p><span>10:00 - 21:00 WIB</span></li>
                                <li><p>Jumat - Minggu</p><span>10:00 - 23:00 WIB</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer text-center py-3" style="background-color: #343a40; color: rgba(255, 255, 255, 0.5);"> {{-- Added basic styling for visibility --}}
            <div class="container">
                <p>&copy; {{ date('Y') }} RestoOnline. All rights reserved.</p>
                <p>Email: support@restoonline.com</p> {{-- Added email from original code --}}
            </div>
        </div>
    </footer>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
     <script src="https://cdnjs.cloudflare.com/ajax/libs/wow/1.1.2/wow.min.js"></script>


    <script>
        AOS.init({
            offset: 200, // default: 120
            duration: 600,
            easing: 'ease-in-out',
            once: true, // animasi hanya muncul sekali
        });
    </script>

    <script>
        new WOW().init();
    </script>

    <script>
        $(document).ready(function(){
            // Check if the elements exist before initializing Slick
            if ($('.slider-single').length && $('.slider-nav').length) {
                $('.slider-single').slick({
                    slidesToShow: 1,
                    arrows: false,
                    fade: true,
                    asNavFor: '.slider-nav'
                });

                $('.slider-nav').slick({
                    slidesToShow: 4,
                    asNavFor: '.slider-single',
                    focusOnSelect: true,
                    centerMode: true,
                    arrows: false,
                     responsive: [ // Added responsiveness for the nav slider
                        {
                            breakpoint: 768, // md
                            settings: {
                                slidesToShow: 3,
                            }
                        },
                        {
                            breakpoint: 576, // sm
                            settings: {
                                slidesToShow: 2,
                            }
                        },
                         {
                            breakpoint: 400, // xs
                            settings: {
                                slidesToShow: 1,
                            }
                        }
                    ]
                });
             } else {
                console.warn("Slick Carousel elements not found. Skipping initialization.");
            }
        });
    </script>

    <script>
        // Koordinat restoran (contoh: Bandung)
        const lokasiResto = [-6.3897205838581215, 107.49571766009339]; // Consider making these dynamic if needed

        // Inisialisasi peta
        // Check if the map element exists before initializing
        if (document.getElementById('map')) {
             const map = L.map('map').setView(lokasiResto, 16);

            // Tambahkan layer peta
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors' // Added contributors
            }).addTo(map);

            // Custom Marker Icon
            const restoIcon = L.divIcon({
                html: '<i class="fas fa-utensils fa-2x" style="color: #e63946;"></i>',
                className: 'custom-icon',
                iconSize: [30, 30],
                iconAnchor: [15, 30]
            });

            // Tambahkan marker
            const marker = L.marker(lokasiResto, {
                icon: restoIcon
            }).addTo(map);

            // Popup info
            marker.bindPopup(`
                <b>Resto Enak</b><br>
                <small>Jl. Contoh No. 123</small> {{-- Consider making this dynamic --}}
            `).openPopup();

            // Fungsi petunjuk arah
            function openDirections() {
                 // Use a more robust way to get current location if 'from' is needed dynamically
                const url = `https://www.openstreetmap.org/directions?from=&to=${lokasiResto[0]},${lokasiResto[1]}#map=17/${lokasiResto[0]}/${lokasiResto[1]}`;
                window.open(url, '_blank');
            }
             // Attach the function to the button's onclick event (optional if already in HTML)
            const directionsButton = document.querySelector('#location .btn-primary');
            if (directionsButton) {
                 directionsButton.onclick = openDirections;
            }

         } else {
            console.warn("Leaflet map element (#map) not found. Skipping map initialization.");
         }
    </script>

    <script>
        let lastScrollTop = 0;
        const navbar = document.getElementById("navbar");

        // Check if navbar element exists
        if (navbar) {
             window.addEventListener("scroll", function () {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

                if (scrollTop > lastScrollTop && scrollTop > navbar.offsetHeight) { // Add condition to hide only after scrolling past navbar height
                    // Scroll down
                    navbar.classList.add("navbar-hidden");
                } else {
                    // Scroll up or at the very top
                    navbar.classList.remove("navbar-hidden");
                }

                lastScrollTop = scrollTop <= 0 ? 0 : scrollTop; // Avoid negative values
            }, false);
        } else {
            console.warn("Navbar element (#navbar) not found. Skipping scroll hide/show logic.");
        }
    </script>

    {{-- Removed custom JS for tabs as Bootstrap's JS handles data-bs-toggle="pill" --}}
    {{-- Ensure your CSS correctly styles .nav-link.active and .tab-pane.active --}}


    {{-- Bootstrap JS already included in Bootstrap Bundle --}}
    {{-- AOS JS already included --}}

</body>
</html>