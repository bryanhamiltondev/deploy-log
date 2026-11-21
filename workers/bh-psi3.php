<?php
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
$TOKEN = 'bh-Psi3Fx!';
if (!isset($_GET['t']) || $_GET['t'] !== $TOKEN) { http_response_code(404); echo 'Not Found'; exit; }
$GO = (isset($_GET['go']) && $_GET['go'] === 'go');
$NL = chr(10);
chdir(__DIR__);
$self = basename(__FILE__);
$EDITS = array(
  array('name' => 'receipts line', 'pat' => '#homepage LCP 7\.0s[^0-9<]{1,12}2\.3s#', 'rep' => 'homepage PSI 96 perf / 100 SEO (Nov 21)', 'done' => 'homepage PSI 96 perf', 'want' => 3),
);
echo 'MODE: ' . ($GO ? 'GO (writes enabled)' : 'DRY RUN (nothing will be written)') . $NL;
$files = array();
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__, FilesystemIterator::SKIP_DOTS));
foreach ($it as $f) {
  if (!$f->isFile()) continue;
  $rel = substr($f->getPathname(), strlen(__DIR__) + 1);
  if ($rel === '' || $rel[0] === '.' || $rel === $self) continue;
  if (strpos($rel, 'assets/') === 0 || strpos($rel, 'og-cache/') === 0 || strpos($rel, '.bak') !== false) continue;
  $ext = strtolower($f->getExtension());
  if ($ext !== 'html' && $ext !== 'htm' && $ext !== 'php') continue;
  if ($f->getSize() > 3000000) continue;
  $files[] = $rel;
}
sort($files);
$contents = array();
foreach ($files as $rel) { $s = file_get_contents($rel); if ($s !== false) $contents[$rel] = $s; }
$ok = true;
foreach ($EDITS as $e) {
  $total = 0; $hits = array();
  foreach ($contents as $rel => $s) { $c = preg_match_all($e['pat'], $s, $m); if ($c > 0) { $hits[$rel] = $c; $total += $c; } }
  echo $e['name'] . ': ' . $total . ' match(es), want ' . $e['want'] . $NL;
  foreach ($hits as $rel => $c) echo '  ' . $rel . ': ' . $c . $NL;
  if ($total === 0) {
    $found = false;
    foreach ($contents as $s) { if (strpos($s, $e['done']) !== false) { $found = true; break; } }
    if ($found) echo '  already applied (nothing to change)' . $NL;
    else { echo '  ABORT: no match and no sign it was applied' . $NL; $ok = false; }
  } elseif ($total !== $e['want']) { echo '  ABORT: match count is not ' . $e['want'] . $NL; $ok = false; }
}
echo $NL . 'DIAG (read-only): where the LED bay deploy-age text lives' . $NL;
$dg = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__, FilesystemIterator::SKIP_DOTS));
$shown = 0;
foreach ($dg as $f) {
  if ($shown >= 8) break;
  if (!$f->isFile()) continue;
  $ext = strtolower($f->getExtension());
  if ($ext !== 'js' && $ext !== 'html' && $ext !== 'htm' && $ext !== 'php') continue;
  $rel = substr($f->getPathname(), strlen(__DIR__) + 1);
  if ($rel === $self || strpos($rel, '.bak') !== false || strpos($rel, 'og-cache/') === 0) continue;
  if ($f->getSize() > 3000000) continue;
  $s = file_get_contents($f->getPathname());
  if ($s === false) continue;
  $p = strpos($s, 'last deploy');
  if ($p === false) $p = strpos($s, 'deploys logged');
  if ($p === false) continue;
  $shown++;
  echo $rel . ' @' . $p . ': ' . str_replace(array(chr(10), chr(13)), ' ', substr($s, max(0, $p - 60), 200)) . $NL;
}
if ($shown === 0) echo 'no hits' . $NL;
if (!$ok) { echo 'nothing written' . $NL; exit; }
if (!$GO) { echo 'DRY RUN OK. Nothing written. Run again with &go=go to write.' . $NL; exit; }
$newc = $contents;
foreach ($EDITS as $e) { foreach ($newc as $rel => $s) { $cnt = 0; $r = preg_replace($e['pat'], $e['rep'], $s, -1, $cnt); if ($r === null) { echo 'ABORT: regex error' . $NL; exit; } if ($cnt > 0) $newc[$rel] = $r; } }
$changed = 0;
foreach ($newc as $rel => $n) {
  if ($n === $contents[$rel]) continue;
  $bak = $rel . '.bak-psi3';
  if (!copy($rel, $bak)) { echo 'ABORT: backup failed for ' . $rel . $NL; exit; }
  if (file_put_contents($rel, $n, LOCK_EX) !== strlen($n)) { copy($bak, $rel); echo 'WRITE SHORT; restored ' . $rel . $NL; exit; }
  clearstatcache();
  if (file_get_contents($rel) !== $n) { copy($bak, $rel); echo 'VERIFY FAILED; restored ' . $rel . $NL; exit; }
  echo 'wrote ' . $rel . ' (backup ' . $bak . ')' . $NL;
  $changed++;
}
echo 'files changed: ' . $changed . $NL;
@unlink(__FILE__);
echo 'worker self-deleted. Backups kept as *.bak-psi3.' . $NL;
