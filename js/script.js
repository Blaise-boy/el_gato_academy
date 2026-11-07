document.addEventListener('DOMContentLoaded', function () {

    // Toggle the search bar under the header
    const searchToggle = document.getElementById('searchToggle');
    const searchBar = document.getElementById('siteSearchBar');

    if (searchToggle && searchBar) {
        searchToggle.addEventListener('click', function () {
            searchBar.classList.toggle('open');
            if (searchBar.classList.contains('open')) {
                const input = searchBar.querySelector('input');
                if (input) input.focus();
            }
        });
    }

    // Toggle mobile navigation
    const mobileToggle = document.getElementById('mobileToggle');
    const mainNav = document.getElementById('mainNav');

    if (mobileToggle && mainNav) {
        mobileToggle.addEventListener('click', function () {
            mainNav.classList.toggle('open');
        });
    }

    // Logged-in student dropdown in the header
    const navUserBtn = document.getElementById('navUserBtn');
    const navUser = document.getElementById('navUser');

    if (navUserBtn && navUser) {
        navUserBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            const isOpen = navUser.classList.toggle('open');
            navUserBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        document.addEventListener('click', function (e) {
            if (!navUser.contains(e.target)) {
                navUser.classList.remove('open');
                navUserBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }

});
