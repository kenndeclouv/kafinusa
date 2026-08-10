['pointerdown'].forEach(evt => {
    document.addEventListener(evt, (e) => {
        if (e.pointerType !== 'touch') return;

        const target = e.target.closest('button, a, [role="button"], [data-flux-button], [data-flux-link], label, .cursor-pointer');

        if (target && typeof navigator.vibrate === 'function') {
            navigator.vibrate(25);
        }
    }, { passive: true });
});
