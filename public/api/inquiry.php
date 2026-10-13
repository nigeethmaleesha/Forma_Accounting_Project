<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/app/bootstrap.php';
headers_security();
header('Cache-Control: no-store');
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Allow: POST'); json_reply(405, ['error'=>'Method not allowed.']);
}
if (!str_contains(strtolower($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json')) {
    json_reply(415, ['error'=>'Unsupported form format.']);
}
if ((int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 8000) {
    json_reply(413, ['error'=>'Your enquiry is too long.']);
}
$origin = (string)($_SERVER['HTTP_ORIGIN'] ?? '');
if ($origin !== '') {
    $originHost = parse_url($origin, PHP_URL_HOST);
    $requestHost = parse_url('http://' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST);
    if (!is_string($originHost) || !is_string($requestHost) || strcasecmp($originHost, $requestHost) !== 0) {
        json_reply(403, ['error'=>'Request origin was not recognised.']);
    }
}
$raw = file_get_contents('php://input', false, null, 0, 8001);
if (!is_string($raw) || strlen($raw) > 8000) json_reply(413, ['error'=>'Your enquiry is too long.']);
$form = json_decode($raw, true);
if (!is_array($form) || array_is_list($form)) json_reply(400, ['error'=>'Invalid enquiry.']);
if (!check_csrf(is_string($form['csrf'] ?? null) ? $form['csrf'] : null)) {
    json_reply(419, ['error'=>'Your form session expired. Refresh this page and try again.']);
}
if (!empty($form['website'])) json_reply(200, ['ok'=>true,'delivery'=>'discarded']); // Honeypot.
foreach (['name','email','subject','message'] as $field) {
    if (!isset($form[$field]) || !is_string($form[$field])) json_reply(422, ['error'=>'Please check the required fields.']);
}
$name = trim($form['name']); $email = trim($form['email']);
$subject = trim($form['subject']); $message = trim($form['message']);
$subjects = ['Business accounting','Tax and compliance','Bookkeeping and payroll','Business advisory','Virtual CFO support','Superannuation guidance','Something else'];
$nameLength = preg_match_all('/./us', $name);
$messageLength = preg_match_all('/./us', $message);
if ($nameLength === false || $nameLength < 2 || $nameLength > 120
    || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254
    || !in_array($subject, $subjects, true) || $messageLength === false || $messageLength < 15 || $messageLength > 3000
    || ($form['consent'] ?? '') !== 'yes' || preg_match('/[\r\n]/', $name . $email)) {
    json_reply(422, ['error'=>'Please check all required fields and try again.']);
}
// File-lock sliding window, limits requests per IP. The private storage folder is not web accessible.
$dir = APP_ROOT . '/storage/limits';
if (!is_dir($dir) && !mkdir($dir, 0700, true) && !is_dir($dir)) {
    json_reply(503, ['error'=>'Form service temporarily unavailable.']);
}
$key = hash('sha256', (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown') . date('Y-m-d') . 'forma');
$fp = @fopen($dir . '/' . $key . '.json', 'c+');
if (!$fp || !flock($fp, LOCK_EX)) {
    if ($fp) fclose($fp);
    json_reply(503, ['error'=>'Form service temporarily unavailable.']);
}
$history = json_decode((string)stream_get_contents($fp), true);
$history = is_array($history) ? $history : [];
$now = time();
$history = array_values(array_filter($history, static fn($t) => is_numeric($t) && $t > $now - (int)config('RATE_LIMIT_WINDOW')));
if (count($history) >= max(1, (int)config('RATE_LIMIT_MAX'))) {
    flock($fp, LOCK_UN); fclose($fp);
    json_reply(429, ['error'=>'Too many enquiries. Please try again later.']);
}
$history[] = $now;
rewind($fp); ftruncate($fp, 0); fwrite($fp, json_encode($history)); fflush($fp);
flock($fp, LOCK_UN); fclose($fp);

$body = "Website enquiry\n\nName: {$name}\nEmail: {$email}\nService: {$subject}\n\nMessage:\n{$message}\n";
$mode = (string)config('MAIL_MODE');
if ($mode === 'log' && config('SITE_MODE') !== 'production') {
    if (file_put_contents(APP_ROOT . '/storage/preview-inquiries.log', '[' . gmdate('c') . "]\n" . $body . "\n----\n", FILE_APPEND | LOCK_EX) === false) {
        json_reply(503, ['error'=>'Local preview logging unavailable.']);
    }
    json_reply(200, ['ok'=>true,'delivery'=>'preview']);
}
$to = trim((string)config('CONTACT_TO_EMAIL'));
$from = trim((string)config('CONTACT_FROM_EMAIL'));
if (!filter_var($to, FILTER_VALIDATE_EMAIL) || !filter_var($from, FILTER_VALIDATE_EMAIL)) {
    json_reply(503, ['error'=>'Enquiry delivery is not configured yet. Please try again later.']);
}
if ($mode === 'mail') {
    $headers = ['From'=>$from,'Reply-To'=>$email,'Content-Type'=>'text/plain; charset=UTF-8'];
    if (!@mail($to, 'New website enquiry: ' . $subject, $body, $headers)) {
        json_reply(502, ['error'=>'The mail service could not accept the enquiry. Please try again later.']);
    }
    json_reply(200, ['ok'=>true,'delivery'=>'mail']);
}
if ($mode === 'resend') {
    if (!extension_loaded('curl') || !config('RESEND_API_KEY')) json_reply(503, ['error'=>'Email service is not configured.']);
    $ch = curl_init('https://api.resend.com/emails');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER=>true, CURLOPT_POST=>true, CURLOPT_TIMEOUT=>10,
        CURLOPT_HTTPHEADER=>['Content-Type: application/json','Authorization: Bearer ' . (string)config('RESEND_API_KEY')],
        CURLOPT_POSTFIELDS=>json_encode(['from'=>$from,'to'=>[$to],'reply_to'=>$email,'subject'=>'New website enquiry: ' . $subject,'text'=>$body]),
    ]);
    $response = curl_exec($ch); $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    if ($response === false || $status < 200 || $status >= 300) json_reply(502, ['error'=>'Email service unavailable. Please try later.']);
    json_reply(200, ['ok'=>true,'delivery'=>'resend']);
}
json_reply(503, ['error'=>'Enquiry delivery is not configured yet. Please try again later.']);
