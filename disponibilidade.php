<?php
/**
 * Disponibilidade diária de marcações por serviço.
 * GET: servico, year, month
 */
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

require_once __DIR__ . '/includes/content.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit;
}

$servico = trim((string) ($_GET['servico'] ?? ''));
$year = (int) ($_GET['year'] ?? date('Y'));
$month = (int) ($_GET['month'] ?? date('n'));

if ($servico === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Serviço em falta.']);
    exit;
}

if ($year < 2020 || $year > 2100 || $month < 1 || $month > 12) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Mês inválido.']);
    exit;
}

$service = services_find_by_title($servico);
if (!$service) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Serviço não encontrado.']);
    exit;
}

$days = bookings_month_availability($servico, $year, $month);

echo json_encode([
    'success' => true,
    'service' => $servico,
    'year' => $year,
    'month' => $month,
    'daily_limit' => (int) ($service['daily_limit'] ?? 10),
    'days' => $days,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
