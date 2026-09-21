(() => {
  const servicePrefixes = ['service_icon', 'service_title', 'service_details', 'service_limit', 'service_unlimited'];

  const newsPrefixes = [
    'news_id', 'news_featured', 'news_tag', 'news_date', 'news_date_iso',
    'news_title', 'news_excerpt', 'news_body', 'news_images'
  ];

  function syncServiceLimitFields(card) {
    const unlimited = card.querySelector('.service-unlimited-input');
    const limitInput = card.querySelector('.service-limit-input');
    if (!unlimited || !limitInput) return;
    if (unlimited.checked) {
      if (!limitInput.disabled) {
        limitInput.dataset.lastLimit = limitInput.value || '10';
      }
      limitInput.disabled = true;
      limitInput.required = false;
    } else {
      limitInput.disabled = false;
      limitInput.required = true;
      if (!limitInput.value || parseInt(limitInput.value, 10) < 1) {
        limitInput.value = limitInput.dataset.lastLimit || '10';
      }
    }
  }

  function reindexNames(card, i, prefixes) {
    card.dataset.index = String(i);
    card.querySelectorAll('[name]').forEach((el) => {
      const name = el.getAttribute('name') || '';
      if (/^news_files_\d+\[\]$/.test(name)) {
        el.setAttribute('name', `news_files_${i}[]`);
        return;
      }
      if (/^service_days_\d+\[\]$/.test(name)) {
        el.setAttribute('name', `service_days_${i}[]`);
        return;
      }
      prefixes.forEach((prefix) => {
        const re = new RegExp(`^${prefix}_\\d+$`);
        if (re.test(name)) {
          el.setAttribute('name', `${prefix}_${i}`);
        }
      });
    });
  }

  function reindexServices(container, countInput) {
    const dayNames = {
      mon: 'Segunda-feira', tue: 'Terça-feira', wed: 'Quarta-feira',
      thu: 'Quinta-feira', fri: 'Sexta-feira', sat: 'Sábado', sun: 'Domingo'
    };
    const cards = [...container.querySelectorAll('.service-card')];
    cards.forEach((card, i) => {
      reindexNames(card, i, servicePrefixes);
      syncServiceLimitFields(card);
      const num = card.querySelector('.service-num');
      if (num) num.textContent = String(i + 1);
      const preview = card.querySelector('.service-icon-preview');
      const iconInput = card.querySelector('.service-icon-input');
      if (preview && iconInput && !(iconInput.value || '').trim()) {
        preview.textContent = String(i + 1);
      }
      const meta = card.querySelector('.list-meta');
      if (meta) {
        const checked = [...card.querySelectorAll(`input[name="service_days_${i}[]"]:checked`)]
          .map((el) => dayNames[el.value] || el.value);
        const unlimited = card.querySelector('.service-unlimited-input');
        const limitInput = card.querySelector('.service-limit-input');
        let limit = parseInt((limitInput && limitInput.value) || '10', 10);
        if (Number.isNaN(limit) || limit < 1) limit = 10;
        const noLimit = !!(unlimited && unlimited.checked);
        let daysLabel = 'Sem dias definidos';
        if (checked.length === 1) {
          daysLabel = checked[0];
        } else if (checked.length > 1) {
          const last = checked.pop();
          daysLabel = `${checked.join(', ')} e ${last}`;
        }
        meta.textContent = noLimit
          ? `${daysLabel} · sem limite`
          : `${daysLabel} · ${limit} / dia`;
      }
      card.querySelectorAll('.days-check-grid input[type="checkbox"]').forEach((cb) => {
        const key = cb.value;
        cb.id = `service_day_${i}_${key}`;
        const label = cb.closest('label');
        if (label) label.setAttribute('for', cb.id);
      });
      const unlimitedCb = card.querySelector('.service-unlimited-input');
      if (unlimitedCb) {
        unlimitedCb.id = `service_unlimited_${i}`;
        const label = unlimitedCb.closest('label');
        if (label) label.setAttribute('for', unlimitedCb.id);
      }
    });
    countInput.value = String(cards.length);
  }

  function syncNewsGalleryInput(card) {
    const hidden = card.querySelector('.news-images-input');
    if (!hidden) return;
    const paths = [...card.querySelectorAll('.news-gallery-item[data-path]')]
      .map((el) => el.getAttribute('data-path') || '')
      .filter(Boolean);
    hidden.value = paths.join('\n');

    const thumb = card.querySelector('.list-thumb');
    const thumbImg = card.querySelector('.list-thumb-img');
    const thumbEmpty = card.querySelector('.list-thumb-empty');
    const meta = card.querySelector('.list-meta');
    const tagInput = card.querySelector('[name^="news_tag_"]');
    const tag = (tagInput && tagInput.value.trim()) || '';
    const countLabel = `${paths.length} imagem${paths.length === 1 ? '' : 'ns'}`;

    if (meta) {
      meta.textContent = tag ? `${tag} · ${countLabel}` : countLabel;
    }

    if (paths[0] && thumbImg) {
      thumbImg.src = '../' + paths[0];
      thumbImg.hidden = false;
      if (thumb) thumb.classList.remove('is-empty');
      if (thumbEmpty) thumbEmpty.hidden = true;
    } else if (thumbImg && !card.querySelector('.news-gallery-item.is-pending')) {
      thumbImg.removeAttribute('src');
      thumbImg.hidden = true;
      if (thumb) thumb.classList.add('is-empty');
      if (thumbEmpty) {
        thumbEmpty.hidden = false;
        thumbEmpty.textContent = '—';
      }
    }
  }

  function reindexNews(container, countInput) {
    const cards = [...container.querySelectorAll('.news-card')];
    cards.forEach((card, i) => {
      reindexNames(card, i, newsPrefixes);
      const num = card.querySelector('.news-num');
      if (num) num.textContent = String(i + 1);
      const badge = card.querySelector('.badge-feature');
      const featured = card.querySelector('.news-featured-input');
      if (featured && featured.checked) {
        if (!badge) {
          const label = card.querySelector('.list-label');
          if (label) {
            const span = document.createElement('span');
            span.className = 'badge-feature';
            span.textContent = 'Destaque';
            label.appendChild(span);
          }
        }
      } else if (badge) {
        badge.remove();
      }
      syncNewsGalleryInput(card);
    });
    countInput.value = String(cards.length);
  }

  function syncTitle(card, inputSelector, titleSelector) {
    const input = card.querySelector(inputSelector);
    const label = card.querySelector(titleSelector);
    if (!input || !label) return;
    const value = (input.value || '').trim();
    label.textContent = value || 'Sem título';
  }

  function setOpen(card, open, editBtnSelector) {
    const editor = card.querySelector('.list-editor');
    const editBtn = card.querySelector(editBtnSelector);
    if (!editor) return;
    editor.hidden = !open;
    card.classList.toggle('is-open', open);
    if (editBtn) editBtn.textContent = open ? 'Fechar' : 'Editar';
  }

  async function copyText(text, el) {
    try {
      await navigator.clipboard.writeText(text);
      if (el) {
        el.classList.add('copied');
        setTimeout(() => el.classList.remove('copied'), 1200);
      }
    } catch (_) {
      prompt('Copiar caminho:', text);
    }
  }

  const servicesWrap = document.getElementById('servicesWrap');
  const serviceCount = document.getElementById('service_count');
  const addServiceBtn = document.getElementById('addServiceBtn');
  const serviceTemplate = document.getElementById('serviceTemplate');

  if (servicesWrap && addServiceBtn && serviceTemplate && serviceCount) {
    servicesWrap.addEventListener('click', (e) => {
      const card = e.target.closest('.service-card');
      if (!card) return;

      if (e.target.closest('.btn-edit-service')) {
        const editor = card.querySelector('.list-editor');
        setOpen(card, !!(editor && editor.hidden), '.btn-edit-service');
        return;
      }
      if (e.target.closest('.btn-close-service')) {
        setOpen(card, false, '.btn-edit-service');
        syncTitle(card, '.service-title-input', '.list-title');
        return;
      }
      if (e.target.closest('.btn-remove-service')) {
        if (servicesWrap.querySelectorAll('.service-card').length <= 1) {
          alert('Tem de manter pelo menos um serviço.');
          return;
        }
        if (!confirm('Remover este serviço?')) return;
        card.remove();
        reindexServices(servicesWrap, serviceCount);
        return;
      }
      if (e.target.closest('.btn-move-up-service')) {
        const prev = card.previousElementSibling;
        if (prev) {
          servicesWrap.insertBefore(card, prev);
          reindexServices(servicesWrap, serviceCount);
        }
        return;
      }
      if (e.target.closest('.btn-move-down-service')) {
        const next = card.nextElementSibling;
        if (next) {
          servicesWrap.insertBefore(next, card);
          reindexServices(servicesWrap, serviceCount);
        }
      }
    });

    servicesWrap.addEventListener('input', (e) => {
      const card = e.target.closest('.service-card');
      if (!card) return;
      if (e.target.classList.contains('service-title-input')) {
        syncTitle(card, '.service-title-input', '.list-title');
      }
      if (e.target.classList.contains('service-icon-input')) {
        const preview = card.querySelector('.service-icon-preview');
        const value = e.target.value.trim();
        if (preview) {
          preview.textContent = value || (card.dataset.index ? String(Number(card.dataset.index) + 1) : '1');
        }
      }
      if (e.target.classList.contains('service-limit-input') || e.target.matches('input[type="checkbox"]')) {
        reindexServices(servicesWrap, serviceCount);
      }
    });

    servicesWrap.addEventListener('change', (e) => {
      const card = e.target.closest('.service-card');
      if (e.target.classList.contains('service-unlimited-input') && card) {
        syncServiceLimitFields(card);
        reindexServices(servicesWrap, serviceCount);
        return;
      }
      if (e.target.matches('input[name^="service_days_"]')) {
        reindexServices(servicesWrap, serviceCount);
      }
    });

    addServiceBtn.addEventListener('click', () => {
      const i = servicesWrap.querySelectorAll('.service-card').length;
      const html = serviceTemplate.innerHTML
        .replaceAll('__I__', String(i))
        .replaceAll('__N__', String(i + 1));
      servicesWrap.insertAdjacentHTML('beforeend', html);
      serviceCount.value = String(i + 1);
      const newCard = servicesWrap.querySelector('.service-card:last-child');
      if (newCard) {
        syncServiceLimitFields(newCard);
        setOpen(newCard, true, '.btn-edit-service');
        newCard.querySelector('.service-title-input')?.focus();
      }
    });

    // Estado inicial dos campos de limite
    servicesWrap.querySelectorAll('.service-card').forEach((card) => syncServiceLimitFields(card));
  }

  const newsWrap = document.getElementById('newsWrap');
  const newsCount = document.getElementById('news_count');
  const addNewsBtn = document.getElementById('addNewsBtn');
  const newsTemplate = document.getElementById('newsTemplate');

  if (newsWrap && addNewsBtn && newsTemplate && newsCount) {
    newsWrap.addEventListener('click', (e) => {
      const card = e.target.closest('.news-card');
      if (!card) return;

      if (e.target.closest('.btn-gallery-remove')) {
        const item = e.target.closest('.news-gallery-item');
        if (item) {
          item.remove();
          syncNewsGalleryInput(card);
        }
        return;
      }

      if (e.target.closest('.btn-edit-news')) {
        const editor = card.querySelector('.list-editor');
        setOpen(card, !!(editor && editor.hidden), '.btn-edit-news');
        return;
      }
      if (e.target.closest('.btn-close-news')) {
        setOpen(card, false, '.btn-edit-news');
        syncTitle(card, '.news-title-input', '.list-title');
        return;
      }
      if (e.target.closest('.btn-remove-news')) {
        if (newsWrap.querySelectorAll('.news-card').length <= 1) {
          alert('Tem de manter pelo menos uma notícia.');
          return;
        }
        if (!confirm('Remover esta notícia?')) return;
        card.remove();
        reindexNews(newsWrap, newsCount);
        return;
      }
      if (e.target.closest('.btn-move-up-news')) {
        const prev = card.previousElementSibling;
        if (prev) {
          newsWrap.insertBefore(card, prev);
          reindexNews(newsWrap, newsCount);
        }
        return;
      }
      if (e.target.closest('.btn-move-down-news')) {
        const next = card.nextElementSibling;
        if (next) {
          newsWrap.insertBefore(next, card);
          reindexNews(newsWrap, newsCount);
        }
      }
    });

    newsWrap.addEventListener('input', (e) => {
      const card = e.target.closest('.news-card');
      if (!card) return;
      if (e.target.classList.contains('news-title-input')) {
        syncTitle(card, '.news-title-input', '.list-title');
      }
      if (e.target.name && e.target.name.startsWith('news_tag_')) {
        syncNewsGalleryInput(card);
      }
    });

    newsWrap.addEventListener('change', (e) => {
      if (e.target.classList.contains('news-featured-input')) {
        reindexNews(newsWrap, newsCount);
        return;
      }

      const input = e.target.closest('.news-files-input');
      if (!input || !input.files || !input.files.length) return;
      const card = input.closest('.news-card');
      if (!card) return;

      const pending = card.querySelector('.news-gallery-pending');
      const grid = card.querySelector('.news-gallery-grid');
      if (pending) {
        pending.hidden = false;
        pending.innerHTML = `<p class="hint">${input.files.length} nova(s) imagem(ns) serão adicionadas ao guardar.</p>`;
      }

      [...input.files].forEach((file) => {
        if (!file.type.startsWith('image/') || !grid) return;
        const url = URL.createObjectURL(file);
        const div = document.createElement('div');
        div.className = 'news-gallery-item is-pending';
        div.innerHTML = `<img src="${url}" alt=""><span class="pending-badge">Nova</span>`;
        grid.appendChild(div);
      });

      const thumbImg = card.querySelector('.list-thumb-img');
      const thumb = card.querySelector('.list-thumb');
      const thumbEmpty = card.querySelector('.list-thumb-empty');
      if (input.files[0] && thumbImg) {
        const firstExisting = card.querySelector('.news-gallery-item[data-path] img');
        if (!firstExisting) {
          thumbImg.src = URL.createObjectURL(input.files[0]);
          thumbImg.hidden = false;
          if (thumb) thumb.classList.remove('is-empty');
          if (thumbEmpty) thumbEmpty.hidden = true;
        }
      }
    });

    addNewsBtn.addEventListener('click', () => {
      const i = newsWrap.querySelectorAll('.news-card').length;
      const html = newsTemplate.innerHTML
        .replaceAll('__I__', String(i))
        .replaceAll('__N__', String(i + 1));
      newsWrap.insertAdjacentHTML('beforeend', html);
      newsCount.value = String(i + 1);
      const newCard = newsWrap.querySelector('.news-card:last-child');
      if (newCard) {
        setOpen(newCard, true, '.btn-edit-news');
        newCard.querySelector('.news-title-input')?.focus();
      }
    });
  }

  const seoWrap = document.getElementById('seoWrap');
  if (seoWrap) {
    seoWrap.addEventListener('click', (e) => {
      const card = e.target.closest('.seo-card');
      if (!card) return;
      if (e.target.closest('.btn-edit-seo')) {
        const editor = card.querySelector('.list-editor');
        setOpen(card, !!(editor && editor.hidden), '.btn-edit-seo');
        return;
      }
      if (e.target.closest('.btn-close-seo')) {
        setOpen(card, false, '.btn-edit-seo');
      }
    });
  }

  document.querySelectorAll('.path-copy').forEach((el) => {
    el.addEventListener('click', () => copyText(el.textContent || '', el));
  });

  document.querySelectorAll('.path-copy-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
      copyText(btn.getAttribute('data-path') || '', btn);
      const original = btn.textContent;
      btn.textContent = 'Copiado';
      setTimeout(() => { btn.textContent = original; }, 1200);
    });
  });
})();
