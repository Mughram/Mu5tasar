import assert from 'node:assert/strict';
import { spawn, spawnSync } from 'node:child_process';
import { mkdtemp, rm, readdir } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { createServer } from 'node:net';

const root = fileURLToPath(new URL('../', import.meta.url));
const sessionDir = await mkdtemp(join(tmpdir(), 'mu5tasar-security-'));
const php = process.env.PHP_BINARY || 'php';
const phpArgs = process.env.PHP_TEST_ARGS ? JSON.parse(process.env.PHP_TEST_ARGS) : [];
const env = { ...process.env, SESSION_COOKIE_SECURE: '0', DB_PASSWORD: '', DB_HOST: '' };
const args = [...phpArgs, '-d', `session.save_path=${sessionDir}`];
const portProbe = createServer();
await new Promise(resolve => portProbe.listen(0, '127.0.0.1', resolve));
const port = portProbe.address().port;
await new Promise(resolve => portProbe.close(resolve));
const base = `http://127.0.0.1:${port}/`;
const server = spawn(php, [...args, '-S', `127.0.0.1:${port}`, '-t', join(root, 'main')], {
  cwd: root, env, windowsHide: true, stdio: ['ignore', 'pipe', 'pipe'],
});
let output = '';
server.stdout.on('data', data => { output += data; });
server.stderr.on('data', data => { output += data; });
let checks = 0;
async function request(path, options = {}) {
  const response = await fetch(base + path, { redirect: 'manual', ...options });
  return { status: response.status, headers: response.headers, text: await response.text() };
}
function expect(condition, message) {
  assert.ok(condition, message);
  checks++;
}
function tokenFrom(page) {
  return page.text.match(/name="csrf_token" value="([a-f0-9]{64})"/)?.[1];
}
function post(path, cookie, values) {
  return request(path, { method: 'POST', headers: { Cookie: cookie }, body: new URLSearchParams(values) });
}

try {
  for (let attempt = 0; attempt < 50; attempt++) {
    try { await request('index.php'); break; } catch {
      if (server.exitCode !== null) throw new Error('PHP server exited: ' + output);
      await new Promise(resolve => setTimeout(resolve, 100));
    }
  }
  const login = await request('login.php');
  expect(login.status === 200, 'Login renders without database credentials');
  const cookieHeader = login.headers.get('set-cookie');
  const cookie = cookieHeader.split(';')[0];
  const csrf = tokenFrom(login);
  expect(Boolean(csrf), 'Login contains a CSRF token');
  expect(/HttpOnly/i.test(cookieHeader) && /SameSite=Lax/i.test(cookieHeader), 'Session cookie protections');
  for (const path of ['login.php', 'register.php', 'createQuiz.php', 'quizAndResult.php?id=1', 'logout.php']) {
    for (const values of [{}, { csrf_token: 'wrong' }, { 'csrf_token[]': csrf }]) {
      expect((await post(path, cookie, values)).status === 403, `${path}: invalid CSRF rejected before actions`);
    }
  }
  const other = await request('login.php');
  const otherCookie = other.headers.get('set-cookie').split(';')[0];
  expect((await post('login.php', otherCookie, { csrf_token: csrf })).status === 403, 'Cross-session token rejected');
  expect((await post('login.php', cookie, { csrf_token: csrf })).status === 200, 'Valid token reaches form validation');
  expect((await post('register.php', cookie, { csrf_token: csrf })).status === 200, 'Registration accepts valid token');
  const reflected = await post('login.php', cookie, { csrf_token: csrf, email: '\'"><script>alert(1)</script>', password: '' });
  expect(!reflected.text.includes('<script>alert(1)</script>'), 'Hostile form value is not reflected as markup');
  const failure = await post('login.php', cookie, { csrf_token: csrf, email: "' OR 1=1 --", password: 'wrong' });
  expect(failure.status === 500 && failure.text === 'The request could not be completed. Please try again later.', 'Missing credentials fail without exposing SQL or secrets');
  expect((await request('logout.php', { headers: { Cookie: cookie } })).status === 405, 'GET logout is rejected');
  expect((await request('login.php', { method: 'PUT' })).status === 405, 'Unsupported methods are rejected');

  // Seed a disposable local PHP session to exercise protected validation without a DB login.
  const seed = spawnSync(php, [...args, '-r',
    'session_start(); $_SESSION["email"]="security-test@example.invalid"; $_SESSION["csrf_token"]=bin2hex(random_bytes(32)); echo json_encode([session_id(), $_SESSION["csrf_token"]]); session_write_close();'],
    { env, encoding: 'utf8', windowsHide: true });
  assert.equal(seed.status, 0, seed.stderr);
  const [sessionId, authCsrf] = JSON.parse(seed.stdout);
  const authCookie = `PHPSESSID=${sessionId}`;
  const authHeaders = { Cookie: authCookie };
  for (const id of ["1' OR 1=1 --", '<script>alert(1)</script>', '2147483648', '1;DROP TABLE quiz', '-1']) {
    const result = await request('quizAndResult.php?id=' + encodeURIComponent(id), { headers: authHeaders });
    expect(result.status === 400 && result.text === 'Invalid quiz ID.', 'Injected or invalid ID is rejected without reflection');
  }
  expect((await request('quizAndResult.php?id[]=1', { headers: authHeaders })).status === 400, 'Array quiz ID rejected');
  const create = await request('createQuiz.php', { headers: authHeaders });
  expect(create.status === 200 && tokenFrom(create) === authCsrf, 'Protected create form renders with token');
  expect((await post('createQuiz.php', authCookie, { csrf_token: authCsrf, correct1: '1;DROP TABLE quiz' })).status === 400, 'Correct-answer selector is allowlisted');
  const before = await readdir(join(root, 'main/uploads'));
  const quizFields = { csrf_token: authCsrf, catagorie: 'Disposable security test', description: 'Disposable security test' };
  for (let i = 1; i <= 5; i++) {
    quizFields[`q${i}`] = 'Question';
    quizFields[`correct${i}`] = '1';
    for (let j = 1; j <= 4; j++) quizFields[`option${j}_${i}`] = 'Answer';
  }
  for (const [filename, content, type] of [
    ['attack.svg', '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"/>', 'image/svg+xml'],
    ['fake.jpg', '<script>alert(1)</script>', 'image/jpeg'],
    ['shell.php', '<?php echo "attack"; ?>', 'application/x-httpd-php'],
  ]) {
    const form = new FormData();
    for (const [key, value] of Object.entries(quizFields)) form.set(key, value);
    form.set('img1', new Blob([content], { type }), filename);
    const result = await request('createQuiz.php', { method: 'POST', headers: authHeaders, body: form });
    expect(result.status === 400 && result.text.includes('Upload a JPEG, PNG, GIF, or WebP image.'), 'Active or disguised image rejected');
  }
  const form = new FormData();
  for (const [key, value] of Object.entries(quizFields)) form.set(key, value);
  const pixel = Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jG2cAAAAASUVORK5CYII=', 'base64');
  form.set('img1', new Blob([pixel], { type: 'image/png' }), 'valid.png');
  const upload = await request('createQuiz.php', { method: 'POST', headers: authHeaders, body: form });
  expect(upload.status === 500 && upload.text.includes('could not be completed'), 'Valid image reaches DB configuration check');
  assert.deepEqual(await readdir(join(root, 'main/uploads')), before, 'Failed save removes disposable uploaded image'); checks++;
  expect((await request('logout.php', { headers: authHeaders })).status === 405, 'GET cannot log out authenticated session');
  expect((await request('createQuiz.php', { headers: authHeaders })).status === 200, 'Session survives GET logout');
  expect((await post('logout.php', authCookie, { csrf_token: authCsrf })).status === 302, 'Valid POST logs out');
  expect((await request('createQuiz.php', { headers: authHeaders })).status === 302, 'Logged-out session loses protected access');
  console.log(`PASS: ${checks} HTTP checks. Disposable sessions and upload cleaned; no live DB writes.`);
} finally {
  server.kill();
  if (server.exitCode === null) await new Promise(resolve => server.once('exit', resolve));
  await rm(sessionDir, { recursive: true, force: true });
}
