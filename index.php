<?php
require_once __DIR__ . '/includes/content.php';
$seo = seo_load();
$hero = hero_load();
$news = news_load();
$services = services_load();
$heroBrand = $hero['brand'] !== '' ? $hero['brand'] : 'Hospital do Mal de Hansen de Cumura';
$heroSlides = $hero['slides'] ?? [];
$featuredIndex = 0;
foreach ($news as $i => $item) {
    if (!empty($item['featured'])) {
        $featuredIndex = $i;
        break;
    }
}
$featuredNews = $news[$featuredIndex] ?? null;
$waNumber = preg_replace('/\D+/', '', (string) ($seo['whatsapp'] ?: $seo['phone_tel']));
$waText = rawurlencode('Olá, gostaria de obter informações sobre o Hospital de Cumura.');
$waUrl = $waNumber !== '' ? 'https://wa.me/' . $waNumber . '?text=' . $waText : '#contato';
$waHanseniase = $waNumber !== ''
    ? 'https://wa.me/' . $waNumber . '?text=' . rawurlencode('Olá, gostaria de informações sobre hanseniase no Hospital de Cumura.')
    : '#contato';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <?php seo_render_head($seo); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,500;8..60,600;8..60,700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css?v=20260921z">
</head>
<body>

    <!-- Preloader -->
    <div class="preloader">
        <div class="spinner"></div>
    </div>

    <!-- Header institucional -->
    <header class="site-header fixed-top">
        <div class="topbar">
            <div class="container topbar-inner">
                <p class="topbar-left mb-0">Instituição de referência no tratamento da hanseníase · Biombo, Guiné-Bissau</p>
                <div class="topbar-right">
                    <a href="tel:<?= seo_e($seo['phone_tel'] ?: $seo['phone']) ?>"><?= seo_e($seo['phone']) ?></a>
                    <span class="topbar-sep" aria-hidden="true">|</span>
                    <a href="mailto:<?= seo_e($seo['email']) ?>"><?= seo_e($seo['email']) ?></a>
                </div>
            </div>
        </div>
        <nav class="navbar navbar-expand-lg" id="mainNavbar">
            <div class="container">
                <a class="navbar-brand logo" href="#home">
                    <span class="logo-mark" aria-hidden="true">HMH</span>
                    <span class="logo-text">
                        <span class="logo-name">Hospital de Cumura</span>
                        <span class="logo-sub">Mal de Hansen · Guiné-Bissau</span>
                    </span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navLinks" aria-controls="navLinks" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navLinks">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
                        <li class="nav-item"><a class="nav-link" href="#home">Início</a></li>
                        <li class="nav-item"><a class="nav-link" href="#sobre">Hospital</a></li>
                        <li class="nav-item"><a class="nav-link" href="#servicos">Serviços</a></li>
                        <li class="nav-item"><a class="nav-link" href="#hanseniase">Hanseníase</a></li>
                        <li class="nav-item"><a class="nav-link" href="#noticias">Notícias</a></li>
                        <li class="nav-item"><a class="nav-link" href="#contato">Contacto</a></li>
                        <li class="nav-item ms-lg-3"><a href="#contato" class="btn btn-agendar">Agendar Consulta</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero -->
    <section class="hero" id="home">
        <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="8000">
            <?php if ($heroSlides): ?>
            <div class="carousel-indicators">
                <?php foreach ($heroSlides as $i => $_slide): ?>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= (int) $i ?>"<?= $i === 0 ? ' class="active" aria-current="true"' : '' ?> aria-label="Slide <?= (int) ($i + 1) ?>"></button>
                <?php endforeach; ?>
            </div>
            <div class="carousel-inner">
                <?php foreach ($heroSlides as $i => $slide):
                    $img = $slide['image'] !== '' ? $slide['image'] : 'images/patio_10.jpeg';
                    $btn1Assunto = $slide['btn1_assunto'] ?? '';
                ?>
                <div class="carousel-item<?= $i === 0 ? ' active' : '' ?>" style="background-image: url('<?= seo_e($img) ?>');">
                    <div class="carousel-caption d-flex flex-column justify-content-center h-100">
                        <p class="hero-brand"><?= seo_e($heroBrand) ?></p>
                        <h1 class="display-4"><?= seo_e($slide['title']) ?></h1>
                        <?php if (($slide['text'] ?? '') !== ''): ?>
                        <p class="lead"><?= seo_e($slide['text']) ?></p>
                        <?php endif; ?>
                        <div class="hero-buttons mt-4">
                            <?php if (($slide['btn1_label'] ?? '') !== ''): ?>
                            <a href="<?= seo_e($slide['btn1_href'] ?: '#contato') ?>" class="btn btn-primary btn-lg"<?= $btn1Assunto !== '' ? ' data-assunto="' . seo_e($btn1Assunto) . '"' : '' ?>><?= seo_e($slide['btn1_label']) ?></a>
                            <?php endif; ?>
                            <?php if (($slide['btn2_label'] ?? '') !== ''): ?>
                            <a href="<?= seo_e($slide['btn2_href'] ?: '#sobre') ?>" class="btn btn-outline-light btn-lg"><?= seo_e($slide['btn2_label']) ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if (count($heroSlides) > 1): ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Próximo</span>
            </button>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Faixa institucional -->
    <aside class="institutional-band" aria-label="Missão institucional">
        <div class="container">
            <div class="institutional-band-grid">
                <div>
                    <p class="band-label">Missão</p>
                    <p class="band-text mb-0">Prestar cuidados especializados, dignos e gratuitos a pessoas afectadas pela hanseníase.</p>
                </div>
                <div>
                    <p class="band-label">Âmbito</p>
                    <p class="band-text mb-0">Tratamento clínico, reabilitação física e apoio psicossocial em Cumura e na região.</p>
                </div>
                <div>
                    <p class="band-label">Compromisso</p>
                    <p class="band-text mb-0">Combater o estigma, promover o diagnóstico precoce e a reintegração na comunidade.</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- Sobre -->
    <section id="sobre">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6 fade-up">
                    <p class="section-eyebrow">A instituição</p>
                    <h2 class="section-heading-left">Nossa História</h2>
                    <p>O <strong>Hospital do Mal de Hansen de Cumura</strong> é uma instituição de referência no tratamento da hanseníase (mal de Hansen) na região de Biombo, Guiné-Bissau. Fundado durante o período colonial, o hospital tornou-se um símbolo de acolhimento e cuidado para centenas de pacientes ao longo das décadas.</p>
                    <p>Localizado na zona rural de Cumura, a cerca de 30 km de Bissau, o hospital oferece tratamento especializado, reabilitação física e apoio psicossocial para pessoas afetadas pela hanseníase, muitas das quais enfrentam estigma e exclusão social.</p>
                    <p>Contamos com uma equipe multidisciplinar dedicada, incluindo médicos, enfermeiros, fisioterapeutas e assistentes sociais, todos comprometidos com a missão de devolver dignidade e qualidade de vida aos nossos pacientes.</p>
                    <p class="institution-place"><strong>Cumura, Região de Biombo, Guiné-Bissau</strong></p>
                </div>
                <div class="col-lg-6 fade-up">
                    <div class="galeria-main-image">
                        <img src="images/patio_3.jpeg" alt="Vista do pátio do hospital" id="mainGalleryImage">
                    </div>
                    <div class="galeria-thumbnails">
                        <img src="images/patio_1.jpeg" alt="Vista do pátio do hospital" class="thumbnail active">
                        <img src="images/patio_10.jpeg" alt="Equipe de enfermagem" class="thumbnail">
                        <img src="images/patio_2.jpeg" alt="Paciente em sessão de fisioterapia" class="thumbnail">
                        <img src="images/patio_6.jpeg" alt="Fachada do hospital" class="thumbnail">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Serviços -->
    <section id="servicos" class="servicos-circle-bg">
        <div class="container">
            <h2 class="section-title fade-up">Nossos Serviços</h2>
            <p class="section-subtitle fade-up">Oferecemos assistência completa e humanizada para pacientes com hanseníase e suas famílias.</p>

            <div class="services-circle-container fade-up">
                <div class="service-details-content">
                    <h3 id="service-circle-title"></h3>
                    <p id="service-circle-details"></p>
                    <a href="#contato" class="btn btn-primary mt-3" id="serviceBookBtn" data-assunto="Agendamento de Consulta">Agendar este serviço</a>
                </div>
                <div class="service-wheel-wrapper">
                    <div class="service-wheel">
                        <!-- Itens serão gerados por JS -->
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Hanseníase — educação e combate ao estigma -->
    <section id="hanseniase" class="hanseniase-section">
        <div class="container">
            <h2 class="section-title fade-up">Sobre a Hanseníase</h2>
            <p class="section-subtitle fade-up">Informação clara ajuda a diagnosticar cedo, tratar e acabar com o estigma.</p>

            <div class="row g-4 g-lg-5 mb-5">
                <div class="col-lg-6 fade-up">
                    <h3 class="hanseniase-heading">O que é?</h3>
                    <p>A hanseníase (mal de Hansen) é uma doença infecciosa causada pela bactéria <em>Mycobacterium leprae</em>. Afeta principalmente a pele, os nervos e, por vezes, os olhos e as mucosas. Tem cura — e o tratamento é gratuito.</p>
                    <p class="mb-0">Quanto mais cedo for o diagnóstico, menor o risco de incapacidades e de transmissão na comunidade.</p>
                </div>
                <div class="col-lg-6 fade-up">
                    <h3 class="hanseniase-heading">Sinais de alerta</h3>
                    <ul class="hanseniase-list">
                        <li>Manchas na pele com perda de sensibilidade ao toque, calor ou dor</li>
                        <li>Formigamento, dormência ou fraqueza nas mãos e pés</li>
                        <li>Nódulos ou endurecimento da pele</li>
                        <li>Feridas que demoram a cicatrizar</li>
                        <li>Dor ou inflamação nos nervos (cotovelos, joelhos, pescoço)</li>
                    </ul>
                </div>
            </div>

            <div class="fade-up">
                <h3 class="hanseniase-heading text-center mb-4">Mitos e verdades</h3>
                <div class="accordion hanseniase-accordion" id="accordionHanseniase">
                    <div class="accordion-item">
                        <h4 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#mito1" aria-expanded="true" aria-controls="mito1">
                                A hanseníase é uma maldição ou castigo?
                            </button>
                        </h4>
                        <div id="mito1" class="accordion-collapse collapse show" data-bs-parent="#accordionHanseniase">
                            <div class="accordion-body">
                                <strong>Mito.</strong> É uma doença causada por uma bactéria. Não tem origem espiritual. Qualquer pessoa pode adoecer e merece cuidado com dignidade.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h4 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mito2" aria-expanded="false" aria-controls="mito2">
                                Contagia-se facilmente pelo toque?
                            </button>
                        </h4>
                        <div id="mito2" class="accordion-collapse collapse" data-bs-parent="#accordionHanseniase">
                            <div class="accordion-body">
                                <strong>Mito.</strong> A transmissão exige contacto próximo e prolongado com alguém sem tratamento. Depois de iniciar a poliquimioterapia (PQT), a pessoa deixa de transmitir a doença.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h4 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mito3" aria-expanded="false" aria-controls="mito3">
                                A hanseníase tem cura?
                            </button>
                        </h4>
                        <div id="mito3" class="accordion-collapse collapse" data-bs-parent="#accordionHanseniase">
                            <div class="accordion-body">
                                <strong>Verdade.</strong> O tratamento com PQT cura a doença. No Hospital de Cumura o acompanhamento inclui também reabilitação e apoio psicossocial.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h4 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mito4" aria-expanded="false" aria-controls="mito4">
                                Quem tem hanseníase deve ser afastado da família?
                            </button>
                        </h4>
                        <div id="mito4" class="accordion-collapse collapse" data-bs-parent="#accordionHanseniase">
                            <div class="accordion-body">
                                <strong>Mito.</strong> O isolamento social agrava o sofrimento e atrasa o tratamento. Com o diagnóstico e a medicação, a pessoa pode viver em comunidade e voltar à vida normal.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="hanseniase-cta text-center fade-up mt-5">
                <p class="mb-3">Tem manchas ou sintomas? Procure avaliação médica o mais cedo possível.</p>
                <a href="#contato" class="btn btn-primary me-2 mb-2">Agendar consulta</a>
                <a href="<?= seo_e($waHanseniase) ?>" class="btn btn-whatsapp-outline mb-2" target="_blank" rel="noopener noreferrer">Falar no WhatsApp</a>
            </div>
        </div>
    </section>

    <!-- Depoimentos -->
    <section class="depoimentos" id="depoimentos">
        <div class="container">
            <h2 class="section-title fade-up">Histórias de Superação</h2>
            <p class="section-subtitle fade-up">Conheça relatos de pacientes que encontraram acolhimento e tratamento no Hospital de Cumura.</p>
            <div class="row g-4">
                <div class="col-lg-4 fade-up">
                    <div class="depoimento-card card h-100">
                        <div class="card-body">
                            <div class="stars">★★★★★</div>
                            <p class="card-text">"Cheguei ao hospital sem esperança. Hoje, depois do tratamento, posso trabalhar novamente e cuidar da minha família. Sou grato a toda a equipe."</p>
                            <p class="paciente">— Mamadu Sissé</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 fade-up">
                    <div class="depoimento-card card h-100">
                        <div class="card-body">
                            <div class="stars">★★★★★</div>
                            <p class="card-text">"O Hospital de Cumura me acolheu quando fui rejeitada pela minha própria comunidade. Aqui encontrei tratamento, respeito e uma nova família."</p>
                            <p class="paciente">— Fátima Baldé</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 fade-up">
                    <div class="depoimento-card card h-100">
                        <div class="card-body">
                            <div class="stars">★★★★★</div>
                            <p class="card-text">"Meu filho foi diagnosticado cedo graças ao trabalho de conscientização do hospital. Hoje ele está curado e estuda normalmente. Que bênção!"</p>
                            <p class="paciente">— Maria da Silva</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Notícias -->
    <section id="noticias">
        <div class="container">
            <h2 class="section-title fade-up">Últimas Notícias</h2>
            <p class="section-subtitle fade-up">Missões, campanhas e apoios do Hospital de Cumura.</p>

            <?php if ($featuredNews):
                $featImg = $featuredNews['images'][0] ?? 'images/patio_10.jpeg';
            ?>
            <article class="news-main fade-up" aria-label="Notícia: <?= seo_e($featuredNews['title']) ?>">
                <div class="row g-0 align-items-stretch">
                    <div class="col-lg-6">
                        <div class="news-main-image">
                            <img src="<?= seo_e($featImg) ?>" alt="<?= seo_e($featuredNews['title']) ?>" loading="lazy" decoding="async">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="news-main-content">
                            <div class="news-meta">
                                <?php if (($featuredNews['tag'] ?? '') !== ''): ?>
                                <span class="news-tag"><?= seo_e($featuredNews['tag']) ?></span>
                                <?php endif; ?>
                                <time datetime="<?= seo_e($featuredNews['date_iso'] ?? '') ?>"><?= seo_e($featuredNews['date'] ?? '') ?></time>
                            </div>
                            <h3 class="news-main-title"><?= seo_e($featuredNews['title']) ?></h3>
                            <p class="news-main-text"><?= seo_e($featuredNews['excerpt'] ?? '') ?></p>
                            <button type="button" class="btn-leia-mais" data-news="<?= (int) $featuredIndex ?>">Leia mais</button>
                        </div>
                    </div>
                </div>
            </article>
            <?php endif; ?>

            <?php
            $secondary = [];
            foreach ($news as $i => $item) {
                if ($i === $featuredIndex) {
                    continue;
                }
                $secondary[] = ['index' => $i, 'item' => $item];
            }
            ?>
            <?php if ($secondary): ?>
            <div class="row g-4 news-secondary fade-up">
                <?php foreach ($secondary as $row):
                    $item = $row['item'];
                    $idx = $row['index'];
                    $thumb = $item['images'][0] ?? 'images/patio_10.jpeg';
                ?>
                <div class="col-md-6">
                    <article class="news-item h-100" aria-label="Notícia: <?= seo_e($item['title']) ?>">
                        <div class="news-item-image">
                            <img src="<?= seo_e($thumb) ?>" alt="<?= seo_e($item['title']) ?>" loading="lazy" decoding="async">
                        </div>
                        <div class="news-item-content">
                            <div class="news-meta">
                                <?php if (($item['tag'] ?? '') !== ''): ?>
                                <span class="news-tag"><?= seo_e($item['tag']) ?></span>
                                <?php endif; ?>
                                <time datetime="<?= seo_e($item['date_iso'] ?? '') ?>"><?= seo_e($item['date'] ?? '') ?></time>
                            </div>
                            <h3 class="news-item-title"><?= seo_e($item['title']) ?></h3>
                            <p class="news-item-text"><?= seo_e($item['excerpt'] ?? '') ?></p>
                            <button type="button" class="btn-leia-mais" data-news="<?= (int) $idx ?>">Leia mais</button>
                        </div>
                    </article>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Modal moderno de notícias -->
    <div class="modal fade news-modal" id="newsModal" tabindex="-1" aria-labelledby="newsModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content news-modal-content">
                <button type="button" class="news-modal-close" data-bs-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">×</span>
                </button>

                <div class="news-modal-layout">
                    <div class="news-modal-media">
                        <div class="news-modal-stage">
                            <img id="newsModalImage" src="" alt="">
                            <button type="button" class="news-media-nav news-media-prev" id="newsMediaPrev" aria-label="Foto anterior">‹</button>
                            <button type="button" class="news-media-nav news-media-next" id="newsMediaNext" aria-label="Foto seguinte">›</button>
                            <span class="news-media-counter" id="newsMediaCounter">1 / 1</span>
                        </div>
                        <div class="news-modal-thumbs" id="newsModalThumbs"></div>
                    </div>

                    <div class="news-modal-panel">
                        <p class="news-modal-date" id="newsModalDate"></p>
                        <h2 class="news-modal-title" id="newsModalTitle"></h2>
                        <div class="news-modal-text" id="newsModalText"></div>

                        <div class="news-modal-actions">
                            <a id="newsModalWhatsApp" class="btn btn-whatsapp-solid" href="#" target="_blank" rel="noopener noreferrer">Partilhar no WhatsApp</a>
                            <a href="#contato" class="btn btn-outline-primary" data-bs-dismiss="modal">Contactar hospital</a>
                        </div>

                        <div class="news-modal-pager">
                            <button type="button" class="news-pager-btn" id="newsPrevArticle">← Notícia anterior</button>
                            <button type="button" class="news-pager-btn" id="newsNextArticle">Próxima notícia →</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contato / Agendamento -->
    <section id="contato">
        <div class="container">
            <h2 class="section-title fade-up">Entre em Contato</h2>
            <p class="section-subtitle fade-up">Agende consulta, peça informações ou saiba como apoiar o hospital.</p>
            <div class="row g-5 align-items-start">
                <div class="col-lg-5 fade-up">
                    <div class="contato-info">
                        <h3>Hospital de Cumura</h3>
                        <div class="info-item">
                            <p class="mb-0"><strong>Morada</strong><br><?= seo_e($seo['address_locality']) ?>, Região de <?= seo_e($seo['address_region']) ?><br>Guiné-Bissau (cerca de 30 km de Bissau)</p>
                        </div>
                        <div class="info-item">
                            <p class="mb-0"><strong>Telefone</strong><br><a href="tel:<?= seo_e($seo['phone_tel'] ?: $seo['phone']) ?>"><?= seo_e($seo['phone']) ?></a></p>
                        </div>
                        <div class="info-item">
                            <p class="mb-0"><strong>E-mail</strong><br><a href="mailto:<?= seo_e($seo['email']) ?>"><?= seo_e($seo['email']) ?></a></p>
                        </div>
                        <div class="info-item">
                            <p class="mb-0"><strong>Horário</strong><br><?= seo_e($seo['opening_hours_label']) ?></p>
                        </div>
                        <a class="btn btn-outline-primary mt-3" href="https://www.openstreetmap.org/search?query=Cumura%20Guinea-Bissau" target="_blank" rel="noopener noreferrer">Ver no mapa</a>
                        <p class="contato-nota mt-3">Os contactos podem ser actualizados na <a href="admin/seo.php">área de administração SEO</a>.</p>
                    </div>
                </div>
                <div class="col-lg-7 fade-up">
                    <form id="formAgendamento" novalidate>
                        <div class="hp-field" aria-hidden="true">
                            <label for="website">Website</label>
                            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="nome" name="nome" autocomplete="name" required maxlength="120">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email" autocomplete="email" maxlength="180">
                        </div>
                        <div class="mb-3">
                            <label for="telefone" class="form-label">Telefone</label>
                            <input type="tel" class="form-control" id="telefone" name="telefone" placeholder="+245" autocomplete="tel" required maxlength="40">
                        </div>
                        <div class="mb-3">
                            <label for="assunto" class="form-label">Assunto</label>
                            <select class="form-select" id="assunto" name="assunto" required>
                                <option value="">Selecione um assunto...</option>
                                <option value="Agendamento de Consulta">Agendamento de Consulta</option>
                                <option value="Doação">Doação / Apoio</option>
                                <option value="Voluntariado">Voluntariado / Missão</option>
                                <option value="Informações">Informações</option>
                                <option value="Outro">Outro</option>
                            </select>
                        </div>
                        <div class="mb-3" id="servicoFieldWrap" hidden>
                            <label for="servico" class="form-label">Serviço pretendido</label>
                            <select class="form-select" id="servico" name="servico">
                                <option value="">Seleccione o serviço...</option>
                                <?php foreach ($services as $svc): ?>
                                <option value="<?= seo_e($svc['title']) ?>"><?= seo_e($svc['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="form-text mb-0">Escolha o serviço entre os disponíveis no hospital.</p>
                        </div>
                        <div class="mb-3" id="diaFieldWrap" hidden>
                            <label class="form-label" id="calendarioLabel">Dia pretendido</label>
                            <input type="hidden" id="dia" name="dia" value="">
                            <div class="booking-calendar" id="bookingCalendar" aria-labelledby="calendarioLabel">
                                <div class="cal-header">
                                    <button type="button" class="cal-nav" id="calPrev" aria-label="Mês anterior">‹</button>
                                    <p class="cal-month" id="calMonthTitle"></p>
                                    <button type="button" class="cal-nav" id="calNext" aria-label="Mês seguinte">›</button>
                                </div>
                                <div class="cal-weekdays" aria-hidden="true">
                                    <span>Seg</span><span>Ter</span><span>Qua</span><span>Qui</span><span>Sex</span><span>Sáb</span><span>Dom</span>
                                </div>
                                <div class="cal-grid" id="calGrid"></div>
                            </div>
                            <p class="form-text mb-0" id="calHint">Seleccione primeiro o serviço para ver os dias disponíveis.</p>
                            <p class="cal-selected" id="calSelected" hidden></p>
                        </div>
                        <div class="mb-3">
                            <label for="mensagem" class="form-label">Mensagem</label>
                            <textarea class="form-control" id="mensagem" name="mensagem" rows="4" required maxlength="3000"></textarea>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="privacidade" name="privacidade" required>
                            <label class="form-check-label" for="privacidade">
                                Li e aceito a <a href="privacidade.html" target="_blank" rel="noopener noreferrer">política de privacidade</a>.
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-submit w-100">Enviar mensagem</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="site-footer text-white">
        <div class="container">
            <div class="row g-4 footer-grid">
                <div class="col-lg-5">
                    <div class="footer-brand-block">
                        <span class="footer-mark" aria-hidden="true">HMH</span>
                        <div>
                            <p class="footer-brand mb-1">Hospital do Mal de Hansen de Cumura</p>
                            <p class="footer-text mb-0">Instituição de saúde dedicada ao tratamento, reabilitação e dignidade das pessoas afectadas pela hanseníase na Região de Biombo, Guiné-Bissau.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-lg-3">
                    <p class="footer-heading">Contactos oficiais</p>
                    <p class="footer-text mb-1"><?= seo_e($seo['address_locality']) ?>, <?= seo_e($seo['address_region']) ?>, Guiné-Bissau</p>
                    <p class="footer-text mb-1"><a href="tel:<?= seo_e($seo['phone_tel'] ?: $seo['phone']) ?>"><?= seo_e($seo['phone']) ?></a></p>
                    <p class="footer-text mb-0"><a href="mailto:<?= seo_e($seo['email']) ?>"><?= seo_e($seo['email']) ?></a></p>
                </div>
                <div class="col-md-4 col-lg-4">
                    <p class="footer-heading">Navegação</p>
                    <ul class="footer-links">
                        <li><a href="#sobre">O Hospital</a></li>
                        <li><a href="#servicos">Serviços clínicos</a></li>
                        <li><a href="#hanseniase">Informação sobre hanseníase</a></li>
                        <li><a href="#noticias">Notícias institucionais</a></li>
                        <li><a href="#contato">Contacto e doações</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p class="footer-copy mb-0">&copy; 2026 Hospital do Mal de Hansen de Cumura. Todos os direitos reservados.</p>
                <p class="footer-copy mb-0"><a href="privacidade.html">Política de privacidade</a> · Cumura · Biombo · Guiné-Bissau</p>
            </div>
        </div>
    </footer>

    <!-- Ações flutuantes -->
    <div class="float-actions">
        <a id="whatsappBtn"
           class="whatsapp-float"
           href="<?= seo_e($waUrl) ?>"
           target="_blank"
           rel="noopener noreferrer"
           aria-label="Contactar pelo WhatsApp"
           title="WhatsApp">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="26" height="26" aria-hidden="true" focusable="false">
                <path fill="currentColor" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/>
            </svg>
        </a>
        <button id="backToTopBtn" type="button" title="Voltar ao topo" aria-label="Voltar ao topo">↑</button>
    </div>

    <!-- Service Modal -->
    <div class="modal fade" id="serviceModal" tabindex="-1" aria-labelledby="serviceModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="serviceModalLabel">
                <span class="modal-icon me-2"></span>
                <span class="modal-title-text"></span>
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            <a href="#contato" class="btn btn-primary" id="modalAgendarBtn" data-assunto="Agendamento de Consulta" data-bs-dismiss="modal">Agendar Consulta</a>
          </div>
        </div>
      </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>window.SITE_NEWS = <?= json_encode(news_for_js($news), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;</script>
    <script>window.SITE_SERVICES = <?= json_encode(services_for_js($services), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;</script>
    <script src="script.js?v=20260921aa"></script>
</body>
</html>
