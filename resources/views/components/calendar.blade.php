@props([
    'events' => [],
])

@php
    // unique DOM id so you can render multiple calendars if needed
    $calId = 'calendar_' . uniqid();
    // Map your PHP events to a simpler array for JSON
    $fcEvents = collect($events)
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
            .fc-custom-calendar {
                --fc-border-color: #e5e7eb;
                --fc-today-bg-color: #f0f9ff;
                --fc-neutral-bg-color: #f9fafb;
            }

            .fc-custom-calendar .fc-toolbar-title {
                font-size: 1.5rem;
                font-weight: 700;
                color: #111827;
            }

            .fc-custom-calendar .fc-button {
                background-color: #3b82f6;
                border: none;
                padding: 0.5rem 1rem;
                font-weight: 500;
                transition: all 0.2s;
                text-transform: none;
            }

            .fc-custom-calendar .fc-button:hover:not(:disabled) {
                background-color: #2563eb;
                transform: translateY(-1px);
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            }

            .fc-custom-calendar .fc-button:active:not(:disabled) {
                background-color: #1d4ed8;
            }

            .fc-custom-calendar .fc-button:disabled {
                opacity: 0.5;
                cursor: not-allowed;
            }

            .fc-custom-calendar .fc-button-active {
                background-color: #1e40af !important;
            }

            .fc-custom-calendar .fc-daygrid-day-number {
                font-weight: 600;
                color: #374151;
                padding: 0.5rem;
            }

            .fc-custom-calendar .fc-col-header-cell {
                background-color: #f3f4f6;
                font-weight: 600;
                color: #1f2937;
                padding: 0.75rem;
                border: none;
            }

            .fc-custom-calendar .fc-daygrid-day {
                transition: background-color 0.2s;
            }

            .fc-custom-calendar .fc-daygrid-day:hover {
                background-color: #fafafa;
            }

            .fc-custom-calendar .fc-event {
                border-radius: 0.375rem;
                padding: 0.25rem 0.5rem;
                margin: 0.125rem 0.25rem;
                font-size: 0.875rem;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.2s;
                border: none;
                box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
                text-align: center;
            }

            .fc-custom-calendar .fc-event:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            }

            .fc-custom-calendar .fc-day-today {
                background-color: var(--fc-today-bg-color) !important;
                position: relative;
            }

            .fc-custom-calendar .fc-day-today .fc-daygrid-day-number {
                background-color: #3b82f6;
                color: white;
                border-radius: 50%;
                width: 2rem;
                height: 2rem;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            /* Custom modal styling */
            .event-modal {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 9999;
                backdrop-filter: blur(4px);
                animation: fadeIn 0.2s;
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                }

                to {
                    opacity: 1;
                }
            }

            .event-modal.active {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .event-modal-content {
                background: white;
                border-radius: 0.75rem;
                padding: 2rem;
                max-width: 28rem;
                width: 90%;
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
                animation: slideUp 0.3s;
            }

            @keyframes slideUp {
                from {
                    transform: translateY(20px);
                    opacity: 0;
                }

                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }

            .event-modal-header {
                display: flex;
                justify-content: space-between;
                align-items: start;
                margin-bottom: 1.5rem;
            }

            .event-modal-title {
                font-size: 1.25rem;
                font-weight: 700;
                color: #111827;
                margin: 0;
            }

            .event-modal-close {
                background: none;
                border: none;
                font-size: 1.5rem;
                color: #6b7280;
                cursor: pointer;
                padding: 0;
                width: 2rem;
                height: 2rem;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 0.375rem;
                transition: all 0.2s;
            }

            .event-modal-close:hover {
                background-color: #f3f4f6;
                color: #111827;
            }

            .event-detail {
                display: flex;
                align-items: start;
                padding: 0.75rem;
                margin-bottom: 0.5rem;
                background-color: #f9fafb;
                border-radius: 0.5rem;
            }

            .event-detail-icon {
                font-size: 1.25rem;
                margin-left: 0.75rem;
                color: #6b7280;
            }

            .event-detail-content {
                flex: 1;
            }

            .event-detail-label {
                font-size: 0.75rem;
                font-weight: 600;
                text-transform: uppercase;
                color: #6b7280;
                margin-bottom: 0.25rem;
            }

            .event-detail-value {
                font-size: 0.875rem;
                color: #111827;
                font-weight: 500;
            }

            .event-badge {
                display: inline-block;
                padding: 0.25rem 0.75rem;
                border-radius: 9999px;
                font-size: 0.75rem;
                font-weight: 600;
                margin-top: 0.25rem;
            }

            /* Legend styles */
            .calendar-legend {
                display: flex;
                flex-wrap: wrap;
                gap: 1rem;
                margin-top: 1rem;
                padding: 1rem;
                background-color: #f9fafb;
                border-radius: 0.5rem;
            }

            .legend-item {
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .legend-color {
                width: 1rem;
                height: 1rem;
                border-radius: 0.25rem;
            }

            .legend-label {
                font-size: 0.875rem;
                color: #4b5563;
                font-weight: 500;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/index.global.min.js"></script>
    @endpush
@endonce

<div class="bg-white rounded-xl shadow-lg overflow-hidden">
    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-gray-900">📅 التقويم</h3>
            <div class="text-sm text-gray-500">
                <span id="{{ $calId }}_count" class="font-semibold text-blue-600"></span>
            </div>
        </div>

        <div id="{{ $calId }}" class="fc-custom-calendar rounded-lg"></div>
        <div class="calendar-legend" id="{{ $calId }}_legend"></div>
    </div>
</div>

<!-- Modal -->
<div id="{{ $calId }}_modal" class="event-modal">
    <div class="event-modal-content">
        <div class="event-modal-header">
            <h4 class="event-modal-title" id="{{ $calId }}_modal_title"></h4>
            <button class="event-modal-close"
                onclick="document.getElementById('{{ $calId }}_modal').classList.remove('active')">
                ×
            </button>
        </div>
        <div id="{{ $calId }}_modal_body"></div>
    </div>
</div>

@push('scripts')
    <script>
        (function() {
            const raw = @json($fcEvents);

            // Enhanced type colors with gradients
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

            // Build legend
            const legendEl = document.getElementById('{{ $calId }}_legend');
            const uniqueTypes = [...new Set(raw.map(e => e.extendedProps?.type).filter(Boolean))];

            legendEl.innerHTML = uniqueTypes.map(type => {
                const color = typeColors[type] || defaultColor;
                return `
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: ${color.bg}"></div>
                        <span class="legend-label">${type}</span>
                    </div>
                `;
            }).join('');

            // Transform events for FullCalendar
            const events = raw.map(e => {
                const color = (e.extendedProps?.type && typeColors[e.extendedProps.type]) || defaultColor;
                return {
                    ...e,
                    backgroundColor: color.bg,
                    borderColor: color.bg,
                    textColor: '#fff',
                    display: 'block',
                };
            });

            // Update event count
            document.getElementById('{{ $calId }}_count').textContent = `${events.length} فعالية`;

            const el = document.getElementById('{{ $calId }}');
            if (!el) return;

            const calendar = new FullCalendar.Calendar(el, {
                initialView: 'dayGridMonth',
                locale: 'ar',
                direction: 'rtl',
                firstDay: 6,
                height: 'auto',
                timeZone: 'local',
                headerToolbar: {
                    start: 'prev,next today',
                    center: 'title',
                    end: 'dayGridMonth,timeGridWeek,listWeek'
                },
                buttonText: {
                    today: 'اليوم',
                    month: 'شهر',
                    week: 'أسبوع',
                    list: 'قائمة'
                },
                events: events,
                eventMouseEnter(info) {
                    info.el.style.transform = 'translateY(-2px)';
                    info.el.style.filter = 'brightness(1.1)';
                },
                eventMouseLeave(info) {
                    info.el.style.transform = '';
                    info.el.style.filter = '';
                },
                eventClick(info) {
                    const e = info.event;
                    const props = e.extendedProps;

                    const modal = document.getElementById('{{ $calId }}_modal');
                    const title = document.getElementById('{{ $calId }}_modal_title');
                    const body = document.getElementById('{{ $calId }}_modal_body');

                    title.textContent = e.title;

                    const startDate = e.start ? e.start.toLocaleDateString('ar-EG', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        weekday: 'long'
                    }) : '—';

                    const endDate = e.end ? e.end.toLocaleDateString('ar-EG', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        weekday: 'long'
                    }) : 'نفس اليوم';

                    const color = typeColors[props.type] || defaultColor;

                    body.innerHTML = `
                        <div class="event-detail">
                            <span class="event-detail-icon">📅</span>
                            <div class="event-detail-content">
                                <div class="event-detail-label">تاريخ البداية</div>
                                <div class="event-detail-value">${startDate}</div>
                            </div>
                        </div>
                        
                        <div class="event-detail">
                            <span class="event-detail-icon">🏁</span>
                            <div class="event-detail-content">
                                <div class="event-detail-label">تاريخ النهاية</div>
                                <div class="event-detail-value">${endDate}</div>
                            </div>
                        </div>
                        
                        ${props.type ? `
                                                                <div class="event-detail">
                                                                    <span class="event-detail-icon">🏷️</span>
                                                                    <div class="event-detail-content">
                                                                        <div class="event-detail-label">نوع الفعالية</div>
                                                                        <span class="event-badge" style="background-color: ${color.light}; color: ${color.bg};">
                                                                            ${props.type}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                ` : ''}
                        
                        ${props.season ? `
                                                                <div class="event-detail">
                                                                    <span class="event-detail-icon">🌟</span>
                                                                    <div class="event-detail-content">
                                                                        <div class="event-detail-label">الموسم</div>
                                                                        <div class="event-detail-value">${props.season}</div>
                                                                    </div>
                                                                </div>
                                                                ` : ''}
                        
                        ${props.year ? `
                                                                <div class="event-detail">
                                                                    <span class="event-detail-icon">📆</span>
                                                                    <div class="event-detail-content">
                                                                        <div class="event-detail-label">السنة</div>
                                                                        <div class="event-detail-value">${props.year}</div>
                                                                    </div>
                                                                </div>
                                                                ` : ''}
                    `;

                    modal.classList.add('active');
                }
            });

            calendar.render();

            // Close modal on outside click
            document.getElementById('{{ $calId }}_modal').addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                }
            });
        })();
    </script>
@endpush
