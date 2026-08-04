<script>
    (function () {
        var theme = localStorage.getItem('theme');
        var dark = theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches);
        if (dark) {
            document.documentElement.classList.add('dark');
        }
    })();
</script>
