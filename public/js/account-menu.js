(function () {
    const button = document.querySelector('.account-button');
    const menu = document.getElementById('accountMenu');
    if (!button || !menu) return;

    function setOpen(open) {
        menu.hidden = !open;
        button.setAttribute('aria-expanded', String(open));
    }

    button.addEventListener('click', () => setOpen(menu.hidden));

    document.addEventListener('click', event => {
        if (!event.target.closest('.account-actions')) setOpen(false);
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !menu.hidden) {
            setOpen(false);
            button.focus();
        }
    });
})();
