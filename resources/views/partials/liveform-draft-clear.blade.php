<script>
(function () {
    try {
        ['shamandora.liveform.step1', 'shamandora.liveform.step2', 'shamandora.liveform.questions'].forEach(function (key) {
            localStorage.removeItem(key);
        });
    } catch (e) {}
})();
</script>
