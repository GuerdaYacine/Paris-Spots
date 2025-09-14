document.addEventListener('turbo:load', () => {
    const nav = document.querySelector('.header-nav');
    const menuBtn = document.querySelector('.header-menu-button');

    menuBtn.onclick = () => {
        nav.classList.toggle('open');
        menuBtn.classList.toggle('opened');
        menuBtn.setAttribute('aria-expanded', menuBtn.classList.contains('opened'));
    };
});
