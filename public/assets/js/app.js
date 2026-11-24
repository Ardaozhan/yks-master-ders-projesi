document.querySelectorAll('form[method="post"]').forEach(form => {
  form.addEventListener('submit', event => {
    if (event.defaultPrevented || !form.reportValidity()) {
      event.preventDefault();
      return;
    }
    form.querySelectorAll('button:not([type]), button[type="submit"], input[type="submit"]').forEach(button => {
      button.disabled = true;
    });
  });
});

const menuButton = document.querySelector('[data-menu-toggle]');
if (menuButton) {
  menuButton.addEventListener('click', () => {
    const menu = document.getElementById(menuButton.getAttribute('aria-controls'));
    if (!menu) return;
    const expanded = menuButton.getAttribute('aria-expanded') === 'true';
    menuButton.setAttribute('aria-expanded', String(!expanded));
    menu.hidden = expanded;
  });
}
