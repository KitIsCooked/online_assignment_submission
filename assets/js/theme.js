(function () {
    const body = document.getElementById('body');
    const toggle = document.getElementById('themeToggle');
    if (!body || !toggle) return;

    const savedTheme = localStorage.getItem('theme') || 'light';
    body.classList.remove('light', 'dark');
    body.classList.add(savedTheme);
    toggle.textContent = savedTheme === 'light' ? '🌙' : '☀️';

    toggle.addEventListener('click', function () {
        const current = body.classList.contains('dark') ? 'dark' : 'light';
        const next = current === 'light' ? 'dark' : 'light';
        body.classList.remove('light', 'dark');
        body.classList.add(next);
        localStorage.setItem('theme', next);
        toggle.textContent = next === 'light' ? '🌙' : '☀️';
    });
})();
