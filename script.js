/* ============================================
   SCROLL SUAVE PARA NAVEGAÇÃO
   ============================================ */
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const targetId = this.getAttribute('href');
        const targetElement = document.querySelector(targetId);

        if (targetElement) {
            const header = document.querySelector('header');
            const headerHeight = header ? header.offsetHeight : 0; // Garante que não quebre se o header não existir
            const targetPosition = targetElement.offsetTop - headerHeight;

            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
        }
    });
});

/* ============================================
   FORMULÁRIO DE CONTATO
   ============================================ */
document.getElementById('formAgendamento').addEventListener('submit', async function (e) {
    e.preventDefault();

    const form = this;
    const nome = document.getElementById('nome').value.trim();
    const telefone = document.getElementById('telefone').value.trim();
    const assunto = document.getElementById('assunto').value;
    const btn = form.querySelector('.btn-submit');
    const originalText = btn.textContent;

    // Validação simples
    if (!nome || !telefone || !assunto) {
        alert('Por favor, preencha todos os campos obrigatórios.');
        return;
    }

    // Desabilita o botão e mostra feedback de envio
    btn.textContent = 'Enviando...';
    btn.disabled = true;

    try {
        const response = await fetch('/hmh/enviar-email.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ nome, telefone, assunto }),
        });

        const result = await response.json();

        if (response.ok && result.success) {
            alert(`Mensagem enviada com sucesso, ${nome}! Entraremos em contato em breve.`);
            form.reset();
        } else {
            throw new Error(result.message || 'Ocorreu um erro ao enviar a mensagem.');
        }
    } catch (error) {
        console.error('Erro no envio do formulário:', error);
        alert('Não foi possível enviar sua mensagem. Por favor, tente novamente mais tarde.');
    } finally {
        // Restaura o botão
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
    initHeroSlider();
});

/* ============================================
   SLIDER DE IMAGENS PARA O HERO
   ============================================ */
function initHeroSlider() {
    // Use o seletor correto para a sua seção de herói. Ex: '.hero-section', '#home', etc.
    const heroElement = document.querySelector('.hero'); 
    
    if (!heroElement) {
        console.warn('Elemento do herói para o slider não foi encontrado. Verifique o seletor.');
        return;
    }

    // --- CONFIGURAÇÃO ---
    // Adicione os caminhos para as suas imagens aqui.
    const images = [
        'img/patio_1.jpeg',
        'img/patio_2.jpeg',
        'img/patio_3.jpeg',
        'img/patio_4.jpeg',
        'img/patio_5.jpeg',
        'img/patio_6.jpeg',
        'img/patio_7.jpeg'
    ];
    const slideDuration = 5000; // Tempo que cada imagem fica visível (em milissegundos)
    const transitionDuration = 1000; // Duração da transição de uma imagem para outra (em milissegundos)
    // --------------------

    if (images.length < 2) {
        if (images.length === 1) {
            heroElement.style.backgroundImage = `url('${images[0]}')`;
        }
        console.info('Slider do herói desativado: são necessárias pelo menos 2 imagens.');
        return;
    }

    let currentImageIndex = 0;

    // Pré-carrega as imagens para evitar "piscar" na primeira transição
    images.forEach(src => { (new Image()).src = src; });

    // Injeta o CSS necessário para a transição de cross-fade diretamente no <head>
    const style = document.createElement('style');
    document.head.appendChild(style);
    style.sheet.insertRule(
        `.hero::after { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-size: cover; background-position: center; z-index: -1; opacity: 0; transition: opacity ${transitionDuration / 1000}s ease-in-out; }`, 0);
    heroElement.style.position = 'relative';
    heroElement.style.zIndex = '1';
    heroElement.style.backgroundSize = 'cover';
    heroElement.style.backgroundPosition = 'center';

    function changeSlide() {
        const nextImageIndex = (currentImageIndex + 1) % images.length;
        const nextImageUrl = `url('${images[nextImageIndex]}')`;

        style.sheet.cssRules[0].style.backgroundImage = nextImageUrl;
        style.sheet.cssRules[0].style.opacity = 1;

        setTimeout(() => {
            heroElement.style.backgroundImage = nextImageUrl;
            style.sheet.cssRules[0].style.opacity = 0;
            currentImageIndex = nextImageIndex;
        }, transitionDuration);
    }

    heroElement.style.backgroundImage = `url('${images[currentImageIndex]}')`;
    setInterval(changeSlide, slideDuration);
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
            icon: '🩹',
            title: 'Curativos e Feridas',
            details: 'Tratamento especializado de úlceras e feridas crônicas com técnica asséptica e materiais adequados.'
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