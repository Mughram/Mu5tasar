<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../config/uploads.php';
restore_exception_handler();
ini_set('display_errors', '1');

$checks = 0;
function check(bool $condition, string $message): void
{
    global $checks;
    if (!$condition) {
        throw new RuntimeException($message);
    }
    $checks++;
}

$token = csrf_token();
check(strlen($token) === 64 && ctype_xdigit($token), 'Random token format');
check(csrf_token() === $token && valid_csrf_token($token), 'Session-bound token remains valid');
foreach ([null, '', [], ['x'], str_repeat('0', 64), substr($token, 1)] as $invalid) {
    check(!valid_csrf_token($invalid), 'Reject malformed or missing token');
}
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
check(!valid_csrf_token($token), 'Reject token from another session');

$payload = '\'"><script>alert(1)</script><img src=x onerror=alert(2)>&';
check(!str_contains(e($payload), '<'), 'Escape HTML markup');
check(str_contains(e($payload), '&#039;') && str_contains(e($payload), '&quot;'), 'Escape both quote types');
check(e("\xFF") === "\xEF\xBF\xBD", 'Substitute invalid UTF-8');
$_POST['raw'] = $payload;
check(post_text('raw') === $payload, 'Do not transform stored input');

foreach (['../x.png', '..\\x.png', 'https://evil.test/x.jpg', '//evil.test/x.png', 'javascript:alert(1)', 'x.svg', "x\0.png"] as $invalid) {
    check(image_url($invalid) === null, 'Reject external or unsafe image path');
}
check(image_url('1766084963_World Cup.jpg') === 'uploads/1766084963_World%20Cup.jpg', 'Keep existing image filenames');
check(save_quiz_image(null) === '', 'Optional image stays optional');
try {
    save_quiz_image(['error' => UPLOAD_ERR_OK, 'tmp_name' => __FILE__]);
    check(false, 'Reject non-uploaded local files');
} catch (InvalidArgumentException $error) {
    check(true, 'Reject non-uploaded local files');
}

// Exercise the real page templates with hostile database fixtures, without a database.
function render_template(string $filename, array $data): string
{
    extract($data, EXTR_SKIP);
    $source = file_get_contents(__DIR__ . '/../main/' . $filename);
    $template = substr($source, strpos($source, '?>') + 2);
    ob_start();
    eval('?>' . $template);
    return ob_get_clean();
}
$quiz = ['catagorie' => $payload, 'description' => $payload];
for ($i = 1; $i <= 5; $i++) {
    $quiz['q' . $i] = $payload;
    $quiz['img' . $i] = 'x.jpg" onerror="alert(1).jpg';
    for ($j = 1; $j <= 4; $j++) {
        $quiz['option' . $j . '_' . $i] = $payload;
    }
}
$html = render_template('quizAndResult.php', ['quiz' => $quiz, 'score' => null]);
$dom = new DOMDocument();
@$dom->loadHTML($html);
$xpath = new DOMXPath($dom);
check($xpath->query('//script[not(@src)] | //*[@onerror or @onload or @autofocus]')->length === 0, 'Stored XSS cannot create active markup');
check($xpath->query('//input[@type="radio"]')->length === 20, 'Preserve all answer inputs');
foreach ($xpath->query('//input[@type="radio"]') as $input) {
    check($input->getAttribute('value') === $payload, 'Answer attribute preserves original data after decoding');
}
$listing = render_template('showQuiz.php', ['rows' => [['id' => "1' onclick='alert(1)", 'catagorie' => $payload, 'description' => $payload]]]);
@$dom->loadHTML($listing);
$xpath = new DOMXPath($dom);
check($xpath->query('//script[not(@src)] | //*[@onclick or @onerror]')->length === 0, 'Listing text and URL context reject stored XSS');

$password = bin2hex(random_bytes(16));
$hash = password_hash($password, PASSWORD_BCRYPT);
check(password_verify($password, $hash) && !password_verify('wrong', $hash), 'Bcrypt password verification');
check(strlen($hash) <= 80, 'Hash fits the live password column');
$_SESSION = [];
session_destroy();
echo "PASS: $checks security and template checks. No database records used.\n";
