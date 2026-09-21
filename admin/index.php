<?php
require_once __DIR__ . '/auth.php';
require_once dirname(__DIR__) . '/includes/content.php';
admin_require_login();

$tab = $_GET['tab'] ?? 'seo';
$allowedTabs = ['seo', 'servicos', 'noticias'];
if (!in_array($tab, $allowedTabs, true)) {
    $tab = 'seo';
}

$message = '';
$error = '';
$seo = seo_load();
$services = services_load();
$news = news_load();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!admin_verify_csrf($_POST['csrf'] ?? null)) {
        $error = 'Sessão inválida. Recarregue a página.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'save_seo') {
            if (seo_save($_POST)) {
                $seo = seo_load();
                admin_sync_static_files($seo);
                $message = 'SEO e contactos guardados.';
                $tab = 'seo';
            } else {
                $error = 'Não foi possível guardar o SEO.';
            }
        }

        if ($action === 'save_services') {
            $items = [];
            $count = (int) ($_POST['service_count'] ?? 0);
            for ($i = 0; $i < $count; $i++) {
                $items[] = [
                    'icon' => $_POST["service_icon_$i"] ?? '',
                    'title' => $_POST["service_title_$i"] ?? '',
                    'details' => $_POST["service_details_$i"] ?? '',
                    'days' => $_POST["service_days_$i"] ?? [],
                    'daily_limit' => !empty($_POST["service_unlimited_$i"])
                        ? 0
                        : ($_POST["service_limit_$i"] ?? 10),
                ];
            }
            if (services_save_all($items)) {
                $services = services_load();
                $message = 'Serviços guardados.';
                $tab = 'servicos';
            } else {
                $error = 'Não foi possível guardar os serviços.';
                $tab = 'servicos';
            }
        }

        if ($action === 'save_news') {
            $items = [];
            $count = (int) ($_POST['news_count'] ?? 0);
            $uploadErrors = [];
            for ($i = 0; $i < $count; $i++) {
                $paths = [];
                foreach (preg_split('/\r\n|\r|\n/', (string) ($_POST["news_images_$i"] ?? '')) ?: [] as $line) {
                    $line = trim($line);
                    if ($line !== '') {
                        $paths[] = $line;
                    }
                }

                $fileKey = "news_files_$i";
                if (!empty($_FILES[$fileKey]['name'])) {
                    $uploads = [];
                    if (is_array($_FILES[$fileKey]['name'])) {
                        foreach ($_FILES[$fileKey]['name'] as $fi => $fname) {
                            $uploads[] = [
                                'name' => $fname,
                                'type' => $_FILES[$fileKey]['type'][$fi] ?? '',
                                'tmp_name' => $_FILES[$fileKey]['tmp_name'][$fi] ?? '',
                                'error' => $_FILES[$fileKey]['error'][$fi] ?? UPLOAD_ERR_NO_FILE,
                                'size' => $_FILES[$fileKey]['size'][$fi] ?? 0,
                            ];
                        }
                    } else {
                        $uploads[] = $_FILES[$fileKey];
                    }

                    foreach ($uploads as $upload) {
                        if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                            continue;
                        }
                        $result = image_upload($upload);
                        if ($result['ok']) {
                            $paths[] = $result['path'];
                        } else {
                            $uploadErrors[] = 'Notícia ' . ($i + 1) . ': ' . $result['message'];
                        }
                    }
                }

                $paths = array_values(array_unique($paths));
                $items[] = [
                    'id' => $_POST["news_id_$i"] ?? '',
                    'featured' => !empty($_POST["news_featured_$i"]),
                    'date' => $_POST["news_date_$i"] ?? '',
                    'date_iso' => $_POST["news_date_iso_$i"] ?? '',
                    'tag' => $_POST["news_tag_$i"] ?? '',
                    'title' => $_POST["news_title_$i"] ?? '',
                    'excerpt' => $_POST["news_excerpt_$i"] ?? '',
                    'body' => $_POST["news_body_$i"] ?? '',
                    'images' => $paths,
                ];
            }
            if ($uploadErrors && !$items) {
                $error = implode(' ', $uploadErrors);
                $tab = 'noticias';
            } elseif (news_save_all($items)) {
                $news = news_load();
                $message = 'Notícias guardadas.' . ($uploadErrors ? ' Avisos: ' . implode(' ', $uploadErrors) : '');
                $tab = 'noticias';
            } else {
                $error = 'Não foi possível guardar as notícias.';
                $tab = 'noticias';
            }
        }
    }
}

$csrf = admin_csrf_token();

function e(?string $v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function field_input(string $name, string $value, string $label, string $type = 'text', string $hint = ''): void
{
    echo '<div class="field">';
    echo '<label for="' . e($name) . '">' . e($label) . '</label>';
    if ($type === 'textarea') {
        echo '<textarea id="' . e($name) . '" name="' . e($name) . '" rows="3">' . e($value) . '</textarea>';
    } else {
        echo '<input type="' . e($type) . '" id="' . e($name) . '" name="' . e($name) . '" value="' . e($value) . '">';
    }
    if ($hint !== '') {
        echo '<p class="hint">' . e($hint) . '</p>';
    }
    echo '</div>';
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="robots" content="noindex,nofollow">
    <title>Admin · HMH Cumura</title>
    <link rel="icon" href="../favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="admin.css?v=20260921w">
</head>
<body>
    <header class="admin-top">
        <div class="admin-top-inner">
            <div>
                <p class="eyebrow">Painel HMH Cumura</p>
                <h1>Gestão do site</h1>
            </div>
            <div class="admin-actions">
                <a class="btn ghost" href="../index.php" target="_blank" rel="noopener">Ver site</a>
                <a class="btn ghost" href="logout.php">Sair</a>
            </div>
        </div>
    </header>

    <nav class="admin-tabs" aria-label="Secções do painel">
        <a class="tab <?= $tab === 'seo' ? 'active' : '' ?>" href="?tab=seo">SEO</a>
        <a class="tab <?= $tab === 'servicos' ? 'active' : '' ?>" href="?tab=servicos">Serviços</a>
        <a class="tab <?= $tab === 'noticias' ? 'active' : '' ?>" href="?tab=noticias">Notícias</a>
    </nav>

    <main class="admin-wrap">
        <?php if ($message): ?><p class="alert ok"><?= e($message) ?></p><?php endif; ?>
        <?php if ($error): ?><p class="alert"><?= e($error) ?></p><?php endif; ?>

        <?php if ($tab === 'seo'): ?>
        <?php
        $seoSections = [
            [
                'id' => 'geral',
                'label' => 'Geral / SEO',
                'summary' => $seo['site_short_name'] !== '' ? $seo['site_short_name'] : ($seo['site_name'] ?: 'Dados gerais do site'),
                'fields' => function () use ($seo) {
                    field_input('site_name', $seo['site_name'], 'Nome da instituição');
                    field_input('site_short_name', $seo['site_short_name'], 'Nome curto');
                    field_input('title', $seo['title'], 'Title (browser)');
                    field_input('description', $seo['description'], 'Meta description', 'textarea');
                    field_input('canonical_url', $seo['canonical_url'], 'URL canónica', 'url');
                    field_input('robots', $seo['robots'], 'Robots');
                    field_input('theme_color', $seo['theme_color'], 'Cor do tema');
                },
            ],
            [
                'id' => 'redes',
                'label' => 'Open Graph / redes',
                'summary' => $seo['og_title'] !== '' ? $seo['og_title'] : 'Títulos e imagens para redes sociais',
                'fields' => function () use ($seo) {
                    field_input('og_title', $seo['og_title'], 'OG Title');
                    field_input('og_description', $seo['og_description'], 'OG Description', 'textarea');
                    field_input('og_image', $seo['og_image'], 'OG Image (URL absoluta)', 'url');
                    field_input('og_locale', $seo['og_locale'], 'OG Locale');
                    field_input('twitter_title', $seo['twitter_title'], 'Twitter Title');
                    field_input('twitter_description', $seo['twitter_description'], 'Twitter Description', 'textarea');
                    field_input('twitter_image', $seo['twitter_image'], 'Twitter Image', 'url');
                },
            ],
            [
                'id' => 'contactos',
                'label' => 'Contactos no site',
                'summary' => $seo['phone'] !== '' ? $seo['phone'] . ($seo['email'] !== '' ? ' · ' . $seo['email'] : '') : 'Telefone, e-mail e morada',
                'fields' => function () use ($seo) {
                    field_input('phone', $seo['phone'], 'Telefone visível');
                    field_input('phone_tel', $seo['phone_tel'], 'Telefone tel:/schema');
                    field_input('email', $seo['email'], 'E-mail', 'email');
                    field_input('whatsapp', $seo['whatsapp'], 'WhatsApp (sem +)');
                    field_input('opening_hours_label', $seo['opening_hours_label'], 'Horário (texto)');
                    field_input('opening_hours', $seo['opening_hours'], 'Horário schema');
                    field_input('address_locality', $seo['address_locality'], 'Localidade');
                    field_input('address_region', $seo['address_region'], 'Região');
                    field_input('address_country', $seo['address_country'], 'País ISO');
                    field_input('schema_description', $seo['schema_description'], 'Descrição JSON-LD', 'textarea');
                },
            ],
        ];
        ?>
        <form method="post" class="admin-form" id="seoForm">
            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="action" value="save_seo">
            <section class="panel slides-panel">
                <div class="panel-head">
                    <h2>SEO e contactos</h2>
                </div>
                <p class="hint" style="margin-top:0">Lista de secções. Use <strong>Editar</strong> para abrir os campos.</p>
                <ul class="item-list" id="seoWrap">
                    <?php foreach ($seoSections as $section): ?>
                    <li class="list-item seo-card" data-section="<?= e($section['id']) ?>">
                        <div class="list-row seo-row">
                            <div class="list-icon" aria-hidden="true"><?= $section['id'] === 'geral' ? '◈' : ($section['id'] === 'redes' ? '◎' : '☎') ?></div>
                            <div class="list-info">
                                <strong class="list-label"><?= e($section['label']) ?></strong>
                                <span class="list-title"><?= e($section['summary']) ?></span>
                            </div>
                            <div class="list-actions">
                                <button type="button" class="btn action btn-edit-seo" title="Editar">Editar</button>
                            </div>
                        </div>
                        <div class="list-editor" hidden>
                            <?php ($section['fields'])(); ?>
                            <div class="editor-footer">
                                <button type="button" class="btn ghost-dark btn-close-seo">Fechar edição</button>
                            </div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </section>
            <div class="form-footer row-actions">
                <button type="submit" class="btn primary">Guardar SEO</button>
            </div>
        </form>
        <?php endif; ?>

        <?php if ($tab === 'servicos'): ?>
        <form method="post" class="admin-form" id="servicesForm">
            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="action" value="save_services">
            <section class="panel slides-panel">
                <div class="panel-head">
                    <h2>Serviços</h2>
                    <button type="button" class="btn ghost-dark" id="addServiceBtn">+ Adicionar</button>
                </div>
                <p class="hint" style="margin-top:0">Defina o ícone da roda, os <strong>dias de consulta</strong> e o <strong>limite diário</strong> de cada serviço.</p>
                <ul class="item-list" id="servicesWrap">
                <?php
                $serviceItems = $services ?: [['icon' => '', 'title' => '', 'details' => '', 'days' => ['mon', 'tue', 'wed', 'thu', 'fri'], 'daily_limit' => 10]];
                $weekLabels = seo_weekday_labels();
                foreach ($serviceItems as $i => $svc):
                    $svcTitle = $svc['title'] ?? '';
                    $svcIcon = $svc['icon'] ?? '';
                    $svcDays = $svc['days'] ?? ['mon', 'tue', 'wed', 'thu', 'fri'];
                    $svcLimit = (int) ($svc['daily_limit'] ?? 10);
                    $svcMeta = seo_days_label($svcDays) . ' · ' . ($svcLimit === 0 ? 'sem limite' : ($svcLimit . ' / dia'));
                ?>
                <li class="list-item service-card" data-index="<?= (int) $i ?>">
                    <div class="list-row seo-row">
                        <div class="list-icon service-icon-preview" aria-hidden="true"><?= $svcIcon !== '' ? e($svcIcon) : ((int) $i + 1) ?></div>
                        <div class="list-info">
                            <strong class="list-label">Serviço <span class="service-num"><?= (int) $i + 1 ?></span></strong>
                            <span class="list-title"><?= e($svcTitle !== '' ? $svcTitle : 'Sem título') ?></span>
                            <span class="list-meta"><?= e($svcMeta) ?></span>
                        </div>
                        <div class="list-actions">
                            <button type="button" class="btn action btn-edit-service" title="Editar">Editar</button>
                            <button type="button" class="btn action btn-move-up-service" title="Subir">↑</button>
                            <button type="button" class="btn action btn-move-down-service" title="Descer">↓</button>
                            <button type="button" class="btn action danger btn-remove-service" title="Remover">Remover</button>
                        </div>
                    </div>
                    <div class="list-editor" hidden>
                        <div class="grid-2">
                            <div class="field">
                                <label>Ícone da roda (emoji)</label>
                                <input type="text" name="service_icon_<?= (int) $i ?>" class="service-icon-input" value="<?= e($svcIcon) ?>" maxlength="8" placeholder="🩺">
                            </div>
                            <div class="field">
                                <label>Título</label>
                                <input type="text" name="service_title_<?= (int) $i ?>" class="service-title-input" value="<?= e($svcTitle) ?>">
                            </div>
                        </div>
                        <div class="field">
                            <label>Descrição</label>
                            <textarea name="service_details_<?= (int) $i ?>" rows="3"><?= e($svc['details'] ?? '') ?></textarea>
                        </div>
                        <div class="field">
                            <label>Dias de consulta</label>
                            <p class="hint">Marque os dias em que este serviço está disponível.</p>
                            <div class="days-check-grid">
                                <?php foreach ($weekLabels as $key => $label):
                                    $id = 'service_day_' . (int) $i . '_' . $key;
                                    $checked = in_array($key, $svcDays, true) ? ' checked' : '';
                                ?>
                                <label class="check-line day-check" for="<?= e($id) ?>">
                                    <input type="checkbox" id="<?= e($id) ?>" name="service_days_<?= (int) $i ?>[]" value="<?= e($key) ?>"<?= $checked ?>>
                                    <?= e($label) ?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="field">
                            <label>Limite diário de atendimentos</label>
                            <label class="check-line" for="service_unlimited_<?= (int) $i ?>">
                                <input type="checkbox" id="service_unlimited_<?= (int) $i ?>" name="service_unlimited_<?= (int) $i ?>" value="1" class="service-unlimited-input"<?= $svcLimit === 0 ? ' checked' : '' ?>>
                                Sem limite diário
                            </label>
                            <input type="number" name="service_limit_<?= (int) $i ?>" class="service-limit-input" value="<?= $svcLimit === 0 ? 10 : $svcLimit ?>" min="1" max="500" step="1"<?= $svcLimit === 0 ? ' disabled' : ' required' ?>>
                            <p class="hint">Se não marcar “Sem limite”, defina o máximo de marcações por dia. Dias cheios ficam indisponíveis no calendário.</p>
                        </div>
                        <div class="editor-footer">
                            <button type="button" class="btn ghost-dark btn-close-service">Fechar edição</button>
                        </div>
                    </div>
                </li>
                <?php endforeach; ?>
                </ul>
            </section>
            <input type="hidden" name="service_count" id="service_count" value="<?= count($serviceItems) ?>">
            <div class="form-footer row-actions">
                <button type="submit" class="btn primary">Guardar serviços</button>
            </div>
        </form>
        <?php endif; ?>

        <?php if ($tab === 'noticias'): ?>
        <form method="post" class="admin-form" id="newsForm" enctype="multipart/form-data">
            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="action" value="save_news">
            <section class="panel slides-panel">
                <div class="panel-head">
                    <h2>Notícias</h2>
                    <button type="button" class="btn ghost-dark" id="addNewsBtn">+ Adicionar</button>
                </div>
                <p class="hint" style="margin-top:0">Lista de notícias. Use as acções para editar, reordenar ou remover. Marque uma como destaque.</p>
                <ul class="item-list" id="newsWrap">
                <?php
                $newsItems = $news ?: [[
                    'id' => '', 'featured' => true, 'date' => '', 'date_iso' => '', 'tag' => '',
                    'title' => '', 'excerpt' => '', 'paragraphs' => [], 'images' => [],
                ]];
                foreach ($newsItems as $i => $item):
                    $body = implode("\n\n", $item['paragraphs'] ?? []);
                    $imagesArr = $item['images'] ?? [];
                    $imagesText = implode("\n", $imagesArr);
                    $cover = $imagesArr[0] ?? '';
                    $title = $item['title'] ?? '';
                    $tag = $item['tag'] ?? '';
                ?>
                <li class="list-item news-card" data-index="<?= (int) $i ?>">
                    <div class="list-row">
                        <div class="list-thumb<?= $cover === '' ? ' is-empty' : '' ?>">
                            <?php if ($cover !== ''): ?>
                            <img src="../<?= e($cover) ?>" alt="" class="list-thumb-img news-preview-img">
                            <?php else: ?>
                            <span class="list-thumb-empty">—</span>
                            <img src="" alt="" class="list-thumb-img news-preview-img" hidden>
                            <?php endif; ?>
                        </div>
                        <div class="list-info">
                            <strong class="list-label">
                                Notícia <span class="news-num"><?= (int) $i + 1 ?></span>
                                <?php if (!empty($item['featured'])): ?><span class="badge-feature">Destaque</span><?php endif; ?>
                            </strong>
                            <span class="list-title"><?= e($title !== '' ? $title : 'Sem título') ?></span>
                            <span class="list-meta"><?= e($tag !== '' ? $tag . ' · ' : '') ?><?= count($imagesArr) ?> imagem<?= count($imagesArr) === 1 ? '' : 'ns' ?></span>
                        </div>
                        <div class="list-actions">
                            <button type="button" class="btn action btn-edit-news" title="Editar">Editar</button>
                            <button type="button" class="btn action btn-move-up-news" title="Subir">↑</button>
                            <button type="button" class="btn action btn-move-down-news" title="Descer">↓</button>
                            <button type="button" class="btn action danger btn-remove-news" title="Remover">Remover</button>
                        </div>
                    </div>
                    <div class="list-editor" hidden>
                        <input type="hidden" name="news_id_<?= (int) $i ?>" value="<?= e($item['id'] ?? '') ?>">
                        <label class="check-line">
                            <input type="checkbox" name="news_featured_<?= (int) $i ?>" value="1" class="news-featured-input" <?= !empty($item['featured']) ? 'checked' : '' ?>>
                            Destaque principal
                        </label>
                        <div class="grid-2">
                            <div class="field"><label>Tag</label><input type="text" name="news_tag_<?= (int) $i ?>" value="<?= e($tag) ?>" placeholder="Missão"></div>
                            <div class="field"><label>Data (texto)</label><input type="text" name="news_date_<?= (int) $i ?>" value="<?= e($item['date'] ?? '') ?>" placeholder="08 de Julho, 2026"></div>
                            <div class="field"><label>Data ISO</label><input type="date" name="news_date_iso_<?= (int) $i ?>" value="<?= e($item['date_iso'] ?? '') ?>"></div>
                        </div>
                        <div class="field"><label>Título</label><input type="text" name="news_title_<?= (int) $i ?>" class="news-title-input" value="<?= e($title) ?>"></div>
                        <div class="field"><label>Resumo</label><textarea name="news_excerpt_<?= (int) $i ?>" rows="2"><?= e($item['excerpt'] ?? '') ?></textarea></div>
                        <div class="field"><label>Texto completo</label><textarea name="news_body_<?= (int) $i ?>" rows="5"><?= e($body) ?></textarea><p class="hint">Separe parágrafos com uma linha em branco.</p></div>

                        <div class="news-gallery-block">
                            <label>Imagens da notícia</label>
                            <p class="hint">Pode ter várias fotos. A primeira é a capa no site.</p>
                            <div class="news-gallery-grid">
                                <?php foreach ($imagesArr as $imgPath): ?>
                                <div class="news-gallery-item" data-path="<?= e($imgPath) ?>">
                                    <img src="../<?= e($imgPath) ?>" alt="">
                                    <button type="button" class="btn-gallery-remove" title="Remover da notícia" aria-label="Remover">×</button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" name="news_images_<?= (int) $i ?>" class="news-images-input" value="<?= e($imagesText) ?>">
                            <div class="field" style="margin-top:0.75rem">
                                <label>Carregar imagens</label>
                                <input type="file" name="news_files_<?= (int) $i ?>[]" class="news-files-input" accept="image/jpeg,image/png,image/webp,image/gif" multiple>
                                <p class="hint">Seleccione uma ou várias · JPG, PNG, WebP, GIF · máx. 5MB cada</p>
                            </div>
                            <div class="news-gallery-pending" hidden></div>
                        </div>

                        <div class="editor-footer">
                            <button type="button" class="btn ghost-dark btn-close-news">Fechar edição</button>
                        </div>
                    </div>
                </li>
                <?php endforeach; ?>
                </ul>
            </section>
            <input type="hidden" name="news_count" id="news_count" value="<?= count($newsItems) ?>">
            <div class="form-footer row-actions">
                <button type="submit" class="btn primary">Guardar notícias</button>
            </div>
        </form>
        <?php endif; ?>
    </main>

    <template id="serviceTemplate">
        <li class="list-item service-card is-open" data-index="__I__">
            <div class="list-row seo-row">
                <div class="list-icon service-icon-preview" aria-hidden="true">__N__</div>
                <div class="list-info">
                    <strong class="list-label">Serviço <span class="service-num">__N__</span></strong>
                    <span class="list-title">Sem título</span>
                    <span class="list-meta">Defina os dias · sem limite</span>
                </div>
                <div class="list-actions">
                    <button type="button" class="btn action btn-edit-service" title="Editar">Editar</button>
                    <button type="button" class="btn action btn-move-up-service" title="Subir">↑</button>
                    <button type="button" class="btn action btn-move-down-service" title="Descer">↓</button>
                    <button type="button" class="btn action danger btn-remove-service" title="Remover">Remover</button>
                </div>
            </div>
            <div class="list-editor">
                <div class="grid-2">
                    <div class="field">
                        <label>Ícone da roda (emoji)</label>
                        <input type="text" name="service_icon___I__" class="service-icon-input" value="" maxlength="8" placeholder="🩺">
                    </div>
                    <div class="field">
                        <label>Título</label>
                        <input type="text" name="service_title___I__" class="service-title-input" value="">
                    </div>
                </div>
                <div class="field">
                    <label>Descrição</label>
                    <textarea name="service_details___I__" rows="3"></textarea>
                </div>
                <div class="field">
                    <label>Dias de consulta</label>
                    <p class="hint">Marque os dias em que este serviço está disponível.</p>
                    <div class="days-check-grid">
                        <label class="check-line day-check"><input type="checkbox" name="service_days___I__[]" value="mon" checked> Segunda-feira</label>
                        <label class="check-line day-check"><input type="checkbox" name="service_days___I__[]" value="tue" checked> Terça-feira</label>
                        <label class="check-line day-check"><input type="checkbox" name="service_days___I__[]" value="wed" checked> Quarta-feira</label>
                        <label class="check-line day-check"><input type="checkbox" name="service_days___I__[]" value="thu" checked> Quinta-feira</label>
                        <label class="check-line day-check"><input type="checkbox" name="service_days___I__[]" value="fri" checked> Sexta-feira</label>
                        <label class="check-line day-check"><input type="checkbox" name="service_days___I__[]" value="sat"> Sábado</label>
                        <label class="check-line day-check"><input type="checkbox" name="service_days___I__[]" value="sun"> Domingo</label>
                    </div>
                </div>
                <div class="field">
                    <label>Limite diário de atendimentos</label>
                    <label class="check-line" for="service_unlimited___I__">
                        <input type="checkbox" id="service_unlimited___I__" name="service_unlimited___I__" value="1" class="service-unlimited-input" checked>
                        Sem limite diário
                    </label>
                    <input type="number" name="service_limit___I__" class="service-limit-input" value="10" min="1" max="500" step="1" disabled>
                    <p class="hint">Se não marcar “Sem limite”, defina o máximo de marcações por dia. Dias cheios ficam indisponíveis no calendário.</p>
                </div>
                <div class="editor-footer">
                    <button type="button" class="btn ghost-dark btn-close-service">Fechar edição</button>
                </div>
            </div>
        </li>
    </template>

    <template id="newsTemplate">
        <li class="list-item news-card is-open" data-index="__I__">
            <div class="list-row">
                <div class="list-thumb is-empty">
                    <span class="list-thumb-empty">—</span>
                    <img src="" alt="" class="list-thumb-img news-preview-img" hidden>
                </div>
                <div class="list-info">
                    <strong class="list-label">Notícia <span class="news-num">__N__</span></strong>
                    <span class="list-title">Sem título</span>
                </div>
                <div class="list-actions">
                    <button type="button" class="btn action btn-edit-news" title="Editar">Editar</button>
                    <button type="button" class="btn action btn-move-up-news" title="Subir">↑</button>
                    <button type="button" class="btn action btn-move-down-news" title="Descer">↓</button>
                    <button type="button" class="btn action danger btn-remove-news" title="Remover">Remover</button>
                </div>
            </div>
            <div class="list-editor">
                <input type="hidden" name="news_id___I__" value="">
                <label class="check-line"><input type="checkbox" name="news_featured___I__" value="1" class="news-featured-input"> Destaque principal</label>
                <div class="grid-2">
                    <div class="field"><label>Tag</label><input type="text" name="news_tag___I__" value=""></div>
                    <div class="field"><label>Data (texto)</label><input type="text" name="news_date___I__" value=""></div>
                    <div class="field"><label>Data ISO</label><input type="date" name="news_date_iso___I__" value=""></div>
                </div>
                <div class="field"><label>Título</label><input type="text" name="news_title___I__" class="news-title-input" value=""></div>
                <div class="field"><label>Resumo</label><textarea name="news_excerpt___I__" rows="2"></textarea></div>
                <div class="field"><label>Texto completo</label><textarea name="news_body___I__" rows="5"></textarea></div>
                <div class="news-gallery-block">
                    <label>Imagens da notícia</label>
                    <p class="hint">Pode ter várias fotos. A primeira é a capa no site.</p>
                    <div class="news-gallery-grid"></div>
                    <input type="hidden" name="news_images___I__" class="news-images-input" value="">
                    <div class="field" style="margin-top:0.75rem">
                        <label>Carregar imagens</label>
                        <input type="file" name="news_files___I__[]" class="news-files-input" accept="image/jpeg,image/png,image/webp,image/gif" multiple>
                        <p class="hint">Seleccione uma ou várias · JPG, PNG, WebP, GIF · máx. 5MB cada</p>
                    </div>
                    <div class="news-gallery-pending" hidden></div>
                </div>
                <div class="editor-footer">
                    <button type="button" class="btn ghost-dark btn-close-news">Fechar edição</button>
                </div>
            </div>
        </li>
    </template>

    <script src="admin.js?v=20260921x"></script>
</body>
</html>
