@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="mb-4">متابعة حجز المخدومين</h3>

        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label for="season_id" class="form-label">الموسم</label>
                    <select id="season_id" class="form-select">
                        <option value="">اختر الموسم</option>
                        @foreach ($seasons as $season)
                            <option value="{{ $season->SeasonID }}">
                                {{ $season->SeasonName }} - {{ $season->SeasonYear }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="season_event_id" class="form-label">الفعالية</label>
                    <select id="season_event_id" class="form-select">
                        <option value="">اختر الفعالية</option>
                    </select>
                </div>

                <button type="button" class="btn btn-primary" onclick="goToPage()">عرض</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('season_id').addEventListener('change', function() {
            const seasonID = this.value;
            const eventSelect = document.getElementById('season_event_id');

            eventSelect.innerHTML = '<option value="">اختر الفعالية</option>';

            if (!seasonID) return;

            fetch(`{{ route('eventServantFollowup.getEventsWithPlan') }}?seasonID=${seasonID}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(event => {
                        const option = document.createElement('option');
                        option.value = event.SeasonEventID;
                        option.textContent =
                            `${event.EventTypeName} - ${event.EventName} (${event.EventStartDate})`;
                        eventSelect.appendChild(option);
                    });
                });
        });

        function goToPage() {
            const seasonEventID = document.getElementById('season_event_id').value;

            if (!seasonEventID) {
                alert('من فضلك اختر الفعالية');
                return;
            }

            window.location.href = `{{ url('event-servant-followup/event') }}/${seasonEventID}`;
        }
    </script>
@endpush
