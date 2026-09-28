<?php
require_once __DIR__ . '/includes/content.php';
$seo = seo_load();
$gallery = gallery_load();
$pageTitle = ($gallery['page_title'] !== '' ? $gallery['page_title'] : 'Galeria');
$pageIntro = $gallery['page_intro'] ?? '';
$items = $gallery['items'] ?? [];
$short = $seo['site_short_name'] !== '' ? $seo['site_short_name'] : 'HMH Cumura';
$waNumber = preg_replace('/\D+/', '', (string) ($seo['whatsapp'] ?: $seo['phone_tel']));
$waText = rawurlencode('Olá, gostaria de obter informações sobre o Hospital de Cumura.');
$waUrl = $waNumber !== '' ? 'https://wa.me/' . $waNumber . '?text=' . $waText : 'index.php#contato';
$canonicalBase = rtrim($seo['canonical_url'], '/');
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <?php seo_render_head($seo, [
        'title' => $pageTitle . ' | ' . $short,
        'description' => $pageIntro !== '' ? $pageIntro : $seo['description'],
        'canonical' => $canonicalBase . '/galeria.php',
    ]); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,500;8..60,600;8..60,700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css?v=20260921gal">
</head>
<body class="page-galeria">

    <div class="preloader">
        <div class="spinner"></div>
    </div>

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
                <a class="navbar-brand logo" href="index.php">
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
                        <li class="nav-item"><a class="nav-link" href="index.php#home">Início</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php#sobre">Hospital</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php#servicos">Serviços</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php#hanseniase">Hanseníase</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php#noticias">Notícias</a></li>
                        <li class="nav-item"><a class="nav-link active" href="galeria.php" aria-current="page">Galeria</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php#contato">Contacto</a></li>
                        <li class="nav-item ms-lg-3"><a href="index.php#contato" class="btn btn-agendar">Agendar Consulta</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <section class="gallery-page-hero" aria-labelledby="galleryPageTitle">
            <div class="gallery-page-hero-bg" aria-hidden="true"></div>
            <div class="container gallery-page-hero-inner">
                <p class="gallery-kicker">Hospital de Cumura</p>
                <h1 id="galleryPageTitle" class="gallery-page-title"><?= seo_e($pageTitle) ?></h1>
                <?php if ($pageIntro !== ''): ?>
                <p class="gallery-page-intro"><?= seo_e($pageIntro) ?></p>
                <?php endif; ?>
            </div>
        </section>

        <section class="gallery-page-section">
            <div class="container">
                <?php if ($items): ?>
                <div class="gallery-masonry" id="galleryGrid">
                    <?php foreach ($items as $i => $item):
                        $img = $item['image'] ?? '';
                        $title = $item['title'] ?? '';
                        $desc = $item['description'] ?? '';
                        $alt = $title !== '' ? $title : ('Imagem da galeria ' . ($i + 1));
                    ?>
                    <figure class="gallery-card-public">
                        <button
                            type="button"
                            class="gallery-card-trigger"
                            data-gallery-index="<?= (int) $i ?>"
                            aria-label="<?= seo_e('Ver: ' . $alt) ?>"
                        >
                            <img src="<?= seo_e($img) ?>" alt="<?= seo_e($alt) ?>" loading="lazy" width="800" height="600">
                        </button>
                        <?php if ($title !== '' || $desc !== ''): ?>
                        <figcaption class="gallery-card-caption">
                            <?php if ($title !== ''): ?>
                            <h2 class="gallery-card-title"><?= seo_e($title) ?></h2>
                            <?php endif; ?>
                            <?php if ($desc !== ''): ?>
                            <p class="gallery-card-desc"><?= seo_e($desc) ?></p>
                            <?php endif; ?>
                        </figcaption>
                        <?php endif; ?>
                    </figure>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="gallery-empty">A galeria será actualizada em breve.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

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
                        <li><a href="index.php#sobre">O Hospital</a></li>
                        <li><a href="index.php#servicos">Serviços clínicos</a></li>
                        <li><a href="galeria.php">Galeria</a></li>
                        <li><a href="index.php#noticias">Notícias institucionais</a></li>
                        <li><a href="index.php#contato">Contacto e doações</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p class="footer-copy mb-0">&copy; 2026 Hospital do Mal de Hansen de Cumura. Todos os direitos reservados.</p>
                <p class="footer-copy mb-0"><a href="privacidade.html">Política de privacidade</a> · Cumura · Biombo · Guiné-Bissau</p>
            </div>
        </div>
    </footer>

    <div class="float-actions">
        <a id="whatsappBtn" class="whatsapp-float" href="<?= seo_e($waUrl) ?>" target="_blank" rel="noopener noreferrer" aria-label="Contactar pelo WhatsApp" title="WhatsApp">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="26" height="26" aria-hidden="true" focusable="false">
                <path fill="currentColor" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/>
            </svg>
        </a>
        <button id="backToTopBtn" type="button" title="Voltar ao topo" aria-label="Voltar ao topo">↑</button>
    </div>

    <div class="gallery-lightbox" id="galleryLightbox" hidden>
        <div class="gallery-lightbox-backdrop" data-gallery-close></div>
        <div class="gallery-lightbox-dialog" role="dialog" aria-modal="true" aria-labelledby="galleryLightboxTitle">
            <button type="button" class="gallery-lightbox-close" data-gallery-close aria-label="Fechar">×</button>
            <button type="button" class="gallery-lightbox-nav gallery-lightbox-prev" id="galleryLightboxPrev" aria-label="Anterior">‹</button>
            <button type="button" class="gallery-lightbox-nav gallery-lightbox-next" id="galleryLightboxNext" aria-label="Seguinte">›</button>
            <figure class="gallery-lightbox-figure">
                <img src="" alt="" id="galleryLightboxImg">
                <figcaption class="gallery-lightbox-caption">
                    <h2 id="galleryLightboxTitle"></h2>
                    <p id="galleryLightboxDesc"></p>
                    <p class="gallery-lightbox-counter" id="galleryLightboxCounter"></p>
                </figcaption>
            </figure>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
    window.SITE_GALLERY = <?= json_encode(array_map(static function ($item) {
        return [
            'image' => $item['image'] ?? '',
            'title' => $item['title'] ?? '',
            'description' => $item['description'] ?? '',
        ];
    }, $items), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    </script>
    <script src="script.js?v=20260921gal"></script>
</body>
</html>
