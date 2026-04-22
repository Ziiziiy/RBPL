/**
 * theme.js — Dark / Light mode toggle
 * Disimpan di localStorage, diterapkan sebelum paint (cegah flash)
 */
(function () {
    // Terapkan tema SEBELUM render (di <head> → tidak ada flash)
    var saved = localStorage.getItem('rbpl-theme');
    var prefer = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches
        ? 'dark' : 'light';
    var theme = saved || prefer;
    document.documentElement.setAttribute('data-theme', theme);

    // Setelah DOM siap: inject tombol toggle ke semua halaman
    document.addEventListener('DOMContentLoaded', function () {
        injectToggle();
        updateIcon();
    });

    function injectToggle() {
        var btn = document.createElement('button');
        btn.id        = 'theme-toggle';
        btn.title     = 'Ganti tema';
        btn.innerHTML = getIcon();
        btn.addEventListener('click', function () {
            var cur = document.documentElement.getAttribute('data-theme');
            var next = cur === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('rbpl-theme', next);
            btn.innerHTML = getIcon();
        });
        document.body.appendChild(btn);
    }

    function getIcon() {
        var cur = document.documentElement.getAttribute('data-theme');
        return cur === 'dark'
            ? '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 3a9 9 0 1 0 9 9c0-.46-.04-.92-.1-1.36a5.389 5.389 0 0 1-4.4 2.26 5.403 5.403 0 0 1-3.14-9.8c-.44-.06-.9-.1-1.36-.1z"/></svg>'
            : '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 7c-2.76 0-5 2.24-5 5s2.24 5 5 5 5-2.24 5-5-2.24-5-5-5zm0-5a1 1 0 0 1 1 1v2a1 1 0 0 1-2 0V3a1 1 0 0 1 1-1zm0 18a1 1 0 0 1 1 1v-2a1 1 0 0 1-2 0v2a1 1 0 0 1 1-1zm9-9h-2a1 1 0 0 1 0-2h2a1 1 0 0 1 0 2zM4 12H2a1 1 0 0 1 0-2h2a1 1 0 0 1 0 2zm14.24-7.66a1 1 0 0 1 0 1.41l-1.41 1.41a1 1 0 1 1-1.41-1.41l1.41-1.41a1 1 0 0 1 1.41 0zM7.17 17.24a1 1 0 0 1 0 1.41l-1.41 1.41a1 1 0 1 1-1.41-1.41l1.41-1.41a1 1 0 0 1 1.41 0zM18.24 18.66a1 1 0 0 1-1.41 0l-1.41-1.41a1 1 0 1 1 1.41-1.41l1.41 1.41a1 1 0 0 1 0 1.41zM7.17 6.76a1 1 0 0 1-1.41 0L4.34 5.34a1 1 0 1 1 1.41-1.41l1.41 1.41a1 1 0 0 1 0 1.42z"/></svg>';
    }

    function updateIcon() {
        var btn = document.getElementById('theme-toggle');
        if (btn) btn.innerHTML = getIcon();
    }
})();
