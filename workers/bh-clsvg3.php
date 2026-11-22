<?php
// bh-clsvg3.php - replace the homepage Coder Legion strip (rack.js bh-clsec1 fetch/innerHTML block)
// with the live official badge: https://coderlegion.com/profile-stats-badge/bryanhamiltondev.svg
// New token so any output proves this is the new file (old copies 404 here).
// Dry run by default; &go=go writes. Backup: assets/js/rack.js.bak-clsvg3. Self-deletes on success.
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
$TOKEN = 'bh-ClSvg2!';
if (!isset($_GET['t']) || $_GET['t'] !== $TOKEN) { http_response_code(404); echo 'Not Found'; exit; }
$GO = (isset($_GET['go']) && $_GET['go'] === 'go');
$NL = chr(10);
chdir(__DIR__);
echo 'VERSION: bh-clsvg3 (token bh-ClSvg2!)' . $NL;
echo 'MODE: ' . ($GO ? 'GO (writes enabled)' : 'DRY RUN (nothing will be written)') . $NL;

$jsPath = 'assets/js/rack.js';
$src = @file_get_contents($jsPath);
if ($src === false) { echo 'ABORT: cannot read ' . $jsPath . $NL; exit; }
echo 'rack.js bytes: ' . strlen($src) . $NL;

$mark = '/* bh-clsec1: minimal';
if (substr_count($src, $mark) !== 1) { echo 'ABORT: bh-clsec1 marker count is ' . substr_count($src, $mark) . ', want 1' . $NL; exit; }
$p = strpos($src, $mark);
echo 'marker offset: ' . $p . $NL;

$startNeedle = "fetch('/coderlegion.php',{cache:'no-store'}).then(function(r){return r.json();}).then(function(j){";
$endNeedle = '}).catch(function(){});';
$s0 = strpos($src, $startNeedle, $p);
if ($s0 === false || ($s0 - $p) > 1500) { echo 'ABORT: clsec1 fetch start not found near marker (pos ' . var_export($s0, true) . ', delta ' . ($s0 === false ? -1 : $s0 - $p) . ')' . $NL; exit; }
echo 'fetch offset: ' . $s0 . ' (delta ' . ($s0 - $p) . ')' . $NL;
$e0 = strpos($src, $endNeedle, $s0);
if ($e0 === false || ($e0 - $s0) > 1600) { echo 'ABORT: clsec1 fetch end not found in range' . $NL; exit; }
$end = $e0 + strlen($endNeedle);

$region = substr($src, $s0, $end - $s0);
echo 'region to replace: ' . strlen($region) . ' bytes' . $NL;
echo 'region head: ' . substr($region, 0, 160) . $NL;

$new = <<<'JS'
var s=document.createElement('section');s.id='cl-strip';s.setAttribute('aria-label','Coder Legion stats');/* bh-clsvg3 */s.innerHTML='<a href="https://coderlegion.com/user/bryanhamiltondev" target="_blank" rel="noopener"><img src="https://coderlegion.com/profile-stats-badge/bryanhamiltondev.svg" alt="Coder Legion live stats: points, badges, posts and rank for Bryan Hamilton" loading="lazy" style="max-width:100%;height:auto;display:block"></a>';footer.parentNode.insertBefore(s,footer);
JS;

$newsrc = substr($src, 0, $s0) . $new . substr($src, $end);
if (substr_count($newsrc, 'profile-stats-badge') !== 1) { echo 'ABORT: post-check badge img missing' . $NL; exit; }
if (substr_count($newsrc, $startNeedle) !== substr_count($src, $startNeedle) - 1) { echo 'ABORT: post-check fetch count wrong (other CL blocks must stay)' . $NL; exit; }

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
foreach ($bumped as $rel => $info) echo '  ' . $rel . ': ' . $info[1] . ' ref(s)' . $NL;

if (!$GO) { echo $NL . 'DRY RUN OK. Nothing written. Run again with &go=go to write.' . $NL; exit; }

$bak = $jsPath . '.bak-clsvg3';
if (!copy($jsPath, $bak)) { echo 'ABORT: backup failed' . $NL; exit; }
echo 'backup: ' . $bak . $NL;
if (file_put_contents($jsPath, $newsrc, LOCK_EX) !== strlen($newsrc)) { copy($bak, $jsPath); echo 'WRITE SHORT; restored' . $NL; exit; }
clearstatcache();
if (file_get_contents($jsPath) !== $newsrc) { copy($bak, $jsPath); echo 'VERIFY FAILED; restored' . $NL; exit; }
echo 'wrote ' . $jsPath . ' and verified' . $NL;
foreach ($bumped as $rel => $info) {
  $rf = $rel . '.bak-clsvg3';
  if (!copy($rel, $rf)) { echo 'WARN: backup failed for ' . $rel . ' (js patch is live)' . $NL; continue; }
  if (file_put_contents($rel, $info[0], LOCK_EX) !== strlen($info[0])) { copy($rf, $rel); echo 'WARN: write short ' . $rel . '; restored' . $NL; continue; }
  clearstatcache();
  if (file_get_contents($rel) !== $info[0]) { copy($rf, $rel); echo 'WARN: verify failed ' . $rel . '; restored' . $NL; continue; }
  echo 'bumped ' . $rel . $NL;
}
@unlink(__FILE__);
echo 'worker self-deleted. Backups kept as *.bak-clsvg3.' . $NL;
