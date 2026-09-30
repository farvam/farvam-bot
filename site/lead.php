<?php
/* ذخیره درخواست‌های فرم سایت (ماه رایگان / دمو) در data/leads.json */
require_once __DIR__ . '/lib.php';
header('Content-Type: application/json; charset=utf-8');
header('X-Robots-Tag: noindex');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo '{"ok":false}'; exit; }
$in = json_decode((string)file_get_contents('php://input'), true) ?: [];
if (!empty($in['website'])) { echo '{"ok":true}'; exit; }            // honeypot: bots fill hidden field
$clean = fn($k, $n = 200) => mb_substr(trim(strip_tags((string)($in[$k] ?? ''))), 0, $n);
$lead = ['time' => date('c'), 'name' => $clean('name', 80), 'phone' => $clean('phone', 20), 'role' => $clean('role', 60), 'city' => $clean('city', 60),
         'source' => $clean('src', 60), 'utm' => $clean('utm', 120), 'note' => $clean('note', 500), 'page' => $clean('page', 200)];
if ($lead['name'] === '' || !preg_match('/^[0-9۰-۹+\s\-]{8,20}$/u', $lead['phone'])) { http_response_code(422); echo '{"ok":false}'; exit; }
// simple rate limit: 5 requests per IP per hour
$ip = hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . 'farvam');
$rl = load_json('lead-rate'); $rl[$ip] = array_values(array_filter($rl[$ip] ?? [], fn($t) => $t > time() - 3600));
if (count($rl[$ip]) >= 5) { http_response_code(429); echo '{"ok":false}'; exit; }
$rl[$ip][] = time(); save_json('lead-rate', $rl);
$all = load_json('leads'); array_unshift($all, $lead);
echo json_encode(['ok' => save_json('leads', array_slice($all, 0, 5000))]);
