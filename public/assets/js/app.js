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


const type = document.querySelector('[data-exam-type]');
const subject = document.querySelector('[data-topic-select]');
if (type && subject) {
  const topic = document.getElementById(subject.dataset.topicSelect);
  const base = document.body.dataset.basePath.replace(/\/$/, '');
  const load = async (select, path, prompt) => {
    select.disabled = true;
    select.replaceChildren(new Option('Yükleniyor…', ''));
    try {
      const response = await fetch(base + path);
      if (!response.ok) throw new Error();
      const body = await response.json();
      select.replaceChildren(new Option(prompt, ''));
      body.data.forEach(item => select.add(new Option(item.name, item.id)));
    } catch {
      select.replaceChildren(new Option('Yüklenemedi, tekrar deneyin.', ''));
    }
    select.disabled = false;
  };
  type.addEventListener('change', () => {
    topic.replaceChildren(new Option('Önce ders seçin', ''));
    load(subject, `/api/subjects?tur=${encodeURIComponent(type.value)}`, 'Ders seçin');
  });
  subject.addEventListener('change', () => {
    if (!subject.value) return topic.replaceChildren(new Option('Önce ders seçin', ''));
    load(topic, `/api/konular?ders=${encodeURIComponent(subject.value)}`, 'Konu seçin');
  });
}
