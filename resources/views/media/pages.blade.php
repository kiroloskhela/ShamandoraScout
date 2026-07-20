@extends('layouts.app', ['pageTitle' => __('Photo gallery')])

@section('content')
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-slate-100 mb-2">معرض الصور</h1>
            <p class="text-gray-600 dark:text-slate-300">اختر الموسم والفعالية لعرض الصور والفيديوهات</p>
        </div>

        <!-- Selection Form -->
        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-lg dark:border dark:border-slate-700 p-6 mb-8 border-2 border-blue-300 dark:border-slate-700">
            <div class="grid md:grid-cols-2 gap-6">
                <!-- Season Selection -->
                <div class="relative">
                    <label for="season_id" class="block mb-2 text-sm font-semibold text-gray-700 dark:text-slate-200">{{ __('Choose season') }}</label>
                    <select id="season_id" name="season_id"
                        class="w-full h-12 ps-4 border rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-900 text-slate-600 dark:text-slate-300 focus:border-blue-500 focus:outline-none">
                        <option value="">-- اختر الموسم --</option>
                        @foreach ($seasons as $season)
                            <option value="{{ $season->SeasonID }}">
                                {{ $season->SeasonName }} ({{ $season->SeasonYear }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Event Selection -->
                <div class="relative">
                    <label for="event_id" class="block mb-2 text-sm font-semibold text-gray-700 dark:text-slate-200">{{ __('Choose event') }}</label>
                    <select id="event_id" name="event_id" disabled
                        class="w-full h-12 ps-4 border rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-900 text-slate-400 dark:text-slate-500 focus:border-blue-500 focus:outline-none">
                        <option value="">-- اختر الفعالية --</option>
                    </select>
                </div>
            </div>

            <!-- Load Media Button -->
            <div class="flex justify-center mt-6">
                <button id="loadMediaBtn" disabled
                    class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-blue-500 text-white hover:bg-blue-600 transition disabled:bg-gray-300 disabled:cursor-not-allowed">
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                    تحميل الوسائط
                </button>
            </div>
        </div>

        <!-- Loading Indicator -->
        <div id="loadingIndicator" class="hidden text-center py-8">
            <div class="inline-flex items-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 ml-3"></div>
                <span class="text-gray-600 dark:text-slate-300">جاري تحميل الوسائط...</span>
            </div>
        </div>

        <!-- Selected Event Info -->





        <!-- No Media Message -->
        <div id="noMediaMessage" class="hidden text-center py-12">
            <div class="text-6xl text-gray-300 dark:text-slate-600 mb-4">🎬</div>
            <h3 class="text-xl font-semibold text-gray-600 dark:text-slate-300 mb-2">لا توجد وسائط</h3>
            <p class="text-gray-500 dark:text-slate-400">لم يتم العثور على صور أو فيديوهات لهذه الفعالية</p>
        </div>

        <!-- Media Gallery -->
        <div id="mediaGallery" class="hidden">


            <div id="mediaGrid"></div>
        </div>

        <!-- Media Modal -->
        <div id="videoModal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-900 rounded-lg max-w-4xl w-full max-h-[90vh] overflow-hidden dark:border dark:border-slate-700">
                <div class="flex justify-between items-center p-4 border-b dark:border-slate-700">
                    <h3 id="videoTitle" class="text-lg font-semibold text-slate-800 dark:text-slate-100"></h3>
                    <button id="closeModal" class="text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-slate-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>
                <div class="p-4">
                    <div id="videoContainer" class="aspect-video bg-gray-100 dark:bg-slate-800 rounded"></div>
                    <div class="flex gap-2 mt-4">
                        <a id="openDriveLink" href="#" target="_blank"
                            class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                            فتح في Drive
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const seasonSelect = document.getElementById('season_id');
            const eventSelect = document.getElementById('event_id');
            const loadMediaBtn = document.getElementById('loadMediaBtn');
            const loadingIndicator = document.getElementById('loadingIndicator');


            const mediaGallery = document.getElementById('mediaGallery');
            const mediaGrid = document.getElementById('mediaGrid');
            const mediaCount = document.getElementById('mediaCount');
            const noMediaMessage = document.getElementById('noMediaMessage');
            const videoModal = document.getElementById('videoModal');
            const videoContainer = document.getElementById('videoContainer');
            const videoTitle = document.getElementById('videoTitle');
            const closeModal = document.getElementById('closeModal');
            const openDriveLink = document.getElementById('openDriveLink');

            const filterAll = document.getElementById('filterAll');
            const filterPhotos = document.getElementById('filterPhotos');
            const filterVideos = document.getElementById('filterVideos');

            let selectedEvent = null;
            let allMediaData = [];

            // ---------------- Season & Event ----------------
            seasonSelect.addEventListener('change', function() {
                const seasonId = this.value;
                eventSelect.innerHTML = '<option value="">-- اختر الفعالية --</option>';
                eventSelect.disabled = true;
                loadMediaBtn.disabled = true;
                hideAllSections();

                if (!seasonId) return;

                eventSelect.innerHTML = '<option value="">{{ __('Loading...') }}</option>';
                fetch(`{{ route('media.getEventsForPages') }}?seasonID=${seasonId}`)
                    .then(res => res.json())
                    .then(events => {
                        eventSelect.innerHTML = '<option value="">-- اختر الفعالية --</option>';
                        if (events.length) {
                            events.forEach(event => {
                                const opt = document.createElement('option');
                                opt.value = event.SeasonEventID;
                                opt.textContent =
                                    `${event.EventName} (${event.EventStartDate} → ${event.EventEndDate})`;
                                opt.dataset.eventName = event.EventName;
                                opt.dataset.startDate = event.EventStartDate;
                                opt.dataset.endDate = event.EventEndDate;
                                eventSelect.appendChild(opt);
                            });
                            eventSelect.disabled = false;
                        } else {
                            eventSelect.innerHTML = '<option value="">لا توجد فعاليات</option>';
                        }
                    }).catch(() => eventSelect.innerHTML =
                        '<option value="">{{ __('Error loading events') }}</option>');
            });

            eventSelect.addEventListener('change', function() {
                loadMediaBtn.disabled = !this.value;
                hideAllSections();
                if (this.value) {
                    const sel = this.options[this.selectedIndex];
                    selectedEvent = {
                        id: this.value,
                        name: sel.dataset.eventName,
                        startDate: sel.dataset.startDate,
                        endDate: sel.dataset.endDate
                    };
                }
            });

            // ---------------- Load Media ----------------
            loadMediaBtn.addEventListener('click', function() {
                if (!selectedEvent) return;
                hideAllSections();
                loadingIndicator.classList.remove('hidden');


                fetch(`{{ route('media.getMediaForEvent') }}?seasonEventID=${selectedEvent.id}`)
                    .then(res => res.json())
                    .then(data => {
                        loadingIndicator.classList.add('hidden');


                        if (data.length) {
                            allMediaData = data.map(m => ({
                                ...m,
                                type: detectMediaType(m.DriveLink)
                            }));
                            displayMedia(allMediaData);
                        } else {
                            noMediaMessage.classList.remove('hidden');
                        }
                    }).catch(() => {
                        loadingIndicator.classList.add('hidden');

                        noMediaMessage.classList.remove('hidden');
                    });
            });

            // ---------------- Filters ----------------
            filterAll.addEventListener('click', () => filterMedia('all'));
            filterPhotos.addEventListener('click', () => filterMedia('photo'));
            filterVideos.addEventListener('click', () => filterMedia('video'));

            // ---------------- Modal ----------------
            closeModal.addEventListener('click', closeVideoModal);
            videoModal.addEventListener('click', e => {
                if (e.target === videoModal) closeVideoModal();
            });

            function hideAllSections() {
                loadingIndicator.classList.add('hidden');

                mediaGallery.classList.add('hidden');
                noMediaMessage.classList.add('hidden');
                closeVideoModal();
            }

            function detectMediaType(link) {
                if (link.includes('/folders/')) return 'folder';
                const videoExt = ['mp4', 'mov', 'avi', 'mkv', 'webm', 'm4v'];
                const photoExt = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
                const ext = link.split('.').pop().toLowerCase();
                if (videoExt.includes(ext)) return 'video';
                if (photoExt.includes(ext)) return 'photo';
                return 'photo';
            }

            function filterMedia(type) {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                let filtered = allMediaData;
                if (type === 'photo') {
                    filtered = allMediaData.filter(m => m.type === 'photo');
                    filterPhotos.classList.add('active');
                } else if (type === 'video') {
                    filtered = allMediaData.filter(m => m.type === 'video');
                    filterVideos.classList.add('active');
                } else {
                    filterAll.classList.add('active');
                }
                displayMedia(filtered);
            }

            function displayMedia(mediaData) {
                mediaGrid.innerHTML = '';


                mediaData.forEach((media, idx) => {
                    if (media.DriveLink) mediaGrid.appendChild(createMediaCard(media.DriveLink, media.type,
                        idx + 1));
                });
                mediaGallery.classList.remove('hidden');
            }

            function createMediaCard(link, type, index) {
                const card = document.createElement('div');
                card.classList.add('media-card', 'bg-white', 'dark:bg-slate-900', 'rounded-lg', 'shadow-md', 'overflow-hidden',
                    'hover:shadow-lg', 'transition-shadow', 'dark:border', 'dark:border-slate-700', type);

                if (type === 'folder') {
                    // Validate folderId extraction
                    let folderId = '';
                    try {
                        folderId = link.split('/folders/')[1].split(/[?#]/)[0];
                    } catch (e) {}
                    const aspectDiv = document.createElement('div');
                    aspectDiv.classList.add('aspect-square', 'flex', 'items-center', 'justify-center');
                    if (folderId) {
                        const iframe = document.createElement('iframe');
                        iframe.src = `https://drive.google.com/embeddedfolderview?id=${folderId}#grid`;
                        iframe.width = '100%';
                        iframe.height = '100%';
                        iframe.setAttribute('frameborder', '0');
                        aspectDiv.appendChild(iframe);
                    }
                    card.appendChild(aspectDiv);
                    const labelDiv = document.createElement('div');
                    labelDiv.classList.add('p-3', 'text-center', 'font-semibold', 'text-gray-700', 'dark:text-slate-200');
                    labelDiv.textContent = `مجلد الوسائط ${index}`;
                    card.appendChild(labelDiv);
                } else {
                    const fileId = extractFileId(link);
                    const thumbnail = fileId ? `https://drive.google.com/uc?export=view&id=${fileId}` : '';
                    const icon = type === 'video' ? '🎬' : '📷';
                    const color = type === 'video' ? 'bg-purple-500' : 'bg-green-500';
                    const typeText = type === 'video' ? 'فيديو' : 'صورة';

                    // Aspect square div
                    const aspectDiv = document.createElement('div');
                    aspectDiv.classList.add('aspect-square', 'bg-gray-100', 'dark:bg-slate-800', 'flex', 'items-center',
                        'justify-center', 'relative', 'cursor-pointer');

                    // Click behavior
                    aspectDiv.addEventListener('click', function() {
                        window.openMedia(link, type, `${typeText} ${index}`);
                    });

                    if (thumbnail) {
                        const img = document.createElement('img');
                        img.src = thumbnail;
                        img.alt = `${typeText} ${index}`;
                        img.classList.add('w-full', 'h-full', 'object-cover');
                        img.onerror = function() {
                            // Remove image and show fallback icon
                            if (img.parentElement) {
                                img.parentElement.innerHTML = '';
                                const fallbackDiv = document.createElement('div');
                                fallbackDiv.classList.add('text-gray-400', 'dark:text-slate-500', 'text-4xl');
                                fallbackDiv.textContent = icon;
                                img.parentElement.appendChild(fallbackDiv);
                            }
                        };
                        aspectDiv.appendChild(img);
                    } else {
                        const fallbackDiv = document.createElement('div');
                        fallbackDiv.classList.add('text-gray-400', 'dark:text-slate-500', 'text-4xl');
                        fallbackDiv.textContent = icon;
                        aspectDiv.appendChild(fallbackDiv);
                    }

                    // Type label
                    const typeLabel = document.createElement('div');
                    typeLabel.classList.add('absolute', 'top-2', 'right-2', color, 'text-white', 'text-xs', 'px-2',
                        'py-1', 'rounded');
                    typeLabel.textContent = `${typeText} ${index}`;
                    aspectDiv.appendChild(typeLabel);

                    card.appendChild(aspectDiv);

                    // Drive link
                    const p3Div = document.createElement('div');
                    p3Div.classList.add('p-3');
                    const anchor = document.createElement('a');
                    anchor.setAttribute('target', '_blank');
                    anchor.classList.add('inline-flex', 'items-center', 'justify-center', 'w-full', 'px-3', 'py-2',
                        'text-sm', 'bg-blue-500', 'text-white', 'rounded', 'hover:bg-blue-600', 'transition');
                    anchor.textContent = 'فتح في Drive';
                    // Validate/normalize URL (basic check)
                    if (/^https:\/\//.test(link) || /^http:\/\//.test(link)) {
                        anchor.setAttribute('href', link);
                    } else {
                        anchor.setAttribute('href', '#');
                    }
                    p3Div.appendChild(anchor);
                    card.appendChild(p3Div);
                }
                return card;
            }

            window.openMedia = function(link, type, title) {
                videoTitle.textContent = title;
                openDriveLink.href = link;
                if (type === 'video' && extractFileId(link)) {
                    videoContainer.innerHTML =
                        `<iframe src="https://drive.google.com/file/d/${extractFileId(link)}/preview" width="100%" height="100%" allow="autoplay" frameborder="0" class="rounded"></iframe>`;
                } else if (type === 'photo' && extractFileId(link)) {
                    videoContainer.innerHTML =
                        `<img src="https://drive.google.com/uc?export=view&id=${extractFileId(link)}" alt="${title}" class="w-full h-full object-contain rounded" />`;
                } else {
                    videoContainer.innerHTML =
                        `<div class="flex items-center justify-center h-full text-gray-500 dark:text-slate-400 text-center"><div class="text-6xl mb-4">${type==='video'?'🎬':'📷'}</div>لا يمكن عرض الملف هنا<br>يرجى فتحه في Drive</div>`;
                }
                videoModal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            function closeVideoModal() {
                videoModal.classList.add('hidden');
                videoContainer.innerHTML = '';
                document.body.style.overflow = 'auto';
            }

            function extractFileId(url) {
                const patterns = [/\/file\/d\/([a-zA-Z0-9-_]+)/, /id=([a-zA-Z0-9-_]+)/, /\/d\/([a-zA-Z0-9-_]+)/];
                for (const p of patterns) {
                    const m = url.match(p);
                    if (m) return m[1];
                }
                return null;
            }
        });
    </script>

    <style>
        .filter-btn.active {
            @apply font-semibold ring-2 ring-blue-300;
        }

        .media-card.photo {
            border-left: 4px solid #10b981;
        }

        .media-card.video {
            border-left: 4px solid #8b5cf6;
        }

        .media-card.folder {
            border-left: 4px solid #f59e0b;
        }
    </style>
@endsection
