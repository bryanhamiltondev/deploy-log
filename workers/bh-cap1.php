<?php
// bh-cap1.php - capitalizes the word "chef" to "Chef" in visible text across the site's HTML, TXT and XML files.
// Skips URLs, attributes, PHP files and backups. Dry run by default. &go=go writes.
// Backups kept as *.bak-cap1. Self-deletes on success.
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
$TOKEN = 'bh-Cap1Ch3f!';
if (!isset($_GET['t']) || $_GET['t'] !== $TOKEN) { http_response_code(404); echo "Not Found"; exit; }
$GO = (isset($_GET['go']) && $_GET['go'] === 'go');
chdir(__DIR__);
$PAT = '/(?<![\w\/\-.=#:&%\'"])chef(s?)(?!\w|[\/\-]|\.[A-Za-z])/';
$EXTS = array('html', 'htm', 'txt', 'xml');
echo "MODE: " . ($GO ? "GO (writes enabled)" : "DRY RUN (nothing will be written)") . "\n";
$files = array();
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__, FilesystemIterator::SKIP_DOTS));
foreach ($it as $f) {
  if (!$f->isFile()) continue;
  $rel = substr($f->getPathname(), strlen(__DIR__) + 1);
  if ($rel[0] === '.' || strpos($rel, 'og-cache/') === 0 || strpos($rel, '.bak') !== false) continue;
  if (!in_array(strtolower($f->getExtension()), $EXTS)) continue;
  if ($f->getSize() > 3000000) continue;
  $files[] = $rel;
}
sort($files);
echo "files scanned: " . count($files) . "\n";
$plan = array();
$total = 0;
foreach ($files as $rel) {
  $s = file_get_contents($rel);
  if ($s === false) continue;
  if (!preg_match_all($PAT, $s, $m, PREG_OFFSET_CAPTURE)) continue;
  $n = count($m[0]);
  $total += $n;
  $plan[$rel] = $n;
  echo "FILE $rel: $n match(es)\n";
  foreach ($m[0] as $hit) {
    $off = $hit[1]; $len = strlen($hit[0]);
    $line = substr_count(substr($s, 0, $off), "\n") + 1;
    $ctx = substr($s, max(0, $off - 60), 60 + $len + 60);
    $ctx = trim(preg_replace('/\s+/', ' ', $ctx));
    echo "  L$line: ...$ctx...\n";
  }
}
echo "TOTAL matches: $total in " . count($plan) . " file(s)\n";
if (!$GO) { echo "DRY RUN OK. Nothing written. Run again with &go=go to write.\n"; exit; }
if ($total === 0) { echo "Nothing to change.\n"; @unlink(__FILE__); exit; }
$done = 0;
foreach ($plan as $rel => $n) {
  $s = file_get_contents($rel);
  $cnt = 0;
  $new = preg_replace_callback($PAT, function ($m) { return 'Chef' . $m[1]; }, $s, -1, $cnt);
  if ($new === null || $cnt !== $n) { echo "ABORT on $rel: count mismatch; stopped here\n"; exit; }
  $bak = $rel . '.bak-cap1';
  if (!copy($rel, $bak)) { echo "ABORT: backup failed for $rel\n"; exit; }
  if (file_put_contents($rel, $new, LOCK_EX) !== strlen($new)) { copy($bak, $rel); echo "WRITE SHORT on $rel; restored\n"; exit; }
  clearstatcache();
  if (file_get_contents($rel) !== $new) { copy($bak, $rel); echo "VERIFY FAILED on $rel; restored\n"; exit; }
  $done++;
  echo "wrote $rel ($n)\n";
}
echo "done: $done file(s), $total replacement(s). Backups kept as *.bak-cap1.\n";
@unlink(__FILE__);
echo "worker self-deleted.\n";
