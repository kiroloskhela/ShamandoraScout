@props(['meta' => true])
@if ($meta)
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endif
<script>
(function () {
    var url = @json(route('csrf.token', absolute: false));
    var intervalMs = 10 * 60 * 1000;

    function apply(token) {
        if (!token) return;
        document.querySelectorAll('meta[name="csrf-token"]').forEach(function (el) {
            el.setAttribute('content', token);
        });
        document.querySelectorAll('input[name="_token"]').forEach(function (el) {
            el.value = token;
        });
        if (window.axios && window.axios.defaults) {
            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
        }
    }

    function ping() {
        var ctrl = typeof AbortController === 'function' ? new AbortController() : null;
        var timer = setTimeout(function () {
            if (ctrl) ctrl.abort();
        }, 2500);
        return fetch(url, {
            credentials: 'same-origin',
            cache: 'no-store',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            signal: ctrl ? ctrl.signal : undefined
        }).then(function (res) {
            return res.ok ? res.json() : Promise.reject();
        }).then(function (data) {
            if (data && data.token) apply(data.token);
        }).catch(function () {}).finally(function () {
            clearTimeout(timer);
        });
    }

    window.refreshCsrfToken = ping;
    setInterval(ping, intervalMs);
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') ping();
    });
})();
</script>
