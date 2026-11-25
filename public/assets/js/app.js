const App = {
  base: document.body.dataset.basePath.replace(/\/$/, ''),
  message: 'İşlem tamamlanamadı, tekrar deneyin.',
  async request(path, options = {}) {
    const response = await fetch(this.base + path, {
      ...options,
      headers: { 'Content-Type': 'application/json',
        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content },
    });
    const body = response.status === 204 ? null : await response.json();
    if (!response.ok) throw body || {};
    return body?.data;
  },
  error(input, message) {
    const id = input.getAttribute('aria-describedby');
    const element = (id && document.getElementById(id)) || input.nextElementSibling;
    if (element?.classList.contains('error')) element.textContent = message;
  },
  validate(form) {
    let valid = true;
    form.querySelectorAll('input, select, textarea').forEach(input => {
      if (!input.name || input.type === 'hidden') return;
      let message = '';
      if (input.required && !input.value.trim()) message = 'Bu alan zorunludur.';
      else if (input.type === 'email' && input.validity.typeMismatch) message = 'Geçerli bir e-posta girin.';
      else if (input.name === 'password' && input.minLength === 8 && input.value && input.value.length < 8) {
        message = 'Parola en az 8 karakter olmalıdır.';
      } else if (input.validity.rangeUnderflow || input.validity.rangeOverflow || input.validity.tooLong) {
        message = 'Geçerli bir değer girin.';
      }
      if (input.name === 'actual_minutes' && form.elements.status?.value === 'completed' && !input.value) {
        message = 'Gerçekleşen süre 1 ile 600 dakika arasında olmalıdır.';
      }
      this.error(input, message);
      if (message) valid = false;
    });
    return valid;
  },
  async options(select, path, prompt) {
    select.disabled = true;
    select.replaceChildren(new Option('Yükleniyor…', ''));
    try {
      const data = await this.request(path);
      select.replaceChildren(new Option(prompt, ''));
      data.forEach(item => select.add(new Option(item.name, item.id)));
      select.disabled = false;
    } catch {
      select.replaceChildren(new Option('Yüklenemedi, tekrar deneyin.', ''));
      select.disabled = false;
    }
  },
  selects() {
    const type = document.querySelector('[data-exam-type]');
    const subject = document.querySelector('[data-topic-select]');
    if (!type || !subject) return;
    const topic = document.getElementById(subject.dataset.topicSelect);
    type.addEventListener('change', () => {
      topic.replaceChildren(new Option('Önce ders seçin', ''));
      this.options(subject, `/api/subjects?tur=${encodeURIComponent(type.value)}`, 'Ders seçin');
    });
    subject.addEventListener('change', () => {
      if (!subject.value) return topic.replaceChildren(new Option('Önce ders seçin', ''));
      this.options(topic, `/api/konular?ders=${encodeURIComponent(subject.value)}`, 'Konu seçin');
    });
  },
  async status(form, card) {
    const message = card.querySelector('[data-message]');
    const payload = Object.fromEntries(new FormData(form));
    try {
      const plan = await this.request(`/api/plans/${card.dataset.planId}/status`, {
        method: 'PATCH', body: JSON.stringify(payload),
      });
      const badge = card.querySelector('[data-badge]');
      badge.className = `badge badge-${plan.status}`;
      badge.textContent = { planned: 'Planlandı', in_progress: 'Devam ediyor', completed: 'Tamamlandı' }[plan.status];
      const minutes = plan.actual_minutes;
      card.querySelector('[data-actual]').textContent = minutes === null ? '—' :
        [Math.floor(minutes / 60) ? `${Math.floor(minutes / 60)} sa` : '', minutes % 60 ? `${minutes % 60} dk` : '']
          .filter(Boolean).join(' ');
      message.textContent = 'Çalışma durumu güncellendi.';
    } catch (error) {
      Object.entries(error.errors || {}).forEach(([name, text]) => {
        if (form.elements[name]) this.error(form.elements[name], text);
      });
      message.textContent = error.errors ? 'Alanları kontrol edin.' : this.message;
    }
  },
  init() {
    this.selects();
    document.querySelectorAll('form[method="post"]').forEach(form => {
      form.noValidate = true;
      form.addEventListener('submit', async event => {
        if (event.defaultPrevented) return;
        if (!this.validate(form)) { event.preventDefault(); return; }
        const card = form.closest('[data-plan-id]');
        if (!card || (!form.hasAttribute('data-status-form') && !form.hasAttribute('data-delete-plan'))) return;
        event.preventDefault();
        if (form.hasAttribute('data-status-form')) return this.status(form, card);
        try {
          await this.request(`/api/plans/${card.dataset.planId}`, { method: 'DELETE' });
          const notice = document.createElement('p');
          notice.setAttribute('aria-live', 'polite');
          notice.textContent = 'Plan silindi.';
          card.replaceWith(notice);
        } catch { card.querySelector('[data-message]').textContent = this.message; }
      });
    });
  },
};
App.init();
