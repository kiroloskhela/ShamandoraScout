{{-- <?php /** advertising.php — Shamandora Scouts link hub */ ?>
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
        body {
            font-family: 'Cairo', system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, 'Helvetica Neue', Arial;
        }

        /* subtle page fade-in */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-in {
            animation: fadeIn .45s ease-out both;
        }

        /* cards: hover lift + shadow */
        .card {
            transition: transform .15s ease, box-shadow .2s ease, border-color .2s ease;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 22px rgba(0, 0, 0, .08);
            border-color: rgb(203 213 225);
        }

        /* icon ring hover */
        .icon-ring {
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .card:hover .icon-ring {
            transform: scale(1.05);
            box-shadow: 0 8px 18px rgba(0, 0, 0, .10);
        }
    </style>
</head>

<body class="bg-white min-h-screen text-gray-900">
    <div class="w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid lg:grid-cols-2 gap-8 items-center min-h-[70vh]">

            <!-- Cards Column (like login form area) -->
            <div class="order-2 lg:order-1 animate-in">
                <div class="bg-white rounded-2xl p-6 sm:p-8 lg:p-10 shadow-lg border border-gray-100">
                    <h2 class="text-3xl font-bold text-center mb-2">روابط السوشيال الرسمية</h2>
                    <p class="text-gray-600 text-center mb-8">تابعنا على المنصات المختلفة</p>

                    <!-- 4 SMALL CARDS: only logo + name -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <!-- Facebook -->
                        <a href="https://www.facebook.com/share/17AE3MNHam/" target="_blank" rel="noopener"
                            class="card flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-200">
                            <span
                                class="icon-ring inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-50">
                                <img src="https://cdn.simpleicons.org/facebook/1877F2" alt="Facebook" class="w-7 h-7" />
                            </span>
                            <span class="text-base font-bold">Facebook</span>
                        </a>

                        <!-- Instagram -->
                        <a href="https://www.instagram.com/shamandora_scout?igsh=dXExOXFucTBkMDVt" target="_blank"
                            rel="noopener"
                            class="card flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 focus:outline-none focus:ring-2 focus:ring-pink-200">
                            <span class="icon-ring inline-flex items-center justify-center w-12 h-12 rounded-xl"
                                style="background:linear-gradient(135deg,#F58529 0%,#DD2A7B 50%,#8134AF 100%);">
                                <img src="https://cdn.simpleicons.org/instagram/FFFFFF" alt="Instagram"
                                    class="w-7 h-7" />
                            </span>
                            <span class="text-base font-bold">Instagram</span>
                        </a>

                        <!-- Spotify (correct logo) -->
                        <a href="https://open.spotify.com/artist/6UxngCQeJnijih2mXIhb7Z?si=CUircyf5R2eM1w9a-Sb8sQ"
                            target="_blank" rel="noopener"
                            class="card flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-200">
                            <span
                                class="icon-ring inline-flex items-center justify-center w-12 h-12 rounded-xl bg-green-50">
                                <img src="{{ asset('img/spotify.svg') }}" alt="Spotify" class="w-10 h-10" />
                            </span>
                            <span class="text-base font-bold">Spotify</span>
                        </a>

                        <!-- Anghami (correct logo) -->
                        <a href="https://play.anghami.com/artist/15992103" target="_blank" rel="noopener"
                            class="card flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-200">
                            <span
                                class="icon-ring inline-flex items-center justify-center w-12 h-12 rounded-xl bg-purple-50">
                                <img src="{{ asset('img/anghami.png') }}" alt="Anghami" class="w-7 h-7" />
                            </span>
                            <span class="text-base font-bold">Anghami</span>
                        </a>

                    </div>

                    <!-- Footer mini -->
                    <div class="mt-8 text-center text-sm text-gray-500">
                        صُنع بواسطة فريق <span class="font-semibold">كشافة الشمندورة</span> — © <?= date('Y') ?>
                    </div>
                </div>
            </div>

            <!-- Logo / Side Column (like login side) -->
            <div class="flex flex-col items-center justify-center order-1 lg:order-2 text-center animate-in">
                <div class="mb-7">
                    <div
                        class="w-40 h-40 bg-gray-100 rounded-full flex items-center justify-center shadow-md border border-gray-200">
                        <!-- Placeholder for logo -->

                        <img src="{{ asset('img/shamandora.png') }}">

                    </div>
                </div>
                <h1 class="text-4xl font-extrabold mb-3">كشافة الشمندورة</h1>

            </div>

        </div>
    </div>

    <!-- Optional: gentle page mount stagger for cards -->
    <script>
        // Stagger-in each card on load for a nicer feel
        document.addEventListener('DOMContentLoaded', () => {
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, i) => {
                card.style.opacity = 0;
                card.style.transform = 'translateY(8px)';
                setTimeout(() => {
                    card.style.transition = 'opacity .35s ease, transform .35s ease';
                    card.style.opacity = 1;
                    card.style.transform = 'translateY(0)';
                }, 120 + i * 90);
            });
        });
    </script>
</body>

</html> --}}

<?php /** advertising.php — Shamandora Scouts link hub (vertical compact) */ ?>
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

        /* app primary */
        body {
            font-family: 'Cairo', system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, 'Helvetica Neue', Arial;
        }

        /* Page fade-in */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-in {
            animation: fadeIn .45s ease-out both;
        }

        /* Compact cards with gentle hover */
        .card {
            transition: transform .16s ease, box-shadow .25s ease, border-color .2s ease, background-color .2s ease;
            background: #f8fafc;
            /* slate-50 like */
            border: 1px solid #e5e7eb;
            /* gray-200 */
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 22px rgba(2, 6, 23, .08);
            border-color: #cbd5e1;
            /* slate-300 */
            background: #f9fbff;
        }

        .icon-ring {
            transition: transform .2s ease, box-shadow .25s ease;
        }

        .card:hover .icon-ring {
            transform: scale(1.05);
            box-shadow: 0 8px 18px rgba(2, 6, 23, .10);
        }

        /* Keep everything on one screen on small phones */
        @media (max-height: 700px) {
            .logo-wrap {
                width: 7rem;
                height: 7rem;
            }

            /* shrink logo circle */
            .logo-img {
                width: 4.5rem;
                height: 4.5rem;
            }

            .app-title {
                font-size: 1.6rem;
            }

            .card {
                padding: .6rem .8rem;
            }

            .card .icon-ring {
                width: 2.25rem;
                height: 2.25rem;
            }

            .card .icon {
                width: 1rem;
                height: 1rem;
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
            .icon-ring {
                animation: none !important;
                transition: none !important;
            }
        }
    </style>
</head>

<body class="bg-white min-h-screen text-gray-900 overflow-hidden">
    <div class="wrapper min-h-screen flex items-center justify-center px-4 py-6">
        <main class="w-full max-w-md animate-in">
            <!-- Logo / Title -->
            <div class="flex flex-col items-center text-center">
                <div class="logo-wrap mb-4 rounded-full bg-gray-100 border border-gray-200 shadow-md flex items-center justify-center"
                    style="width: 9rem; height: 9rem;">
                    <img src="{{ asset('img/shamandora.png') }}">
                </div>
                <h1 class="app-title text-3xl font-extrabold mb-2">كشافة الشمندورة</h1>
                <p class="text-gray-600 text-sm mb-5">روابطنا الرسمية — Official Links</p>
            </div>

            <!-- 4 SMALL CARDS (vertical stack) -->
            <section class="stack grid grid-cols-1 gap-3">
                <!-- Facebook -->
                <a href="https://www.facebook.com/share/17AE3MNHam/" target="_blank" rel="noopener"
                    class="card flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-200">
                    <span class="icon-ring inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-50">
                        <img src="https://cdn.simpleicons.org/facebook/1877F2" alt="Facebook" class="w-7 h-7" />
                    </span>
                    <span class="text-base font-bold">Facebook</span>
                </a>

                <!-- Instagram -->
                <a href="https://www.instagram.com/shamandora_scout?igsh=dXExOXFucTBkMDVt" target="_blank"
                    rel="noopener"
                    class="card flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 focus:outline-none focus:ring-2 focus:ring-pink-200">
                    <span class="icon-ring inline-flex items-center justify-center w-12 h-12 rounded-xl"
                        style="background:linear-gradient(135deg,#F58529 0%,#DD2A7B 50%,#8134AF 100%);">
                        <img src="https://cdn.simpleicons.org/instagram/FFFFFF" alt="Instagram" class="w-7 h-7" />
                    </span>
                    <span class="text-base font-bold">Instagram</span>
                </a>

                <!-- Spotify (correct logo) -->
                <a href="https://open.spotify.com/artist/6UxngCQeJnijih2mXIhb7Z?si=CUircyf5R2eM1w9a-Sb8sQ"
                    target="_blank" rel="noopener"
                    class="card flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-200">
                    <span class="icon-ring inline-flex items-center justify-center w-12 h-12 rounded-xl bg-green-50">
                        <img src="{{ asset('img/spotify.svg') }}" alt="Spotify" class="w-10 h-10" />
                    </span>
                    <span class="text-base font-bold">Spotify</span>
                </a>

                <!-- Anghami (correct logo) -->
                <a href="https://play.anghami.com/artist/15992103" target="_blank" rel="noopener"
                    class="card flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-200">
                    <span class="icon-ring inline-flex items-center justify-center w-12 h-12 rounded-xl bg-purple-50">
                        <img src="{{ asset('img/anghami.png') }}" alt="Anghami" class="w-7 h-7" />
                    </span>
                    <span class="text-base font-bold">Anghami</span>
                </a>
            </section>

            <!-- Tiny footer -->
            <footer class="mt-5 text-center text-xs text-gray-500">
                © <?= date('Y') ?> — فريق <span class="font-semibold">كشافة الشمندورة</span>
            </footer>
        </main>
    </div>

    <!-- Gentle stagger-in for cards -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const cards = document.querySelectorAll('.card');
            cards.forEach((c, i) => {
                c.style.opacity = 0;
                c.style.transform = 'translateY(8px)';
                setTimeout(() => {
                    c.style.transition = 'opacity .35s ease, transform .35s ease';
                    c.style.opacity = 1;
                    c.style.transform = 'translateY(0)';
                }, 120 + i * 90);
            });
        });
    </script>
</body>

</html>
