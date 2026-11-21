<?php
// bh-dockhref1.php - gives the ticker drawer's "Click here" link a default href so crawlers see a real link.
// Patches assets/js/rack.js once (marker bh-dockhref1) and bumps rack.js?v=N in site HTML/PHP files.
// Dry run by default. &go=go writes. Backups kept as *.bak-dockhref1. Self-deletes on success.
header("Content-Type: text/plain; charset=utf-8");
header("Cache-Control: no-store, no-cache, must-revalidate");
$TOKEN = "bh-DkHr1ef!";
if (!isset($_GET['t']) || $_GET['t'] !== $TOKEN) { http_response_code(404); echo "Not Found"; exit; }
$GO = (isset($_GET['go']) && $_GET['go'] === 'go');
chdir(__DIR__);
$JS = "assets/js/rack.js";
$MARK = "bh-dockhref1";
$NEEDLE = "dl2.target='_blank';dl2.rel='noopener';";
$REPL = "dl2.href='https://thedjcalendar.com';/* bh-dockhref1 */dl2.target='_blank';dl2.rel='noopener';";
$VPAT = '#rack\.js\?v=(\d+)#';
echo "MODE: " . ($GO ? "GO (writes enabled)" : "DRY RUN (nothing will be written)") . "\n";
if (!is_file($JS)) { echo "ABORT: $JS missing; nothing written\n"; exit; }
$js = file_get_contents($JS);
if ($js === false) { echo "ABORT: cannot read $JS\n"; exit; }
echo "rack.js bytes: " . strlen($js) . "\n";
if (strpos($js, $MARK) !== false) { echo "ABORT: marker $MARK already present; nothing written\n"; exit; }
$n = substr_count($js, $NEEDLE);
echo "needle count: $n (need 1)\n";
if ($n !== 1) { echo "ABORT: needle not found exactly once; nothing written\n"; exit; }
$newJs = str_replace($NEEDLE, $REPL, $js);
echo "new rack.js bytes: " . strlen($newJs) . " (+" . (strlen($newJs) - strlen($js)) . ")\n";
$self = basename(__FILE__);
$files = array();
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__, FilesystemIterator::SKIP_DOTS));
foreach ($it as $f) {
  if (!$f->isFile()) continue;
  $rel = substr($f->getPathname(), strlen(__DIR__) + 1);
  if ($rel[0] === '.' || $rel === $self || strpos($rel, 'assets/') === 0 || strpos($rel, 'og-cache/') === 0 || strpos($rel, '.bak') !== false) continue;
  $ext = strtolower($f->getExtension());
  if ($ext !== 'html' && $ext !== 'htm' && $ext !== 'php') continue;
  if ($f->getSize() > 3000000) continue;
  $files[] = $rel;
}
sort($files);
$plan = array();
$total = 0;
foreach ($files as $rel) {
  $s = file_get_contents($rel);
  if ($s === false) continue;
  $c = preg_match_all($VPAT, $s, $m);
  if ($c > 0) { $plan[$rel] = $c; $total += $c; echo "FILE $rel: $c rack.js ref(s)\n"; }
}
echo "rack.js version refs to bump: $total in " . count($plan) . " file(s)\n";
if (!$GO) { echo "DRY RUN OK. Nothing written. Run again with &go=go to write.\n"; exit; }
$bakJs = $JS . ".bak-dockhref1";
if (!copy($JS, $bakJs)) { echo "ABORT: backup failed for $JS; nothing written\n"; exit; }
echo "backup: $bakJs\n";
if (file_put_contents($JS, $newJs, LOCK_EX) !== strlen($newJs)) { copy($bakJs, $JS); echo "WRITE SHORT on $JS; restored\n"; exit; }
clearstatcache();
if (file_get_contents($JS) !== $newJs) { copy($bakJs, $JS); echo "VERIFY FAILED on $JS; restored\n"; exit; }
echo "rack.js patched and verified\n";
$done = 0;
foreach ($plan as $rel => $c) {
  $s = file_get_contents($rel);
  $cnt = 0;
  $new = preg_replace_callback($VPAT, function ($mm) { return 'rack.js?v=' . ((int)$mm[1] + 1); }, $s, -1, $cnt);
  if ($new === null || $cnt !== $c) { echo "ABORT on $rel: count mismatch; stopped (rack.js already patched, see backups)\n"; exit; }
  $bak = $rel . ".bak-dockhref1";
  if (!copy($rel, $bak)) { echo "ABORT: backup failed for $rel; stopped\n"; exit; }
  if (file_put_contents($rel, $new, LOCK_EX) !== strlen($new)) { copy($bak, $rel); echo "WRITE SHORT on $rel; restored\n"; exit; }
  clearstatcache();
  if (file_get_contents($rel) !== $new) { copy($bak, $rel); echo "VERIFY FAILED on $rel; restored\n"; exit; }
  $done++;
  echo "bumped $rel ($c)\n";
}
echo "done: rack.js patched, $done file(s) bumped. Backups kept as *.bak-dockhref1.\n";
@unlink(__FILE__);
echo "worker self-deleted.\n";
