@props([
    'events' => [],
])

@php
    $calId = 'calendar_' . uniqid();

    $fcEvents = collect($events)
        ->unique('EventID')
        ->map(function ($e) {
            return [
                'id' => $e->EventID ?? null,
                'title' => $e->EventName ?? '',
                'start' => $e->EventStartDate ?? null,
                'end' => $e->EventEndDate ?? null,
                'extendedProps' => [
                    'type' => $e->EventTypeName ?? null,
                    'season' => $e->SeasonName ?? null,
                    'year' => $e->SeasonYear ?? null,
                ],
            ];
        })
        ->values();
@endphp

@once
    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/index.global.min.css">

        <style>
            .sc-calendar-card {
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: 1rem;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
                overflow: hidden;
            }

            .sc-calendar-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                padding: 1rem 1rem 0.75rem;
                border-bottom: 1px solid #f3f4f6;
                flex-wrap: wrap;
            }

            .sc-calendar-title {
                font-size: 1.125rem;
                font-weight: 800;
                color: #111827;
                margin: 0;
            }

            .sc-calendar-count {
                font-size: 0.875rem;
                font-weight: 700;
                color: #2563eb;
                background: #eff6ff;
                padding: 0.4rem 0.75rem;
                border-radius: 9999px;
            }

            .sc-calendar-body {
                padding: 0.75rem;
            }

            .fc-custom-calendar {
                --fc-border-color: #e5e7eb;
                --fc-page-bg-color: #ffffff;
                --fc-neutral-bg-color: #f9fafb;
                --fc-today-bg-color: #eff6ff;
                --fc-list-event-hover-bg-color: #f8fafc;
            }

            .fc-custom-calendar .fc-header-toolbar {
                gap: 0.75rem;
                margin-bottom: 1rem;
                flex-wrap: wrap;
            }

            .fc-custom-calendar .fc-toolbar-chunk {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                flex-wrap: wrap;
            }

            .fc-custom-calendar .fc-toolbar-title {
                font-size: 1.1rem;
                font-weight: 800;
                color: #111827;
            }

            .fc-custom-calendar .fc-button {
                background: #2563eb;
                border: none;
                border-radius: 0.65rem;
                padding: 0.5rem 0.75rem;
                font-size: 0.875rem;
                font-weight: 700;
                text-transform: none;
                box-shadow: none;
            }

            .fc-custom-calendar .fc-button:hover:not(:disabled) {
                background: #1d4ed8;
            }

            .fc-custom-calendar .fc-button:disabled {
                opacity: 0.5;
            }

            .fc-custom-calendar .fc-button-active {
                background: #1e3a8a !important;
            }

            .fc-custom-calendar .fc-col-header-cell {
                background: #f9fafb;
                color: #374151;
                font-weight: 700;
                font-size: 0.875rem;
                padding: 0.75rem 0.25rem;
            }

            .fc-custom-calendar .fc-daygrid-day-number {
                color: #374151;
                font-weight: 700;
                padding: 0.4rem;
                font-size: 0.9rem;
            }

            .fc-custom-calendar .fc-day-today {
                background: #eff6ff !important;
            }

            .fc-custom-calendar .fc-day-today .fc-daygrid-day-number {
                background: #2563eb;
                color: #fff;
                width: 1.9rem;
                height: 1.9rem;
                border-radius: 9999px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            .fc-custom-calendar .fc-daygrid-event,
            .fc-custom-calendar .fc-timegrid-event {
                border: 0;
                border-radius: 0.5rem;
                padding: 0.2rem 0.45rem;
                font-size: 0.78rem;
                font-weight: 700;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
            }

            .fc-custom-calendar .fc-event-title {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .fc-custom-calendar .fc-list-event-title a,
            .fc-custom-calendar .fc-list-event-time {
                color: #111827;
                font-weight: 600;
            }

            .sc-calendar-legend {
                display: flex;
                gap: 0.75rem;
                flex-wrap: wrap;
                padding: 0.75rem 1rem 1rem;
                border-top: 1px solid #f3f4f6;
                background: #fafafa;
            }

            .sc-legend-item {
                display: inline-flex;
                align-items: center;
                gap: 0.45rem;
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: 9999px;
                padding: 0.4rem 0.7rem;
                font-size: 0.82rem;
                color: #374151;
                font-weight: 600;
            }

            .sc-legend-color {
                width: 0.8rem;
                height: 0.8rem;
                border-radius: 9999px;
                flex-shrink: 0;
            }

            .sc-event-modal {
                position: fixed;
                inset: 0;
                z-index: 9999;
                display: none;
                align-items: center;
                justify-content: center;
                background: rgba(0, 0, 0, 0.45);
                backdrop-filter: blur(3px);
                padding: 1rem;
            }

            .sc-event-modal.active {
                display: flex;
            }

            .sc-event-modal-content {
                width: 100%;
                max-width: 32rem;
                background: #fff;
                border-radius: 1rem;
                box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18);
                overflow: hidden;
            }

            .sc-event-modal-header {
                display: flex;
                align-items: start;
                justify-content: space-between;
                gap: 1rem;
                padding: 1rem 1rem 0.75rem;
                border-bottom: 1px solid #f3f4f6;
            }

            .sc-event-modal-title {
                font-size: 1.05rem;
                font-weight: 800;
                color: #111827;
                margin: 0;
            }

            .sc-event-modal-close {
                width: 2.2rem;
                height: 2.2rem;
                border: 0;
                border-radius: 9999px;
                background: #f3f4f6;
                color: #374151;
                cursor: pointer;
                font-size: 1.2rem;
                line-height: 1;
            }

            .sc-event-modal-close:hover {
                background: #e5e7eb;
            }

            .sc-event-modal-body {
                padding: 1rem;
                display: grid;
                gap: 0.75rem;
            }

            .sc-event-detail {
                background: #f9fafb;
                border: 1px solid #eef2f7;
                border-radius: 0.85rem;
                padding: 0.85rem;
            }

            .sc-event-label {
                font-size: 0.75rem;
                color: #6b7280;
                font-weight: 700;
                margin-bottom: 0.25rem;
            }

            .sc-event-value {
                font-size: 0.92rem;
                color: #111827;
                font-weight: 700;
                line-height: 1.6;
            }

            .sc-event-badge {
                display: inline-flex;
                align-items: center;
                border-radius: 9999px;
                padding: 0.35rem 0.75rem;
                font-size: 0.78rem;
                font-weight: 800;
            }

            @media (max-width: 768px) {
                .sc-calendar-head {
                    padding: 0.9rem 0.9rem 0.7rem;
                }

                .sc-calendar-title {
                    font-size: 1rem;
                }

                .sc-calendar-count {
                    font-size: 0.8rem;
                    padding: 0.35rem 0.65rem;
                }

                .sc-calendar-body {
                    padding: 0.5rem;
                }

                .fc-custom-calendar .fc-toolbar {
                    display: flex;
                    flex-direction: column;
                    align-items: stretch;
                }

                .fc-custom-calendar .fc-toolbar-chunk {
                    justify-content: center;
                }

                .fc-custom-calendar .fc-toolbar-title {
                    font-size: 1rem;
                    text-align: center;
                    width: 100%;
                }

                .fc-custom-calendar .fc-button {
                    padding: 0.45rem 0.65rem;
                    font-size: 0.8rem;
                }

                .fc-custom-calendar .fc-col-header-cell {
                    padding: 0.55rem 0.1rem;
                    font-size: 0.72rem;
                }

                .fc-custom-calendar .fc-daygrid-day-number {
                    font-size: 0.78rem;
                    padding: 0.25rem;
                }

                .fc-custom-calendar .fc-daygrid-event {
                    font-size: 0.7rem;
                    padding: 0.12rem 0.3rem;
                    margin-inline: 1px;
                }

                .sc-calendar-legend {
                    gap: 0.5rem;
                    padding: 0.75rem;
                }

                .sc-legend-item {
                    font-size: 0.75rem;
                    padding: 0.35rem 0.6rem;
                }

                .sc-event-modal-content {
                    max-width: 100%;
                    border-radius: 0.85rem;
                }

                .sc-event-modal-header,
                .sc-event-modal-body {
                    padding: 0.85rem;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/index.global.min.js"></script>
    @endpush
@endonce

<div class="sc-calendar-card">
    <div class="sc-calendar-head">
        <h3 class="sc-calendar-title">📅 التقويم</h3>
        <div id="{{ $calId }}_count" class="sc-calendar-count"></div>
    </div>

    <div class="sc-calendar-body">
        <div id="{{ $calId }}" class="fc-custom-calendar"></div>
    </div>

    <div id="{{ $calId }}_legend" class="sc-calendar-legend"></div>
</div>

<div id="{{ $calId }}_modal" class="sc-event-modal">
    <div class="sc-event-modal-content">
        <div class="sc-event-modal-header">
            <h4 id="{{ $calId }}_modal_title" class="sc-event-modal-title"></h4>
            <button type="button" class="sc-event-modal-close"
                onclick="document.getElementById('{{ $calId }}_modal').classList.remove('active')">×</button>
        </div>
        <div id="{{ $calId }}_modal_body" class="sc-event-modal-body"></div>
    </div>
</div>

@push('scripts')
    <script>
        (function() {
            const raw = @json($fcEvents);

            const typeColors = {
                'يوم كشفي': {
                    bg: '#3b82f6',
                    light: '#dbeafe'
                },
                'معسكر مجمع': {
                    bg: '#10b981',
                    light: '#d1fae5'
                },
                'معسكر': {
                    bg: '#f59e0b',
                    light: '#fef3c7'
                },
                'فعالية': {
                    bg: '#8b5cf6',
                    light: '#ede9fe'
                },
                'يوم روحي': {
                    bg: '#ec4899',
                    light: '#fce7f3'
                },
                'يوم مجمع': {
                    bg: '#60a5fa',
                    light: '#dbeafe'
                },
            };

            const defaultColor = {
                bg: '#6b7280',
                light: '#f3f4f6'
            };

            const legendEl = document.getElementById('{{ $calId }}_legend');
            const countEl = document.getElementById('{{ $calId }}_count');
            const modalEl = document.getElementById('{{ $calId }}_modal');
            const modalTitleEl = document.getElementById('{{ $calId }}_modal_title');
            const modalBodyEl = document.getElementById('{{ $calId }}_modal_body');
            const calendarEl = document.getElementById('{{ $calId }}');

            const uniqueTypes = [...new Set(raw.map(e => e.extendedProps?.type).filter(Boolean))];

            legendEl.innerHTML = uniqueTypes.map(type => {
                const color = typeColors[type] || defaultColor;
                return `
                    <div class="sc-legend-item">
                        <span class="sc-legend-color" style="background:${color.bg}"></span>
                        <span>${type}</span>
                    </div>
                `;
            }).join('');

            const events = raw.map(e => {
                const color = (e.extendedProps?.type && typeColors[e.extendedProps.type]) || defaultColor;

                return {
                    ...e,
                    backgroundColor: color.bg,
                    borderColor: color.bg,
                    textColor: '#ffffff'
                };
            });

            countEl.textContent = `${events.length} فعالية`;

            function getResponsiveView() {
                return window.innerWidth < 640 ? 'listWeek' : 'dayGridMonth';
            }

            function getResponsiveToolbar() {
                if (window.innerWidth < 640) {
                    return {
                        start: 'title',
                        center: '',
                        end: 'today prev,next'
                    };
                }

                if (window.innerWidth < 1024) {
                    return {
                        start: 'prev,next today',
                        center: 'title',
                        end: 'dayGridMonth,listWeek'
                    };
                }

                return {
                    start: 'prev,next today',
                    center: 'title',
                    end: 'dayGridMonth,timeGridWeek,listWeek'
                };
            }

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: getResponsiveView(),
                locale: 'ar',
                direction: 'rtl',
                firstDay: 6,
                height: 'auto',
                timeZone: 'local',
                dayMaxEventRows: window.innerWidth < 640 ? 2 : 3,
                moreLinkText: function(n) {
                    return `+${n} أكثر`;
                },
                headerToolbar: getResponsiveToolbar(),
                buttonText: {
                    today: 'اليوم',
                    month: 'شهر',
                    week: 'أسبوع',
                    list: 'قائمة'
                },
                events: events,
                eventClick(info) {
                    const e = info.event;
                    const props = e.extendedProps || {};
                    const color = typeColors[props.type] || defaultColor;

                    const startDate = e.start ? e.start.toLocaleDateString('ar-EG', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    }) : '—';

                    const endDate = e.end ? e.end.toLocaleDateString('ar-EG', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    }) : 'نفس اليوم';

                    modalTitleEl.textContent = e.title || 'تفاصيل الفعالية';

                    modalBodyEl.innerHTML = `
                        <div class="sc-event-detail">
                            <div class="sc-event-label">تاريخ البداية</div>
                            <div class="sc-event-value">${startDate}</div>
                        </div>

                        <div class="sc-event-detail">
                            <div class="sc-event-label">تاريخ النهاية</div>
                            <div class="sc-event-value">${endDate}</div>
                        </div>

                        ${props.type ? `
                                            <div class="sc-event-detail">
                                                <div class="sc-event-label">نوع الفعالية</div>
                                                <div class="sc-event-value">
                                                    <span class="sc-event-badge" style="background:${color.light}; color:${color.bg};">
                                                        ${props.type}
                                                    </span>
                                                </div>
                                            </div>
                                        ` : ''}

                        ${props.season ? `
                                            <div class="sc-event-detail">
                                                <div class="sc-event-label">الموسم</div>
                                                <div class="sc-event-value">${props.season}</div>
                                            </div>
                                        ` : ''}

                        ${props.year ? `
                                            <div class="sc-event-detail">
                                                <div class="sc-event-label">السنة</div>
                                                <div class="sc-event-value">${props.year}</div>
                                            </div>
                                        ` : ''}
                    `;

                    modalEl.classList.add('active');
                },
            });

            calendar.render();

            window.addEventListener('resize', () => {
                calendar.setOption('headerToolbar', getResponsiveToolbar());
                calendar.setOption('dayMaxEventRows', window.innerWidth < 640 ? 2 : 3);

                const currentView = calendar.view.type;
                const targetView = getResponsiveView();

                if (window.innerWidth < 640 && currentView !== 'listWeek') {
                    calendar.changeView('listWeek');
                } else if (window.innerWidth >= 640 && currentView === 'listWeek') {
                    calendar.changeView('dayGridMonth');
                }
            });

            modalEl.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                }
            });
        })();
    </script>
@endpush
