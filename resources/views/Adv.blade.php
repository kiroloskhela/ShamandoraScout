<!doctype html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>روابط كشافة الشمندورة</title>

    <!-- Fonts & Tailwind -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        :root {
            --brand: #0b2a4a;
        }

        body {
            font-family: 'Cairo', system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, 'Helvetica Neue', Arial;
            background: white
        }

        /* Page fade-in */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .animate-in {
            animation: fadeIn .6s ease-out both;
        }

        .logo-wrap {
            animation: float 3s ease-in-out infinite;
        }

        /* Enhanced cards with platform colors */
        .card {
            transition: all .3s cubic-bezier(0.4, 0, 0.2, 1);
            background: white;
            border: 2px solid #e5e7eb;
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--hover-gradient);
            opacity: 0;
            transition: opacity .3s ease;
            z-index: 0;
        }

        .card:hover::before {
            opacity: 1;
        }

        .card>* {
            position: relative;
            z-index: 1;
        }

        .card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, .15);
            border-color: transparent;
        }

        .card:hover .card-name {
            color: white;
            font-weight: 700;
        }

        /* Facebook colors */
        .card-facebook {
            --hover-gradient: linear-gradient(135deg, #1877F2 0%, #0d5ecd 100%);
        }

        /* Instagram colors */
        .card-instagram {
            --hover-gradient: linear-gradient(135deg, #F58529 0%, #DD2A7B 50%, #8134AF 100%);
        }

        /* Spotify colors */
        .card-spotify {
            --hover-gradient: linear-gradient(135deg, #1DB954 0%, #1ed760 100%);
        }

        /* Anghami colors */
        .card-anghami {
            --hover-gradient: linear-gradient(135deg, #7C3FBF 0%, #9b59d8 100%);
        }

        .icon-ring {
            transition: all .3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, .08);
        }

        .card:hover .icon-ring {
            transform: scale(1.15) rotate(5deg);
            box-shadow: 0 8px 24px rgba(255, 255, 255, .3);
            background: rgba(255, 255, 255, .2) !important;
        }

        .card:hover .icon-ring img {
            filter: brightness(0) invert(1);
        }

        .card-instagram:hover .icon-ring img {


            filter: none;
        }

        /* Logo container enhancement */
        .logo-container {
            background: white;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .15);
            position: relative;
        }

        .logo-container::after {



            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: #e5e7eb;
            border-radius: 9999px;
            z-index: -1;
            opacity: .7;
            filter: blur(10px);
        }

        /* Keep everything on one screen on small phones */
        @media (max-height: 700px) {
            .logo-wrap {
                width: 7rem !important;
                height: 7rem !important;
            }

            .app-title {
                font-size: 1.6rem;
            }

            .card {
                padding: .6rem .8rem;
            }

            .card .icon-ring {
                width: 2.5rem !important;
                height: 2.5rem !important;
            }

            .card .icon-ring img {
                width: 1.25rem !important;
                height: 1.25rem !important;
            }

            .card-name {
                font-size: .95rem;
            }

            .wrapper {
                padding-top: 0.75rem;
                padding-bottom: 0.75rem;
            }

            .stack {
                gap: .55rem !important;
            }
        }

        /* Reduce motion preference */
        @media (prefers-reduced-motion: reduce) {

            .animate-in,
            .card,
            .icon-ring,
            .logo-wrap {
                animation: none !important;
                transition: none !important;
            }
        }

        /* Glassmorphism effect for main container */
        .glass-container {
            background: white;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, .3);
            box-shadow: 0 25px 50px rgba(0, 0, 0, .15);
        }
    </style>
</head>

<body class="min-h-screen text-gray-900 overflow-hidden">
    <div class="wrapper min-h-screen flex items-center justify-center px-4 py-6">
        <main class="w-full max-w-md animate-in">
            <div class="glass-container rounded-3xl p-8">
                <!-- Logo / Title -->
                <div class="flex flex-col items-center text-center mb-6">
                    <div class="logo-wrap mb-5 logo-container rounded-full border-4 border-white flex items-center justify-center"
                        style="width: 9rem; height: 9rem;">
                        <svg viewBox="0 0 100 100" class="w-60 h-60">

                            <image href="{{ asset('img/shamandora.png') }}" x="5" y="5" width="90" height="90" />

                        </svg>
                    </div>
                    <h1 class="app-title text-3xl font-extrabold mb-2 text-gray-800">كشافة الشمندورة</h1>
                    <p class="text-gray-600 text-sm">روابطنا الرسمية — Official Links</p>
                </div>

                <!-- 4 SMALL CARDS (vertical stack) -->
                <section class="stack grid grid-cols-1 gap-4">
                    <!-- Facebook -->
                    <a href="https://www.facebook.com/share/17AE3MNHam/" target="_blank" rel="noopener"
                        class="card card-facebook flex items-center gap-4 rounded-2xl px-5 py-4 focus:outline-none focus:ring-4 focus:ring-blue-200">
                        <span class="icon-ring inline-flex items-center justify-center w-14 h-14 rounded-xl bg-blue-50">
                            <svg class="w-8 h-8" fill="#1877F2" viewBox="0 0 24 24">
                                <path
                                    d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                            </svg>
                        </span>
                        <span class="card-name text-lg font-bold text-gray-800">Facebook</span>
                    </a>

                    <!-- Instagram -->
                    <a href="https://www.instagram.com/shamandora_scout?igsh=dXExOXFucTBkMDVt" target="_blank"
                        rel="noopener"
                        class="card card-instagram flex items-center gap-4 rounded-2xl px-5 py-4 focus:outline-none focus:ring-4 focus:ring-pink-200">
                        <span class="icon-ring inline-flex items-center justify-center w-14 h-14 rounded-xl"
                            style="background:linear-gradient(135deg,#F58529 0%,#DD2A7B 50%,#8134AF 100%);">
                            <svg class="w-8 h-8" fill="white" viewBox="0 0 24 24">
                                <path
                                    d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                            </svg>
                        </span>
                        <span class="card-name text-lg font-bold text-gray-800">Instagram</span>
                    </a>

                    <!-- Spotify -->
                    <a href="https://open.spotify.com/artist/6UxngCQeJnijih2mXIhb7Z?si=CUircyf5R2eM1w9a-Sb8sQ"
                        target="_blank" rel="noopener"
                        class="card card-spotify flex items-center gap-4 rounded-2xl px-5 py-4 focus:outline-none focus:ring-4 focus:ring-green-200">
                        <span
                            class="icon-ring inline-flex items-center justify-center w-14 h-14 rounded-xl bg-green-50">
                            <svg class="w-8 h-8" fill="#1DB954" viewBox="0 0 24 24">
                                <path
                                    d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z" />
                            </svg>
                        </span>
                        <span class="card-name text-lg font-bold text-gray-800">Spotify</span>
                    </a>

                    <!-- Anghami -->
                    <a href="https://play.anghami.com/artist/15992103" target="_blank" rel="noopener"
                        class="card card-anghami flex items-center gap-4 rounded-2xl px-5 py-4 focus:outline-none focus:ring-4 focus:ring-purple-200">
                        <span
                            class="icon-ring inline-flex items-center justify-center w-14 h-14 rounded-xl bg-purple-50">



                            <svg class="w-8 h-8" viewBox="0 0 24 24" fill="#f300f9" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M2.127 13.66c.678 4.178 4.192 7.648 8.392 8.232a10.456 10.456 0 0 0 5.702-.817c.301-.137.533-.264.514-.282l-1.984-.388c-1.36.32-2.704.537-4.103.303C5.672 19.876 2.1 14.815 3.424 9.85c.518-1.942 1.681-3.712 3.288-4.922 1.703-1.283 3.956-1.964 6.089-1.727 4.14.46 7.506 3.788 7.978 7.907.045.385.081 5.243.081 5.243l.001 4.542 1.103.181.028-5.25c-.028-4.642-.049-5.323-.177-5.869-1.034-4.428-4.97-7.814-9.52-7.95C6.082 1.822 1.133 7.53 2.127 13.66ZM4.42 8.877c-1.624 4.02.21 8.67 4.099 10.504 1.981.936 4.093.93 6.165.354V18.44c-1.915.74-3.97.706-5.807-.2-1.777-.879-3.173-2.571-3.66-4.503a7.123 7.123 0 0 1 1.44-6.285c1.282-1.549 3.351-2.44 5.34-2.438 3.211.002 6.024 2.301 6.817 5.368.169.653.179.907.213 5.432l.037 4.748 1.138.204v-4.52s-.035-4.806-.09-5.261c-.474-3.883-3.886-6.993-7.801-7.158-3.37-.142-6.618 1.899-7.891 5.05Zm2.707-.98c-2.776 3.349-1.336 8.668 2.756 10.092 1.598.555 3.259.433 4.8-.211 0 0-.057-1.3-.061-1.299-1.282.644-2.802.892-4.186.446-1.53-.492-2.81-1.713-3.323-3.243-.47-1.398-.33-2.867.348-4.18.888-1.717 2.94-2.754 4.824-2.657 2.13.11 3.928 1.55 4.636 3.522.191.531.193.573.234 5.185l.04 4.65 1.207.223-.032-4.679c-.03-4.508-.038-4.703-.211-5.33-.726-2.623-3.028-4.575-5.781-4.763-1.97-.134-3.988.72-5.25 2.243Zm.418 3.253c-.484 2.354 1.191 4.785 3.535 5.283 1.273.27 2.487-.13 3.604-.738v-1.637c-.91.822-1.905 1.435-3.196 1.234-1.801-.28-3.03-2.019-2.802-3.79.215-1.66 1.709-2.827 3.317-2.82 1.656.007 2.646 1.089 3.299 2.468l.082 8.726 1.112.213-.133-9.316c-.569-1.916-2.325-3.292-4.356-3.28a4.55 4.55 0 0 0-4.462 3.657Z"
                                    clip-rule="evenodd"></path>
                            </svg>

                        </span>
                        <span class="card-name text-lg font-bold text-gray-800">Anghami</span>
                    </a>
                </section>

                <!-- Tiny footer -->
                <footer class="mt-6 text-center text-xs text-gray-500">
                    ©
                    <script>
                        document.write(new Date().getFullYear())
                    </script> — فريق <span class="font-semibold">كشافة الشمندورة</span>
                </footer>
            </div>
        </main>
    </div>

    <!-- Gentle stagger-in for cards -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const cards = document.querySelectorAll('.card');
            cards.forEach((c, i) => {
                c.style.opacity = 0;
                c.style.transform = 'translateY(12px)';
                setTimeout(() => {
                    c.style.transition = 'opacity .4s ease, transform .4s ease';
                    c.style.opacity = 1;
                    c.style.transform = 'translateY(0)';
                }, 150 + i * 100);
            });
        });
    </script>
</body>

</html>
