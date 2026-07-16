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
    const mensagem = document.getElementById('mensagem').value.trim();
    const btn = form.querySelector('button[type="submit"]');
    const originalText = btn.textContent;

    if (!nome || !telefone || !assunto || !mensagem) {
        alert('Por favor, preencha todos os campos obrigatórios.');
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
            body: JSON.stringify({ nome, email, telefone, assunto, mensagem }),
        });

        const result = await response.json();

        if (response.ok && result.success) {
            alert(`Mensagem enviada com sucesso, ${nome}! Entraremos em contacto em breve.`);
            form.reset();
        } else {
            throw new Error(result.message || 'Ocorreu um erro ao enviar a mensagem.');
        }
    } catch (error) {
        console.error('Erro no envio do formulário:', error);
        alert('Não foi possível enviar sua mensagem. Por favor, tente novamente mais tarde.');
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
    initNewsModal();
});

/* ============================================
   LINKS QUE PRÉ-SELECIONAM O ASSUNTO DO FORMULÁRIO
   ============================================ */
function initAssuntoLinks() {
    const assuntoSelect = document.getElementById('assunto');
    if (!assuntoSelect) return;

    document.querySelectorAll('[data-assunto]').forEach(link => {
        link.addEventListener('click', () => {
            const value = link.getAttribute('data-assunto');
            if (value && [...assuntoSelect.options].some(opt => opt.value === value)) {
                assuntoSelect.value = value;
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

    const services = [
        {
            icon: '🩺',
            title: 'Tratamento Clínico',
            details: 'Diagnóstico precoce, poliquimioterapia (PQT) e acompanhamento médico contínuo para todos os estágios da hanseníase.'
        },
        {
            icon: '🔬',
            title: 'Laboratório de Análise Clínica',
            details: 'Exames laboratoriais completos, testes diagnósticos para hanseníase e demais análises clínicas necessárias.'
        },
        {
            icon: '🩹',
            title: 'Dermatologia',
            details: 'Avaliação e tratamento de manifestações dermatológicas, incluindo lesões de pele e dermatites.'
        },
        {
            icon: '👁️',
            title: 'Oftalmologia',
            details: 'Avaliação oftalmológica, tratamento de complicações oftalmológicas e prevenção da cegueira.'
        },
        {
            icon: '🫁',
            title: 'Tuberculose (TB)',
            details: 'Diagnóstico e tratamento especializado de tuberculose, com acompanhamento multidisciplinar.'
        },
        {
            icon: '👶',
            title: 'Maternidade',
            details: 'Acompanhamento pré-natal, parto seguro e puerpério com protocolos de segurança para gestantes com hanseníase.'
        },
        {
            icon: '🏥',
            title: 'Radiologia',
            details: 'Serviços de diagnóstico por imagem: raios-X, ultrassonografia e demais exames radiológicos.'
        },
        {
            icon: '⚕️',
            title: 'Medicina Interna',
            details: 'Atendimento clínico geral e manejo de complicações sistêmicas associadas às doenças.'
        },
        {
            icon: '🔪',
            title: 'Cirurgia',
            details: 'Cirurgias reparadoras, procedimentos cirúrgicos e intervenções de urgência quando necessário.'
        },
        {
            icon: '🚨',
            title: 'UCI (Unidade de Cuidados Intensivos)',
            details: 'Cuidados intensivos para pacientes críticos com monitoramento contínuo e suporte avançado.'
        },
        {
            icon: '🦿',
            title: 'Reabilitação Física',
            details: 'Fisioterapia, órteses, próteses e cirurgias reparadoras para prevenir e tratar incapacidades físicas.'
        },
        {
            icon: '🧠',
            title: 'Apoio Psicossocial',
            details: 'Acompanhamento psicológico, grupos de apoio e reinserção social para pacientes e familiares.'
        },
        {
            icon: '📋',
            title: 'Educação em Saúde',
            details: 'Orientação sobre autocuidado, prevenção de incapacidades e combate ao estigma da hanseníase na comunidade.'
        },
        {
            icon: '🚑',
            title: 'Visitas Domiciliares',
            details: 'Equipe móvel que leva atendimento a pacientes com dificuldade de locomoção nas aldeias ao redor de Cumura.'
        }
    ];

    const totalItems = services.length;
    const angleStep = 360 / totalItems;
    const radius = (wheel.offsetWidth / 2) * 0.85; // Raio 85% do contêiner da roda
    let activeIndex = 0;
    let rotationInterval;

    // Gerar itens do círculo
    services.forEach((service, i) => {
        const angle = i * angleStep;
        const item = document.createElement('div');
        item.className = 'service-circle-item';
        item.dataset.index = i;
        
        const rotation = `rotate(${angle}deg) translate(${radius}px) rotate(${-angle}deg)`;
        item.style.transform = rotation;

        item.innerHTML = `<div class="service-icon">${service.icon}</div>`;
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
        });

        contentBox.classList.add('fade-out');
        setTimeout(() => {
            titleEl.textContent = services[index].title;
            detailsEl.textContent = services[index].details;
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
const NEWS_ARTICLES = [
    {
        date: '08 de Julho, 2026',
        title: 'Missão de Cirurgiões Internacionais em Cumura',
        images: [
            'images/missao_2018.jpg',
            'images/missao_h.webp',
            'images/missao.jpg',
            'images/hmh.jpg'
        ],
        paragraphs: [
            'Recebemos uma equipe de cirurgiões voluntários que realizarão cirurgias reparadoras durante todo o mês de agosto. Esta é uma oportunidade importante para os nossos pacientes terem acesso a procedimentos especializados.',
            'Os cirurgiões trabalharão em conjunto com a nossa equipe local, partilhando conhecimentos e experiências para o melhor atendimento aos pacientes.'
        ]
    },
    {
        date: '25 de Junho, 2026',
        title: 'Campanha de Conscientização nas Aldeias',
        images: [
            'images/patio_8.jpeg',
            'images/patio_1.jpeg',
            'images/patio_6.jpeg',
            'images/patio_9.jpeg'
        ],
        paragraphs: [
            'A nossa equipe móvel visitou 5 aldeias na região de Biombo para educar a população sobre os sinais da hanseníase. Mais de 200 pessoas participaram das palestras e receberam materiais informativos.',
            'O objetivo é quebrar tabus e estigmas, além de facilitar o diagnóstico precoce da doença.'
        ]
    },
    {
        date: '10 de Junho, 2026',
        title: 'Hospital Recebe Doação de Medicamentos Essenciais',
        images: [
            'images/patio_4.jpeg',
            'images/missao_h.webp',
            'images/patio_3.jpeg'
        ],
        paragraphs: [
            'Uma parceria com a OMS garantiu o fornecimento de PQT (Poliquimioterapia) para o tratamento de todos os nossos pacientes por mais um ano.',
            'Esta doação é fundamental para garantir a continuidade do tratamento de forma gratuita e de qualidade.'
        ]
    }
];

function initNewsModal() {
    const modalEl = document.getElementById('newsModal');
    if (!modalEl) {
        console.error('Modal de notícias (#newsModal) não encontrado.');
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
        if (!article) return;

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

    function renderArticle(index) {
        const total = NEWS_ARTICLES.length;
        articleIndex = ((Number(index) % total) + total) % total;
        const article = NEWS_ARTICLES[articleIndex];

        dateEl.textContent = article.date;
        titleEl.textContent = article.title;
        textEl.innerHTML = article.paragraphs.map(p => `<p>${p}</p>`).join('');

        if (waEl) {
            const shareText = encodeURIComponent(`${article.title} — Hospital de Cumura`);
            const pageUrl = encodeURIComponent(window.location.href.split('#')[0] + '#noticias');
            waEl.href = `https://wa.me/?text=${shareText}%20${pageUrl}`;
        }

        thumbsEl.innerHTML = '';
        article.images.forEach((src, i) => {
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

        const multi = article.images.length > 1;
        if (prevMediaBtn) prevMediaBtn.style.display = multi ? 'inline-flex' : 'none';
        if (nextMediaBtn) nextMediaBtn.style.display = multi ? 'inline-flex' : 'none';

        setImage(0);
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