document.querySelectorAll('form[method="post"]').forEach(form => {
  form.addEventListener('submit', () => {
    form.querySelectorAll('button[type="submit"]').forEach(button => { button.disabled = true; });
  });
});
document.querySelectorAll('[data-topic-select]').forEach(subject => {
  const topic = document.getElementById(subject.dataset.topicSelect);
  if (!topic) return;
  subject.addEventListener('change', async () => {
    topic.replaceChildren(new Option('Yükleniyor…', ''));
    topic.disabled = true;
    if (!subject.value) { topic.replaceChildren(new Option('Önce ders seçin', '')); return; }
    try {
      const base = document.body.dataset.basePath.replace(/\/$/, '');
      const response = await fetch(`${base}/api/konular?ders=${encodeURIComponent(subject.value)}`);
      if (!response.ok) throw new Error('Request failed');
      const result = await response.json();
      topic.replaceChildren(new Option('Konu seçin', ''));
      result.data.forEach(item => topic.add(new Option(item.name, item.id)));
      topic.disabled = false;
    } catch { topic.replaceChildren(new Option('Konular yüklenemedi', '')); }
  });
});
