const themeToggle = document.querySelector('[data-theme-toggle]');

themeToggle?.addEventListener('click', () => {
    const isDark = document.documentElement.classList.toggle('dark');

    localStorage.setItem('theme', isDark ? 'dark' : 'light');
});

document.querySelectorAll('[data-copy-link]').forEach((button) => {
    button.addEventListener('click', async () => {
        const status = document.querySelector('[data-copy-status]');

        try {
            await navigator.clipboard.writeText(button.dataset.copyLink);
            status.textContent = button.dataset.copySuccess;
        } catch {
            status.textContent = button.dataset.copyFailure;
        }
    });
});
