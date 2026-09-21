/* ============================================
   SCROLL SUAVE PARA NAVEGAÇÃO
   ============================================ */
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const targetId = this.getAttribute('href');
        if (!targetId || targetId === '#') return;

        const targetElement = document.querySelector(targetId);
        if (!targetElement) return;

        e.preventDefault();
        const header = document.querySelector('header');
        const headerHeight = header ? header.offsetHeight : 0;
        const targetPosition = targetElement.offsetTop - headerHeight;

        window.scrollTo({
            top: targetPosition,
            behavior: 'smooth'
        });
    });
});

/* ============================================
   FORMULÁRIO DE CONTATO
   ============================================ */
document.getElementById('formAgendamento').addEventListener('submit', async function (e) {
    e.preventDefault();

    const form = this;
    const nome = document.getElementById('nome').value.trim();
    const email = document.getElementById('email').value.trim();
    const telefone = document.getElementById('telefone').value.trim();
    const assunto = document.getElementById('assunto').value;
    const servicoEl = document.getElementById('servico');
    const servico = servicoEl ? servicoEl.value.trim() : '';
    const diaEl = document.getElementById('dia');
    const dia = diaEl ? diaEl.value.trim() : '';
    const mensagem = document.getElementById('mensagem').value.trim();
    const website = document.getElementById('website') ? document.getElementById('website').value.trim() : '';
    const privacidade = document.getElementById('privacidade') ? document.getElementById('privacidade').checked : false;
    const btn = form.querySelector('button[type="submit"]');
    const originalText = btn.textContent;

    if (!nome || !telefone || !assunto || !mensagem) {
        alert('Por favor, preencha todos os campos obrigatórios.');
        return;
    }

    if (assunto === 'Agendamento de Consulta' && !servico) {
        alert('Por favor, seleccione o serviço pretendido para a consulta.');
        if (servicoEl) servicoEl.focus();
        return;
    }

    if (assunto === 'Agendamento de Consulta' && !dia) {
        alert('Por favor, seleccione no calendário o dia pretendido para a consulta.');
        const cal = document.getElementById('bookingCalendar');
        if (cal) cal.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }

    if (!privacidade) {
        alert('É necessário aceitar a política de privacidade para enviar a mensagem.');
        return;
    }

    btn.textContent = 'Enviando...';
    btn.disabled = true;

    try {
        const response = await fetch('enviar-email.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ nome, email, telefone, assunto, servico, dia, mensagem, website, privacidade }),
        });

        const result = await response.json();

        if (response.ok && result.success) {
            alert(`Mensagem enviada com sucesso, ${nome}! Entraremos em contacto em breve.`);
            form.reset();
            const assuntoSelect = document.getElementById('assunto');
            if (assuntoSelect) assuntoSelect.dispatchEvent(new Event('change'));
        } else {
            throw new Error(result.message || 'Ocorreu um erro ao enviar a mensagem.');
        }
    } catch (error) {
        console.error('Erro no envio do formulário:', error);
        alert(error.message || 'Não foi possível enviar sua mensagem. Por favor, tente novamente mais tarde.');
    } finally {
        btn.textContent = originalText;
        btn.disabled = false;
    }
});

/* ============================================
   MASCARA DE TELEFONE (padrão Guiné-Bissau +245, 7 dígitos)
   ============================================ */
document.getElementById('telefone').addEventListener('input', function (e) {
    let value = e.target.value.replace(/\D/g, ''); // Remove tudo que não for dígito
    if (value.length > 7) value = value.slice(0, 7); // Limita a 7 dígitos
    
    // Formata o valor: +245 9X XXX XX
    if (value.length > 5) {
        value = `+245 ${value.slice(0, 2)} ${value.slice(2, 5)} ${value.slice(5, 7)}`;
    } else if (value.length > 2) {
        value = `+245 ${value.slice(0, 2)} ${value.slice(2)}`;
    } else if (value.length > 0) {
        value = `+245 ${value.slice(0, 2)}`;
    }
    e.target.value = value;
});

/* ============================================
   ANIMAÇÕES AO ROLAR (INTERSECTION OBSERVER)
   ============================================ */
function initScrollAnimations() {
    // Aplica atraso para o efeito "stagger" nos grids
    const staggerGrids = document.querySelectorAll('#servicos .row, #depoimentos .row, #noticias .row');

    staggerGrids.forEach(grid => {
        const items = grid.querySelectorAll('.fade-up');
        items.forEach((item, index) => {
            // Define uma variável CSS para o atraso da transição em cada item
            item.style.setProperty('--stagger-delay', `${index * 100}ms`);
        });
    });

    const elementsToAnimate = document.querySelectorAll('.fade-up');

    // Se o navegador não suportar a API, simplesmente mostra os elementos.
    if (!('IntersectionObserver' in window)) {
        elementsToAnimate.forEach(el => el.classList.add('visible'));
        return;
    }

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            // Quando o elemento entra na tela...
            if (entry.isIntersecting) {
                entry.target.classList.add('visible'); // Adiciona a classe que dispara a animação
                observer.unobserve(entry.target); // Para de observar, para animar apenas uma vez
            }
        });
    }, { threshold: 0.1 }); // A animação começa quando 10% do elemento está visível

    elementsToAnimate.forEach(el => observer.observe(el));
}

/* ============================================
   BOTÃO "VOLTAR AO TOPO"
   ============================================ */
function initBackToTopButton() {
    const backToTopBtn = document.getElementById('backToTopBtn');

    if (!backToTopBtn) return;

    const handleScroll = () => {
        // Mostra o botão após rolar 400px para baixo
        if (window.scrollY > 400) {
            backToTopBtn.classList.add('show');
        } else {
            backToTopBtn.classList.remove('show');
        }
    };

    const scrollToTop = () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    window.addEventListener('scroll', handleScroll);
    backToTopBtn.addEventListener('click', scrollToTop);
}

document.addEventListener('DOMContentLoaded', () => {
    initScrollAnimations();
    initBackToTopButton();
    initAboutGallery();
    initHeaderScroll();
    initServicesCircle();
    initAssuntoLinks();
    initServicoField();
    initNewsModal();
});

/* ============================================
   CAMPO DE SERVIÇO + CALENDÁRIO NO AGENDAMENTO
   ============================================ */
function initServicoField() {
    const assuntoSelect = document.getElementById('assunto');
    const wrap = document.getElementById('servicoFieldWrap');
    const servicoSelect = document.getElementById('servico');
    const diaWrap = document.getElementById('diaFieldWrap');
    const diaInput = document.getElementById('dia');
    const calGrid = document.getElementById('calGrid');
    const calMonthTitle = document.getElementById('calMonthTitle');
    const calHint = document.getElementById('calHint');
    const calSelected = document.getElementById('calSelected');
    const calPrev = document.getElementById('calPrev');
    const calNext = document.getElementById('calNext');
    if (!assuntoSelect || !wrap || !servicoSelect || !diaWrap || !diaInput || !calGrid) return;

    const services = Array.isArray(window.SITE_SERVICES) ? window.SITE_SERVICES : [];
    const dayKeyToJs = { sun: 0, mon: 1, tue: 2, wed: 3, thu: 4, fri: 5, sat: 6 };
    const monthNames = [
        'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
        'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
    ];
    const weekdayNames = [
        'Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira',
        'Quinta-feira', 'Sexta-feira', 'Sábado'
    ];

    let viewYear;
    let viewMonth;
    let allowedJsDays = [];
    let currentService = null;
    let occupancy = {};
    let fetchToken = 0;

    function startOfToday() {
        const d = new Date();
        d.setHours(0, 0, 0, 0);
        return d;
    }

    function pad(n) {
        return String(n).padStart(2, '0');
    }

    function toIso(date) {
        return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
    }

    function formatSelected(date) {
        return `${weekdayNames[date.getDay()]}, ${pad(date.getDate())} de ${monthNames[date.getMonth()]} de ${date.getFullYear()}`;
    }

    function clearSelection() {
        diaInput.value = '';
        if (calSelected) {
            calSelected.hidden = true;
            calSelected.textContent = '';
        }
    }

    function remainingFor(iso) {
        if (occupancy[iso]) {
            if (occupancy[iso].unlimited || occupancy[iso].remaining === null) {
                return null;
            }
            if (typeof occupancy[iso].remaining === 'number') {
                return occupancy[iso].remaining;
            }
        }
        if (!currentService) return 0;
        const limit = Number(currentService.daily_limit);
        if (!limit) return null;
        return limit;
    }

    function isUnlimited() {
        if (!currentService) return false;
        return !Number(currentService.daily_limit);
    }

    async function loadOccupancy() {
        if (!currentService || !currentService.title) {
            occupancy = {};
            return;
        }
        if (isUnlimited()) {
            occupancy = {};
            renderCalendar();
            return;
        }
        const token = ++fetchToken;
        try {
            const url = `disponibilidade.php?servico=${encodeURIComponent(currentService.title)}&year=${viewYear}&month=${viewMonth + 1}`;
            const res = await fetch(url, { headers: { Accept: 'application/json' } });
            const data = await res.json();
            if (token !== fetchToken) return;
            occupancy = (data && data.success && data.days) ? data.days : {};
        } catch (_) {
            if (token !== fetchToken) return;
            occupancy = {};
        }
        renderCalendar();
    }

    function setAllowedDaysFromService(title) {
        currentService = services.find((s) => s.title === title) || null;
        const keys = currentService && Array.isArray(currentService.days) ? currentService.days : [];
        allowedJsDays = keys
            .map((k) => dayKeyToJs[k])
            .filter((n) => typeof n === 'number');

        const now = startOfToday();
        viewYear = now.getFullYear();
        viewMonth = now.getMonth();
        clearSelection();
        occupancy = {};

        if (!title) {
            if (calHint) calHint.textContent = 'Seleccione primeiro o serviço para ver os dias disponíveis.';
            renderCalendar();
            return;
        }
        if (!allowedJsDays.length) {
            if (calHint) calHint.textContent = 'Este serviço não tem dias disponíveis.';
            renderCalendar();
            return;
        }
        const limit = Number(currentService.daily_limit) || 0;
        const labels = currentService.day_labels || [];
        if (calHint) {
            const daysPart = labels.length ? `Dias: ${labels.join(', ')}. ` : '';
            const limitPart = limit === 0
                ? 'Sem limite diário de atendimentos.'
                : `Limite: ${limit} atendimentos/dia.`;
            calHint.textContent = `${daysPart}${limitPart} Clique numa data disponível.`;
        }
        renderCalendar();
        loadOccupancy();
    }

    function renderCalendar() {
        calGrid.innerHTML = '';
        if (calMonthTitle) {
            calMonthTitle.textContent = `${monthNames[viewMonth]} ${viewYear}`;
        }

        const first = new Date(viewYear, viewMonth, 1);
        const mondayIndex = (first.getDay() + 6) % 7;
        const daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();
        const totalCells = Math.ceil((mondayIndex + daysInMonth) / 7) * 7;

        for (let cell = 0; cell < totalCells; cell++) {
            const dayNum = cell - mondayIndex + 1;
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'cal-day';

            if (dayNum < 1 || dayNum > daysInMonth) {
                btn.classList.add('is-empty');
                btn.disabled = true;
                btn.textContent = '';
                calGrid.appendChild(btn);
                continue;
            }

            const date = new Date(viewYear, viewMonth, dayNum);
            date.setHours(0, 0, 0, 0);
            const iso = toIso(date);
            const isPast = date < startOfToday();
            const isAllowed = allowedJsDays.includes(date.getDay());
            const remaining = remainingFor(iso);
            const unlimited = remaining === null;
            const isFull = isAllowed && !isPast && !unlimited && remaining <= 0;
            const isSelected = diaInput.value === iso;

            btn.textContent = String(dayNum);
            btn.dataset.date = iso;

            if (isPast || !isAllowed || !allowedJsDays.length || isFull) {
                btn.disabled = true;
                btn.classList.add('is-disabled');
                if (isFull) {
                    btn.classList.add('is-full');
                    btn.title = 'Lotado — limite diário atingido';
                }
            } else {
                btn.classList.add('is-available');
                if (unlimited) {
                    btn.title = 'Disponível';
                } else {
                    btn.title = remaining === 1
                        ? '1 vaga restante'
                        : `${remaining} vagas restantes`;
                }
                btn.addEventListener('click', () => {
                    diaInput.value = iso;
                    calGrid.querySelectorAll('.cal-day.is-selected').forEach((el) => el.classList.remove('is-selected'));
                    btn.classList.add('is-selected');
                    if (calSelected) {
                        calSelected.hidden = false;
                        const vagas = remainingFor(iso);
                        if (vagas === null) {
                            calSelected.textContent = `Seleccionado: ${formatSelected(date)}`;
                        } else {
                            calSelected.textContent = `Seleccionado: ${formatSelected(date)} (${vagas} vaga${vagas === 1 ? '' : 's'})`;
                        }
                    }
                });
            }

            if (date.getTime() === startOfToday().getTime()) {
                btn.classList.add('is-today');
            }
            if (isSelected) {
                btn.classList.add('is-selected');
            }

            calGrid.appendChild(btn);
        }
    }

    function syncVisibility() {
        const isBooking = assuntoSelect.value === 'Agendamento de Consulta';
        wrap.hidden = !isBooking;
        servicoSelect.required = isBooking;
        diaWrap.hidden = !isBooking;
        diaInput.required = isBooking;

        if (!isBooking) {
            servicoSelect.value = '';
            setAllowedDaysFromService('');
            return;
        }
        setAllowedDaysFromService(servicoSelect.value);
    }

    if (calPrev) {
        calPrev.addEventListener('click', () => {
            viewMonth -= 1;
            if (viewMonth < 0) {
                viewMonth = 11;
                viewYear -= 1;
            }
            if (diaInput.value) {
                const selected = new Date(diaInput.value + 'T00:00:00');
                if (selected.getFullYear() !== viewYear || selected.getMonth() !== viewMonth) {
                    clearSelection();
                }
            }
            renderCalendar();
            loadOccupancy();
        });
    }
    if (calNext) {
        calNext.addEventListener('click', () => {
            viewMonth += 1;
            if (viewMonth > 11) {
                viewMonth = 0;
                viewYear += 1;
            }
            if (diaInput.value) {
                const selected = new Date(diaInput.value + 'T00:00:00');
                if (selected.getFullYear() !== viewYear || selected.getMonth() !== viewMonth) {
                    clearSelection();
                }
            }
            renderCalendar();
            loadOccupancy();
        });
    }

    servicoSelect.addEventListener('change', () => {
        setAllowedDaysFromService(servicoSelect.value);
    });
    assuntoSelect.addEventListener('change', syncVisibility);
    syncVisibility();
}

/* ============================================
   LINKS QUE PRÉ-SELECIONAM O ASSUNTO DO FORMULÁRIO
   ============================================ */
function initAssuntoLinks() {
    const assuntoSelect = document.getElementById('assunto');
    const servicoSelect = document.getElementById('servico');
    if (!assuntoSelect) return;

    document.querySelectorAll('[data-assunto]').forEach(link => {
        link.addEventListener('click', () => {
            const value = link.getAttribute('data-assunto');
            if (value && [...assuntoSelect.options].some(opt => opt.value === value)) {
                assuntoSelect.value = value;
                assuntoSelect.dispatchEvent(new Event('change'));
            }
            const servico = link.getAttribute('data-servico');
            if (servico && servicoSelect && [...servicoSelect.options].some(opt => opt.value === servico)) {
                servicoSelect.value = servico;
                servicoSelect.dispatchEvent(new Event('change'));
            }
        });
    });
}

/* ============================================
   SERVIÇOS (CÍRCULO ROTATIVO)
   ============================================ */
function initServicesCircle() {
    const container = document.querySelector('.services-circle-container');
    if (!container) return;

    const wheel = container.querySelector('.service-wheel');
    const titleEl = document.getElementById('service-circle-title');
    const detailsEl = document.getElementById('service-circle-details');
    const contentBox = container.querySelector('.service-details-content');

    const services = Array.isArray(window.SITE_SERVICES) && window.SITE_SERVICES.length
        ? window.SITE_SERVICES
        : [
            {
                icon: '🩺',
                title: 'Tratamento Clínico',
                details: 'Diagnóstico precoce, poliquimioterapia (PQT) e acompanhamento médico contínuo para todos os estágios da hanseníase.'
            }
        ];

    const totalItems = services.length;
    const angleStep = 360 / totalItems;
    const radius = (wheel.offsetWidth / 2) * 0.85;
    let activeIndex = 0;
    let rotationInterval;

    services.forEach((service, i) => {
        const angle = i * angleStep;
        const item = document.createElement('div');
        item.className = 'service-circle-item';
        item.dataset.index = i;

        // Posiciona na roda; o ícone gira com a roda (sem correcção estática)
        item.style.transform = `rotate(${angle}deg) translate(${radius}px)`;

        const icon = (service.icon || '').trim() || String(i + 1);
        item.innerHTML = `<div class="service-icon" style="transform: rotate(${-angle}deg)">${icon}</div>`;
        wheel.appendChild(item);

        item.addEventListener('click', () => {
            setService(i);
            resetInterval();
        });
    });

    const items = wheel.querySelectorAll('.service-circle-item');

    function setService(index) {
        activeIndex = index;
        const rotationAngle = -index * angleStep;

        wheel.style.transform = `rotate(${rotationAngle}deg)`;

        items.forEach((item, i) => {
            item.classList.toggle('active', i === index);
            // Mantém o ícone legível enquanto a roda gira
            const iconEl = item.querySelector('.service-icon');
            if (iconEl) {
                const baseAngle = i * angleStep;
                iconEl.style.transform = `rotate(${-baseAngle - rotationAngle}deg)`;
            }
        });

        contentBox.classList.add('fade-out');
        setTimeout(() => {
            titleEl.textContent = services[index].title;
            detailsEl.textContent = services[index].details;
            const bookBtn = document.getElementById('serviceBookBtn');
            if (bookBtn) {
                bookBtn.setAttribute('data-servico', services[index].title);
            }
            contentBox.classList.remove('fade-out');
        }, 300);
    }

    function nextService() {
        const nextIndex = (activeIndex + 1) % totalItems;
        setService(nextIndex);
    }

    function startInterval() {
        rotationInterval = setInterval(nextService, 3500); // Velocidade de rotação (em milissegundos). Alterado de 5000 para 3500.
    }

    function resetInterval() {
        clearInterval(rotationInterval);
        startInterval();
    }

    container.addEventListener('mouseenter', () => clearInterval(rotationInterval));
    container.addEventListener('mouseleave', () => startInterval());

    setService(0);
    startInterval();
}

/* ============================================
   GALERIA DE IMAGENS (SOBRE)
   ============================================ */
function initAboutGallery() {
    const mainImage = document.getElementById('mainGalleryImage');
    const thumbnails = document.querySelectorAll('.galeria-thumbnails .thumbnail');

    if (!mainImage || thumbnails.length === 0) return;

    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', function() {
            // Atualiza a imagem principal
            mainImage.src = this.src;
            mainImage.alt = this.alt;

            // Atualiza a miniatura ativa
            thumbnails.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });
}

/* ============================================
   HEADER SCROLL EFFECT
   ============================================ */
function initHeaderScroll() {
    const header = document.querySelector('.navbar');
    if (!header) return;

    const handleHeaderScroll = () => {
        // Adiciona a classe 'scrolled' ao header após rolar 50px
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    };

    window.addEventListener('scroll', handleHeaderScroll);
    handleHeaderScroll(); // Executa uma vez no carregamento para o caso de a página recarregar no meio
}

/* ============================================
   PRELOADER
   ============================================ */
function initPreloader() {
    const preloader = document.querySelector('.preloader');
    if (!preloader) return;

    window.addEventListener('load', () => {
        preloader.classList.add('hidden');
        // Opcional: remove o preloader do DOM após a transição para não interferir
        setTimeout(() => {
            preloader.style.display = 'none';
        }, 500); // Deve corresponder à duração da transição no CSS
    });
}

initPreloader();

/* ============================================
   MODAL MODERNO DE NOTÍCIAS
   ============================================ */
const NEWS_ARTICLES = Array.isArray(window.SITE_NEWS) ? window.SITE_NEWS : [];

function initNewsModal() {
    const modalEl = document.getElementById('newsModal');
    if (!modalEl || !NEWS_ARTICLES.length) {
        return;
    }

    const hasBootstrap = typeof bootstrap !== 'undefined' && bootstrap.Modal;
    const modal = hasBootstrap ? bootstrap.Modal.getOrCreateInstance(modalEl) : null;

    const imageEl = document.getElementById('newsModalImage');
    const thumbsEl = document.getElementById('newsModalThumbs');
    const dateEl = document.getElementById('newsModalDate');
    const titleEl = document.getElementById('newsModalTitle');
    const textEl = document.getElementById('newsModalText');
    const counterEl = document.getElementById('newsMediaCounter');
    const waEl = document.getElementById('newsModalWhatsApp');
    const prevMediaBtn = document.getElementById('newsMediaPrev');
    const nextMediaBtn = document.getElementById('newsMediaNext');
    const prevArticleBtn = document.getElementById('newsPrevArticle');
    const nextArticleBtn = document.getElementById('newsNextArticle');

    if (!imageEl || !thumbsEl || !dateEl || !titleEl || !textEl) {
        console.error('Elementos do modal de notícias incompletos.');
        return;
    }

    let articleIndex = 0;
    let imageIndex = 0;
    let backdropEl = null;

    function showModal() {
        if (modal) {
            modal.show();
            return;
        }
        modalEl.classList.add('show');
        modalEl.style.display = 'block';
        modalEl.removeAttribute('aria-hidden');
        modalEl.setAttribute('aria-modal', 'true');
        document.body.classList.add('modal-open');
        backdropEl = document.createElement('div');
        backdropEl.className = 'modal-backdrop fade show';
        document.body.appendChild(backdropEl);
    }

    function hideModal() {
        if (modal) {
            modal.hide();
            return;
        }
        modalEl.classList.remove('show');
        modalEl.style.display = 'none';
        modalEl.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        if (backdropEl) {
            backdropEl.remove();
            backdropEl = null;
        }
    }

    function setImage(index) {
        const article = NEWS_ARTICLES[articleIndex];
        if (!article || !article.images || !article.images.length) return;

        imageIndex = (index + article.images.length) % article.images.length;
        imageEl.style.opacity = '0.4';
        imageEl.src = article.images[imageIndex];
        imageEl.alt = article.title;
        if (counterEl) {
            counterEl.textContent = `${imageIndex + 1} / ${article.images.length}`;
        }

        thumbsEl.querySelectorAll('.news-modal-thumb').forEach((thumb, i) => {
            thumb.classList.toggle('active', i === imageIndex);
        });

        requestAnimationFrame(() => {
            imageEl.style.opacity = '1';
        });
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function renderArticle(index) {
        const total = NEWS_ARTICLES.length;
        if (!total) return;
        articleIndex = ((Number(index) % total) + total) % total;
        const article = NEWS_ARTICLES[articleIndex];
        const images = Array.isArray(article.images) ? article.images : [];
        const paragraphs = Array.isArray(article.paragraphs) ? article.paragraphs : [];

        dateEl.textContent = article.date || '';
        titleEl.textContent = article.title || '';
        textEl.innerHTML = paragraphs.map(p => `<p>${escapeHtml(p)}</p>`).join('');

        if (waEl) {
            const shareText = encodeURIComponent(`${article.title} — Hospital de Cumura`);
            const pageUrl = encodeURIComponent(window.location.href.split('#')[0] + '#noticias');
            waEl.href = `https://wa.me/?text=${shareText}%20${pageUrl}`;
        }

        thumbsEl.innerHTML = '';
        images.forEach((src, i) => {
            const thumb = document.createElement('img');
            thumb.src = src;
            thumb.alt = `Foto ${i + 1}`;
            thumb.className = 'news-modal-thumb';
            thumb.addEventListener('click', (e) => {
                e.stopPropagation();
                setImage(i);
            });
            thumbsEl.appendChild(thumb);
        });

        if (prevArticleBtn) prevArticleBtn.disabled = total < 2;
        if (nextArticleBtn) nextArticleBtn.disabled = total < 2;

        const multi = images.length > 1;
        if (prevMediaBtn) prevMediaBtn.style.display = multi ? 'inline-flex' : 'none';
        if (nextMediaBtn) nextMediaBtn.style.display = multi ? 'inline-flex' : 'none';
        if (counterEl) {
            counterEl.style.display = images.length ? '' : 'none';
        }

        if (images.length) {
            setImage(0);
        } else {
            imageEl.removeAttribute('src');
            imageEl.alt = '';
            if (counterEl) counterEl.textContent = '0 / 0';
        }
    }

    function openNews(index) {
        const parsed = parseInt(index, 10);
        renderArticle(Number.isNaN(parsed) ? 0 : parsed);
        showModal();
    }

    // Delegação: funciona mesmo se os botões forem recriados
    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('[data-news]');
        if (!trigger) return;
        e.preventDefault();
        openNews(trigger.getAttribute('data-news'));
    });

    if (prevMediaBtn) {
        prevMediaBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            setImage(imageIndex - 1);
        });
    }
    if (nextMediaBtn) {
        nextMediaBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            setImage(imageIndex + 1);
        });
    }
    if (prevArticleBtn) {
        prevArticleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            renderArticle(articleIndex - 1);
        });
    }
    if (nextArticleBtn) {
        nextArticleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            renderArticle(articleIndex + 1);
        });
    }

    modalEl.querySelectorAll('[data-bs-dismiss="modal"]').forEach(btn => {
        btn.addEventListener('click', () => hideModal());
    });

    document.addEventListener('keydown', (e) => {
        if (!modalEl.classList.contains('show')) return;
        if (e.key === 'ArrowLeft') setImage(imageIndex - 1);
        if (e.key === 'ArrowRight') setImage(imageIndex + 1);
        if (e.key === 'Escape' && !hasBootstrap) hideModal();
    });

    let touchStartX = 0;
    imageEl.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].clientX;
    }, { passive: true });
    imageEl.addEventListener('touchend', (e) => {
        const delta = e.changedTouches[0].clientX - touchStartX;
        if (Math.abs(delta) < 40) return;
        setImage(delta < 0 ? imageIndex + 1 : imageIndex - 1);
    }, { passive: true });

    window.openNewsModal = openNews;
}