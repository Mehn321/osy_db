/**
 * Profile Edit Modal Utilities
 * Loaded in <head> so functions are always available for inline onclick handlers,
 * even when a SPA router re-renders page content without re-running inline scripts.
 */
window.openProfileEditModal = function () {
    var modal = document.getElementById('profileEditModal');
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
};

window.closeProfileEditModal = function () {
    var modal = document.getElementById('profileEditModal');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
};

// Bind via event delegation after DOM is ready (secondary / redundant layer)
function bindProfileModalControls() {
    document.querySelectorAll('[data-profile-open]').forEach(function (btn) {
        if (btn._profileModalBound) return;
        btn._profileModalBound = true;
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            window.openProfileEditModal();
        });
    });

    document.querySelectorAll('[data-profile-close]').forEach(function (btn) {
        if (btn._profileModalBound) return;
        btn._profileModalBound = true;
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            window.closeProfileEditModal();
        });
    });

    var modal = document.getElementById('profileEditModal');
    if (modal && !modal.dataset.bound) {
        modal.dataset.bound = 'true';
        modal.addEventListener('click', function (e) {
            if (e.target === modal) window.closeProfileEditModal();
        });
    }
}

document.addEventListener('DOMContentLoaded', bindProfileModalControls);

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') window.closeProfileEditModal();
});
