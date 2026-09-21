<?php

require_once __DIR__ . '/seo.php';

function content_root(): string
{
    return dirname(__DIR__);
}

function content_json_read(string $file, $default)
{
    $path = content_root() . '/data/' . $file;
    if (!is_file($path)) {
        return $default;
    }
    $data = json_decode((string) file_get_contents($path), true);
    return $data === null ? $default : $data;
}

function content_json_write(string $file, $data): bool
{
    $dir = content_root() . '/data';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return (bool) file_put_contents($dir . '/' . $file, $json . "\n", LOCK_EX);
}

function content_clean(string $value, int $max = 500): string
{
    $value = trim(str_replace(["\0"], '', $value));
    return mb_substr($value, 0, $max);
}

function hero_defaults(): array
{
    return [
        'brand' => 'Hospital do Mal de Hansen de Cumura',
        'slides' => [],
    ];
}

function hero_load(): array
{
    $data = content_json_read('hero.json', hero_defaults());
    if (!isset($data['slides']) || !is_array($data['slides'])) {
        $data['slides'] = [];
    }
    $data['brand'] = content_clean((string) ($data['brand'] ?? ''), 160);
    return $data;
}

function hero_save(array $input): bool
{
    $slides = [];
    $rawSlides = $input['slides'] ?? [];
    if (!is_array($rawSlides)) {
        $rawSlides = [];
    }

    foreach ($rawSlides as $slide) {
        if (!is_array($slide)) {
            continue;
        }
        $title = content_clean((string) ($slide['title'] ?? ''), 180);
        $image = content_clean((string) ($slide['image'] ?? ''), 260);
        if ($title === '' && $image === '') {
            continue;
        }
        $slides[] = [
            'image' => $image,
            'title' => $title,
            'text' => content_clean((string) ($slide['text'] ?? ''), 320),
            'btn1_label' => content_clean((string) ($slide['btn1_label'] ?? ''), 80),
            'btn1_href' => content_clean((string) ($slide['btn1_href'] ?? '#contato'), 120),
            'btn2_label' => content_clean((string) ($slide['btn2_label'] ?? ''), 80),
            'btn2_href' => content_clean((string) ($slide['btn2_href'] ?? '#sobre'), 120),
            'btn1_assunto' => content_clean((string) ($slide['btn1_assunto'] ?? ''), 80),
        ];
    }

    return content_json_write('hero.json', [
        'brand' => content_clean((string) ($input['brand'] ?? ''), 160),
        'slides' => $slides,
    ]);
}

function news_load(): array
{
    $data = content_json_read('news.json', []);
    return is_array($data) ? array_values($data) : [];
}

function news_save_all(array $items): bool
{
    $clean = [];
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $title = content_clean((string) ($item['title'] ?? ''), 200);
        if ($title === '') {
            continue;
        }

        $images = [];
        if (!empty($item['images']) && is_array($item['images'])) {
            foreach ($item['images'] as $img) {
                $img = content_clean((string) $img, 260);
                if ($img !== '') {
                    $images[] = $img;
                }
            }
        } elseif (!empty($item['images_text'])) {
            foreach (preg_split('/\r\n|\r|\n/', (string) $item['images_text']) as $line) {
                $line = content_clean($line, 260);
                if ($line !== '') {
                    $images[] = $line;
                }
            }
        }

        $paragraphs = [];
        if (!empty($item['paragraphs']) && is_array($item['paragraphs'])) {
            foreach ($item['paragraphs'] as $p) {
                $p = trim(str_replace("\0", '', (string) $p));
                if ($p !== '') {
                    $paragraphs[] = mb_substr($p, 0, 2000);
                }
            }
        } elseif (!empty($item['body'])) {
            foreach (preg_split('/\n{2,}/', trim((string) $item['body'])) as $p) {
                $p = trim(str_replace("\0", '', $p));
                if ($p !== '') {
                    $paragraphs[] = mb_substr($p, 0, 2000);
                }
            }
        }

        $id = content_clean((string) ($item['id'] ?? ''), 40);
        if ($id === '') {
            $id = 'n' . substr(bin2hex(random_bytes(4)), 0, 8);
        }

        $clean[] = [
            'id' => $id,
            'featured' => !empty($item['featured']),
            'date' => content_clean((string) ($item['date'] ?? ''), 60),
            'date_iso' => content_clean((string) ($item['date_iso'] ?? ''), 20),
            'tag' => content_clean((string) ($item['tag'] ?? ''), 40),
            'title' => $title,
            'excerpt' => content_clean((string) ($item['excerpt'] ?? ''), 320),
            'paragraphs' => $paragraphs,
            'images' => array_values(array_unique($images)),
        ];
    }

    // Garantir no máximo 1 featured; se nenhum, o primeiro fica featured
    $hasFeatured = false;
    foreach ($clean as $i => $row) {
        if (!empty($row['featured'])) {
            if ($hasFeatured) {
                $clean[$i]['featured'] = false;
            } else {
                $hasFeatured = true;
            }
        }
    }
    if (!$hasFeatured && $clean) {
        $clean[0]['featured'] = true;
    }

    return content_json_write('news.json', $clean);
}

function images_list(): array
{
    $dir = content_root() . '/images';
    if (!is_dir($dir)) {
        return [];
    }
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $files = [];
    foreach (scandir($dir) ?: [] as $name) {
        if ($name === '.' || $name === '..') {
            continue;
        }
        $path = $dir . '/' . $name;
        if (!is_file($path)) {
            continue;
        }
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) {
            continue;
        }
        $files[] = [
            'name' => $name,
            'path' => 'images/' . $name,
            'size' => filesize($path) ?: 0,
            'mtime' => filemtime($path) ?: 0,
        ];
    }
    usort($files, static fn($a, $b) => $b['mtime'] <=> $a['mtime']);
    return $files;
}

function image_upload(array $file): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'message' => 'Falha no upload do ficheiro.'];
    }
    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        return ['ok' => false, 'message' => 'Imagem demasiado grande (máx. 5MB).'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $map = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];
    if (!isset($map[$mime])) {
        return ['ok' => false, 'message' => 'Tipo de ficheiro não permitido.'];
    }

    $base = preg_replace('/[^a-zA-Z0-9_-]+/', '-', pathinfo($file['name'], PATHINFO_FILENAME));
    $base = trim($base, '-') ?: 'imagem';
    $name = strtolower($base) . '-' . date('YmdHis') . '.' . $map[$mime];
    $destDir = content_root() . '/images';
    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }
    $dest = $destDir . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return ['ok' => false, 'message' => 'Não foi possível guardar a imagem.'];
    }
    return ['ok' => true, 'path' => 'images/' . $name, 'message' => 'Imagem carregada.'];
}

function image_delete(string $relative): array
{
    $relative = str_replace('\\', '/', $relative);
    if (!preg_match('#^images/[a-zA-Z0-9._-]+$#', $relative)) {
        return ['ok' => false, 'message' => 'Caminho inválido.'];
    }
    $path = content_root() . '/' . $relative;
    if (!is_file($path)) {
        return ['ok' => false, 'message' => 'Ficheiro não encontrado.'];
    }
    if (!unlink($path)) {
        return ['ok' => false, 'message' => 'Não foi possível apagar.'];
    }
    return ['ok' => true, 'message' => 'Imagem apagada.'];
}

function admin_sync_static_files(array $seo): void
{
    $base = rtrim($seo['canonical_url'], '/');
    $root = content_root();

    $robots = "User-agent: *\nAllow: /\n\nSitemap: {$base}/sitemap.xml\n\nDisallow: /mensagens/\nDisallow: /enviar-email.php\nDisallow: /admin/\nDisallow: /config/\nDisallow: /data/\nDisallow: /includes/\n";
    @file_put_contents($root . '/robots.txt', $robots);

    $sitemap = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>{$base}/</loc>
    <changefreq>weekly</changefreq>
    <priority>1.0</priority>
  </url>
  <url>
    <loc>{$base}/privacidade.html</loc>
    <changefreq>yearly</changefreq>
    <priority>0.3</priority>
  </url>
</urlset>

XML;
    @file_put_contents($root . '/sitemap.xml', $sitemap);
}

function news_for_js(array $news): array
{
    $out = [];
    foreach ($news as $item) {
        $out[] = [
            'date' => $item['date'] ?? '',
            'title' => $item['title'] ?? '',
            'images' => $item['images'] ?? [],
            'paragraphs' => $item['paragraphs'] ?? [],
        ];
    }
    return $out;
}

function services_defaults(): array
{
    return [
        [
            'icon' => '🩺',
            'title' => 'Tratamento Clínico',
            'details' => 'Diagnóstico precoce, poliquimioterapia (PQT) e acompanhamento médico contínuo.',
            'days' => ['mon', 'tue', 'wed', 'thu', 'fri'],
            'daily_limit' => 10,
        ],
    ];
}

function services_normalize_limit($value): int
{
    $limit = (int) $value;
    if ($limit < 0) {
        return 0;
    }
    if ($limit > 500) {
        return 500;
    }
    return $limit;
}

function services_load(): array
{
    $data = content_json_read('services.json', services_defaults());
    if (!is_array($data)) {
        return services_defaults();
    }
    $fallbackDays = ['mon', 'tue', 'wed', 'thu', 'fri'];
    $out = [];
    foreach ($data as $item) {
        if (!is_array($item)) {
            continue;
        }
        $title = content_clean((string) ($item['title'] ?? ''), 120);
        if ($title === '') {
            continue;
        }
        $days = seo_normalize_days($item['days'] ?? []);
        if (!$days) {
            $days = $fallbackDays;
        }
        $out[] = [
            'icon' => content_clean((string) ($item['icon'] ?? ''), 16),
            'title' => $title,
            'details' => content_clean((string) ($item['details'] ?? ''), 500),
            'days' => $days,
            'daily_limit' => services_normalize_limit($item['daily_limit'] ?? 10),
        ];
    }
    return $out ?: services_defaults();
}

function services_find_by_title(string $title): ?array
{
    $title = trim($title);
    if ($title === '') {
        return null;
    }
    foreach (services_load() as $item) {
        if (($item['title'] ?? '') === $title) {
            return $item;
        }
    }
    return null;
}

function services_save_all(array $items): bool
{
    $clean = [];
    $fallbackDays = ['mon', 'tue', 'wed', 'thu', 'fri'];
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $title = content_clean((string) ($item['title'] ?? ''), 120);
        if ($title === '') {
            continue;
        }
        $days = seo_normalize_days($item['days'] ?? []);
        if (!$days) {
            $days = $fallbackDays;
        }
        $clean[] = [
            'icon' => content_clean((string) ($item['icon'] ?? ''), 16),
            'title' => $title,
            'details' => content_clean((string) ($item['details'] ?? ''), 500),
            'days' => $days,
            'daily_limit' => services_normalize_limit($item['daily_limit'] ?? 10),
        ];
    }
    return content_json_write('services.json', $clean);
}

function services_for_js(array $services): array
{
    $labels = seo_weekday_labels();
    $out = [];
    foreach ($services as $item) {
        $dayLabels = [];
        foreach (($item['days'] ?? []) as $key) {
            if (isset($labels[$key])) {
                $dayLabels[] = $labels[$key];
            }
        }
        $out[] = [
            'icon' => $item['icon'] ?? '',
            'title' => $item['title'] ?? '',
            'details' => $item['details'] ?? '',
            'days' => array_values($item['days'] ?? []),
            'day_labels' => $dayLabels,
            'daily_limit' => (int) ($item['daily_limit'] ?? 10),
        ];
    }
    return $out;
}

function bookings_path(): string
{
    return content_root() . '/data/bookings.json';
}

function bookings_is_valid_date(string $date): bool
{
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return false;
    }
    $dt = DateTime::createFromFormat('Y-m-d', $date);
    return $dt && $dt->format('Y-m-d') === $date;
}

function bookings_weekday_key(string $date): string
{
    $map = ['Sun' => 'sun', 'Mon' => 'mon', 'Tue' => 'tue', 'Wed' => 'wed', 'Thu' => 'thu', 'Fri' => 'fri', 'Sat' => 'sat'];
    $dt = DateTime::createFromFormat('Y-m-d', $date);
    if (!$dt) {
        return '';
    }
    return $map[$dt->format('D')] ?? '';
}

/**
 * @return array{ok:bool,message?:string,booked?:int,limit?:int,remaining?:int}
 */
function bookings_reserve(string $serviceTitle, string $date): array
{
    $service = services_find_by_title($serviceTitle);
    if (!$service) {
        return ['ok' => false, 'message' => 'Serviço inválido.'];
    }
    if (!bookings_is_valid_date($date)) {
        return ['ok' => false, 'message' => 'Data inválida.'];
    }
    $today = (new DateTime('today'))->format('Y-m-d');
    if ($date < $today) {
        return ['ok' => false, 'message' => 'Não é possível agendar em datas passadas.'];
    }
    $dayKey = bookings_weekday_key($date);
    if ($dayKey === '' || !in_array($dayKey, $service['days'] ?? [], true)) {
        return ['ok' => false, 'message' => 'Este serviço não está disponível neste dia.'];
    }

    $limit = services_normalize_limit($service['daily_limit'] ?? 10);
    $path = bookings_path();
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    // Sem limite diário: valida o dia, mas não conta ocupação
    if ($limit === 0) {
        return [
            'ok' => true,
            'booked' => 0,
            'limit' => 0,
            'remaining' => null,
            'unlimited' => true,
        ];
    }

    $fh = fopen($path, 'c+');
    if ($fh === false) {
        return ['ok' => false, 'message' => 'Não foi possível verificar a disponibilidade.'];
    }

    try {
        if (!flock($fh, LOCK_EX)) {
            return ['ok' => false, 'message' => 'Não foi possível verificar a disponibilidade.'];
        }

        $raw = stream_get_contents($fh);
        $data = is_string($raw) && $raw !== '' ? json_decode($raw, true) : [];
        if (!is_array($data)) {
            $data = [];
        }
        if (!isset($data[$serviceTitle]) || !is_array($data[$serviceTitle])) {
            $data[$serviceTitle] = [];
        }

        $booked = (int) ($data[$serviceTitle][$date] ?? 0);
        if ($booked >= $limit) {
            return [
                'ok' => false,
                'message' => 'Este dia já atingiu o limite de atendimentos. Escolha outra data.',
                'booked' => $booked,
                'limit' => $limit,
                'remaining' => 0,
            ];
        }

        $booked++;
        $data[$serviceTitle][$date] = $booked;
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        ftruncate($fh, 0);
        rewind($fh);
        fwrite($fh, $json . "\n");
        fflush($fh);

        return [
            'ok' => true,
            'booked' => $booked,
            'limit' => $limit,
            'remaining' => max(0, $limit - $booked),
        ];
    } finally {
        flock($fh, LOCK_UN);
        fclose($fh);
    }
}

/**
 * Contagens de um serviço num mês (YYYY-MM).
 * remaining = null significa sem limite diário.
 * @return array<string, array{booked:int,limit:int,remaining:int|null,unlimited:bool}>
 */
function bookings_month_availability(string $serviceTitle, int $year, int $month): array
{
    $service = services_find_by_title($serviceTitle);
    if (!$service) {
        return [];
    }
    $limit = services_normalize_limit($service['daily_limit'] ?? 10);
    $unlimited = $limit === 0;
    $path = bookings_path();
    $data = [];
    if (!$unlimited && is_file($path)) {
        $decoded = json_decode((string) file_get_contents($path), true);
        if (is_array($decoded) && isset($decoded[$serviceTitle]) && is_array($decoded[$serviceTitle])) {
            $data = $decoded[$serviceTitle];
        }
    }

    $out = [];
    $daysInMonth = (int) (new DateTime(sprintf('%04d-%02d-01', $year, $month)))->format('t');
    for ($d = 1; $d <= $daysInMonth; $d++) {
        $iso = sprintf('%04d-%02d-%02d', $year, $month, $d);
        $booked = $unlimited ? 0 : (int) ($data[$iso] ?? 0);
        $out[$iso] = [
            'booked' => $booked,
            'limit' => $limit,
            'remaining' => $unlimited ? null : max(0, $limit - $booked),
            'unlimited' => $unlimited,
        ];
    }
    return $out;
}
