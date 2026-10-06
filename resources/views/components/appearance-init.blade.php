<script>
(() => {
    let preference = 'light';
    try { preference = localStorage.getItem('techshop-theme') || 'light'; } catch (_) {}
    if (!['light', 'dark'].includes(preference)) preference = 'light';
    const dark = preference === 'dark';
    document.documentElement.dataset.theme = dark ? 'dark' : 'light';
    document.documentElement.dataset.themePreference = preference;
})();
</script>
