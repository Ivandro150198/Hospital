<?php

function seo_data_path(): string
{
    return dirname(__DIR__) . '/data/seo.json';
}

function seo_defaults(): array
{
    return [
        'site_name' => 'Hospital do Mal de Hansen de Cumura',
        'site_short_name' => 'HMH Cumura',
        'title' => 'Hospital do Mal de Hansen de Cumura | HMH Cumura',
        'description' => '',
        'canonical_url' => 'https://hospitalcumura.org/',
        'theme_color' => '#1f5c45',
        'robots' => 'index,follow',
        'og_title' => '',
        'og_description' => '',
        'og_image' => '',
        'og_locale' => 'pt_GW',
        'twitter_title' => '',
        'twitter_description' => '',
        'twitter_image' => '',
        'phone' => '',
        'phone_tel' => '',
        'email' => '',
        'whatsapp' => '',
        'address_locality' => 'Cumura',
        'address_region' => 'Biombo',
        'address_country' => 'GW',
        'opening_hours' => 'Mo-Fr 08:00-16:00',
        'opening_hours_label' => 'Segunda a Sexta · 08h00 – 16h00',
        'schema_description' => '',
        'consultation_days' => ['mon', 'tue', 'wed', 'thu', 'fri'],
    ];
}

function seo_weekday_labels(): array
{
    return [
        'mon' => 'Segunda-feira',
        'tue' => 'Terça-feira',
        'wed' => 'Quarta-feira',
        'thu' => 'Quinta-feira',
        'fri' => 'Sexta-feira',
        'sat' => 'Sábado',
        'sun' => 'Domingo',
    ];
}

function seo_normalize_days($days): array
{
    $valid = array_keys(seo_weekday_labels());
    if (!is_array($days)) {
        $days = [];
    }
    $clean = [];
    foreach ($days as $day) {
        $day = strtolower(trim((string) $day));
        if (in_array($day, $valid, true) && !in_array($day, $clean, true)) {
            $clean[] = $day;
        }
    }
    // Manter ordem da semana
    return array_values(array_intersect($valid, $clean));
}

function seo_days_label(array $days): string
{
    $labels = seo_weekday_labels();
    $days = seo_normalize_days($days);
    if (!$days) {
        return 'Sem dias definidos';
    }
    $names = [];
    foreach ($days as $day) {
        $names[] = $labels[$day];
    }
    if (count($names) === 1) {
        return $names[0];
    }
    $last = array_pop($names);
    return implode(', ', $names) . ' e ' . $last;
}

function seo_load(): array
{
    $path = seo_data_path();
    $data = seo_defaults();

    if (is_file($path)) {
        $json = json_decode((string) file_get_contents($path), true);
        if (is_array($json)) {
            $data = array_merge($data, $json);
        }
    }

    $data['consultation_days'] = seo_normalize_days($data['consultation_days'] ?? []);
    if (!$data['consultation_days']) {
        $data['consultation_days'] = seo_defaults()['consultation_days'];
    }

    // Fallbacks úteis
    if ($data['og_title'] === '') {
        $data['og_title'] = $data['site_name'];
    }
    if ($data['og_description'] === '') {
        $data['og_description'] = $data['description'];
    }
    if ($data['twitter_title'] === '') {
        $data['twitter_title'] = $data['og_title'];
    }
    if ($data['twitter_description'] === '') {
        $data['twitter_description'] = $data['og_description'];
    }
    if ($data['twitter_image'] === '') {
        $data['twitter_image'] = $data['og_image'];
    }
    if ($data['schema_description'] === '') {
        $data['schema_description'] = $data['description'];
    }
    if ($data['phone_tel'] === '' && $data['phone'] !== '') {
        $data['phone_tel'] = preg_replace('/\D+/', '', $data['phone']);
        if ($data['phone_tel'] !== '' && $data['phone_tel'][0] !== '+') {
            // keep digits only for tel:/wa.me
        }
    }

    return $data;
}

function seo_save(array $input): bool
{
    $allowed = array_keys(seo_defaults());
    $data = seo_defaults();

    foreach ($allowed as $key) {
        if ($key === 'consultation_days') {
            continue;
        }
        if (!array_key_exists($key, $input)) {
            continue;
        }
        $value = is_string($input[$key]) ? trim($input[$key]) : '';
        $value = str_replace(["\r", "\n", "\0"], '', $value);
        $data[$key] = mb_substr($value, 0, 500);
    }

    // description / schema podem ser um pouco maiores
    if (isset($input['description'])) {
        $data['description'] = mb_substr(str_replace("\0", '', trim((string) $input['description'])), 0, 320);
    }
    if (isset($input['og_description'])) {
        $data['og_description'] = mb_substr(str_replace("\0", '', trim((string) $input['og_description'])), 0, 320);
    }
    if (isset($input['twitter_description'])) {
        $data['twitter_description'] = mb_substr(str_replace("\0", '', trim((string) $input['twitter_description'])), 0, 320);
    }
    if (isset($input['schema_description'])) {
        $data['schema_description'] = mb_substr(str_replace("\0", '', trim((string) $input['schema_description'])), 0, 400);
    }

    $data['consultation_days'] = seo_normalize_days($input['consultation_days'] ?? []);
    if (!$data['consultation_days']) {
        $data['consultation_days'] = seo_defaults()['consultation_days'];
    }

    $dir = dirname(seo_data_path());
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return (bool) file_put_contents(seo_data_path(), $json . "\n", LOCK_EX);
}

function seo_e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function seo_render_head(array $seo): void
{
    $canonical = rtrim($seo['canonical_url'], '/') . '/';
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Hospital',
        'name' => $seo['site_name'],
        'alternateName' => $seo['site_short_name'],
        'url' => $canonical,
        'image' => $seo['og_image'],
        'description' => $seo['schema_description'],
        'address' => [
            '@type' => 'PostalAddress',
            'addressLocality' => $seo['address_locality'],
            'addressRegion' => $seo['address_region'],
            'addressCountry' => $seo['address_country'],
        ],
        'telephone' => $seo['phone_tel'] !== '' ? $seo['phone_tel'] : $seo['phone'],
        'email' => $seo['email'],
        'openingHours' => $seo['opening_hours'],
        'medicalSpecialty' => 'InfectiousDisease',
    ];
    ?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title><?= seo_e($seo['title']) ?></title>
<meta name="description" content="<?= seo_e($seo['description']) ?>">
<meta name="theme-color" content="<?= seo_e($seo['theme_color']) ?>">
<meta name="robots" content="<?= seo_e($seo['robots']) ?>">
<link rel="canonical" href="<?= seo_e($canonical) ?>">
<link rel="icon" href="favicon.svg" type="image/svg+xml">
<meta property="og:title" content="<?= seo_e($seo['og_title']) ?>">
<meta property="og:description" content="<?= seo_e($seo['og_description']) ?>">
<meta property="og:image" content="<?= seo_e($seo['og_image']) ?>">
<meta property="og:url" content="<?= seo_e($canonical) ?>">
<meta property="og:type" content="website">
<meta property="og:locale" content="<?= seo_e($seo['og_locale']) ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= seo_e($seo['twitter_title']) ?>">
<meta name="twitter:description" content="<?= seo_e($seo['twitter_description']) ?>">
<meta name="twitter:image" content="<?= seo_e($seo['twitter_image']) ?>">
<script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<?php
}
