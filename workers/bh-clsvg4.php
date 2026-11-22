<?php
// bh-clsvg4.php - center the #cl-strip badge horizontally on the homepage.
// Adds justify-content:center to the bh-clsec1 inline #cl-strip rule in assets/js/rack.js.
// Dry run by default; &go=go writes. Backup: assets/js/rack.js.bak-clsvg4. Self-deletes on success.
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
$TOKEN = 'bh-ClSvg3!';
if (!isset($_GET['t']) || $_GET['t'] !== $TOKEN) { http_response_code(404); echo 'Not Found'; exit; }
$GO = (isset($_GET['go']) && $_GET['go'] === 'go');
$NL = chr(10);
chdir(__DIR__);
echo 'VERSION: bh-clsvg4 (token bh-ClSvg3!)' . $NL;
echo 'MODE: ' . ($GO ? 'GO (writes enabled)' : 'DRY RUN (nothing will be written)') . $NL;

$jsPath = 'assets/js/rack.js';
$src = @file_get_contents($jsPath);
if ($src === false) { echo 'ABORT: cannot read ' . $jsPath . $NL; exit; }

echo 'badge live: ' . (substr_count($src, 'profile-stats-badge') === 1 ? 'yes' : 'NO (' . substr_count($src, 'profile-stats-badge') . ' hits)') . $NL;

$needle = 'gap:6px 18px;align-items:center}#cl-strip .lbl';
if (substr_count($src, $needle) !== 1) { echo 'ABORT: CSS needle count is ' . substr_count($src, $needle) . ', want 1' . $NL; exit; }
$repl = 'gap:6px 18px;align-items:center;justify-content:center}#cl-strip .lbl';
$newsrc = str_replace($needle, $repl, $src);
if (substr_count($newsrc, 'justify-content:center}#cl-strip .lbl') !== 1) { echo 'ABORT: post-check failed' . $NL; exit; }
echo 'CSS patch: #cl-strip gets justify-content:center' . $NL;

$bumped = array();
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__, FilesystemIterator::SKIP_DOTS));
foreach ($it as $f) {
  if (!$f->isFile()) continue;
  $rel = substr($f->getPathname(), strlen(__DIR__) + 1);
  if ($rel === '' || $rel[0] === '.') continue;
  if (strpos($rel, 'assets/') === 0 || strpos($rel, 'og-cache/') === 0 || strpos($rel, '.bak') !== false) continue;
  $ext = strtolower($f->getExtension());
  if ($ext !== 'html' && $ext !== 'htm' && $ext !== 'php') continue;
  if ($f->getSize() > 3000000) continue;
  $s = @file_get_contents($f->getPathname());
  if ($s === false || strpos($s, 'rack.js?v=') === false) continue;
  $n = 0;
  $r = preg_replace_callback('/rack\.js\?v=(\d+)/', function ($m) use (&$n) { $n++; return 'rack.js?v=' . ((int)$m[1] + 1); }, $s);
  if ($n > 0) { $bumped[$rel] = array($r, $n); }
}
echo 'version bumps queued: ' . count($bumped) . ' file(s)' . $NL;

if (!$GO) { echo $NL . 'DRY RUN OK. Nothing written. Run again with &go=go to write.' . $NL; exit; }

$bak = $jsPath . '.bak-clsvg4';
if (!copy($jsPath, $bak)) { echo 'ABORT: backup failed' . $NL; exit; }
echo 'backup: ' . $bak . $NL;
if (file_put_contents($jsPath, $newsrc, LOCK_EX) !== strlen($newsrc)) { copy($bak, $jsPath); echo 'WRITE SHORT; restored' . $NL; exit; }
clearstatcache();
if (file_get_contents($jsPath) !== $newsrc) { copy($bak, $jsPath); echo 'VERIFY FAILED; restored' . $NL; exit; }
echo 'wrote ' . $jsPath . ' and verified' . $NL;
foreach ($bumped as $rel => $info) {
  $rf = $rel . '.bak-clsvg4';
  if (!copy($rel, $rf)) { echo 'WARN: backup failed for ' . $rel . ' (js patch is live)' . $NL; continue; }
  if (file_put_contents($rel, $info[0], LOCK_EX) !== strlen($info[0])) { copy($rf, $rel); echo 'WARN: write short ' . $rel . '; restored' . $NL; continue; }
  clearstatcache();
  if (file_get_contents($rel) !== $info[0]) { copy($rf, $rel); echo 'WARN: verify failed ' . $rel . '; restored' . $NL; continue; }
  echo 'bumped ' . $rel . $NL;
}
@unlink(__FILE__);
echo 'worker self-deleted. Backups kept as *.bak-clsvg4.' . $NL;
